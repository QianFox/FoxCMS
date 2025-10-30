<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   15:17
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   15:17
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\lib\Oss;
use app\common\lib\Qiniu;
use app\common\model\DictData as DictDataModel;
use app\common\model\DictType as DictTypeModel;
use app\common\model\UploadFiles as UploadFilesModel;
use app\common\util\ImageQuality;
use app\common\util\ImageUtil;
use think\App;
use think\facade\Db;
use think\File;

// 图片管理器
class PicManager extends AdminBase
{
    // 获取图片
    private function imgList($param)
    {
        $startDate = $param['startDate'];
        $endDate = $param['endDate'];
        $currentPage =  intval($param['currentPage'] ? $param['currentPage'] : 1);
        $pageSize = intval($param['pageSize'] ? $param['pageSize'] : 10);
        $groupId = intval(($param['groupId'] || $param['groupId'] == '0') ? $param['groupId'] : -1); //分组
        //        $storage = intval($param['storage']);//远程
        $where = ["file_type" => "image"];
        if ($groupId == 1) { //本地图片
            $where['storage'] = 0;
        } else if ($groupId == 2) { //OSS图片
            $where['storage'] = 1;
        } else {
            if ($groupId != -1) {
                $where['file_group_type'] = $groupId;
            }
        }

        $ufmWObj = UploadFilesModel::field('id, file_name as name, url, storage, file_group_type, size')->where($where);
        if ($startDate != '') {
            $ufmWObj->whereTime("create_time", '>=', $startDate);
        }
        if ($endDate != '') {
            $ufmWObj->whereTime("create_time", '<=', $endDate);
        }
        $imgObj = $ufmWObj->order("create_time", 'desc')->paginate(['page' => $currentPage, 'list_rows' => $pageSize]);
        $rdata = [
            "current_page" => $imgObj->currentPage(),
            "total" => intval($imgObj->total()),
            "per_page" => intval($pageSize),
            "groupId" => intval($groupId),
            "data" => $imgObj->all()
        ];
        return $rdata;
    }

    // 获取全部图片分组
    private function groupList($param)
    {
        $groupId = $param["groupId"];
        $groupId = intval(($param['groupId'] || $param['groupId'] == '0') ? $param['groupId'] : -1);

        $groupList = DictTypeModel::where('dict_type', 'fox_pic_group_type')->with('dictDatas')->find()->dictDatas;
        $state = 0;
        if ($groupId == -1) {
            $state = 1;
        }
        $rGroupList = [['id' => 0, 'title' => '全部', 'type' => 'all', 'count' => 0, 'state' => $state,  'groupId' => -1]];
        $total = UploadFilesModel::count();
        foreach ($groupList as $key => $item) {
            $state = 0;
            if ($groupId == $item->dict_value) {
                $state = 1;
            }
            $rGroupList[($key + 1)] = ['id' => $item->dict_code, 'title' => $item->dict_label, 'type' => $item->type, 'count' => $item->count, 'state' => $state, 'groupId' => intval($item->dict_value)];
        }
        $rGroupList[0]['count'] = $total;
        return $rGroupList;
    }

    // 通过curl方式获取制定的图片到本地
    private function getImg($url = "", $filename = "")
    {
        $imageUtil = new ImageUtil();
        $fileName = $imageUtil->download($url, $filename); //本地文件全路径
        if (empty($fileName)) {
            return ['code' => 0, 'msg' => "拉取文件失败"];
        }
        $file = new File($fileName);
        $result = $imageUtil->upload($file);
        if ($result['code'] == 1) {
            unlink($fileName);
            return $result;
        } else {
            return ['code' => 0, 'msg' => "上传失败"];
        }
    }

    // 更新图片组
    private function updateImageGroup()
    {
        try {
            //更新上传图片数
            $groupList = DictTypeModel::where('dict_type', 'fox_pic_group_type')->with('dictDatas')->find()->dictDatas;
            foreach ($groupList as $key => $item) {
                $dict_value = $item->dict_value; //模型分组
                $count = 0;
                if ($dict_value == 1) { //本地图片
                    $count = UploadFilesModel::where("storage", 0)->count();
                } elseif ($dict_value == 2) { //OSS图片
                    $count = UploadFilesModel::where("storage", 1)->count();
                } else {
                    $count = UploadFilesModel::where("file_group_type", $dict_value)->count();
                }
                $dicytData = (new DictDataModel())->where(['dict_type' => "fox_pic_group_type", "dict_value" => $dict_value])->find();
                if ($dicytData) {
                    $dicytData->count = $count;
                    $dicytData->save();
                }
            }
        } catch (\ErrorException $e) {
        }
    }

    // 文件管理
    public function picManager()
    {
        $param = $this->request->param();
        $event = $param["event"]; //行为
        $startDate = $param["startDate"] ?? "";
        $param["startDate"] = $startDate;
        $endDate = $param["endDate"] ?? "";
        $param["endDate"] = $endDate;
        $groupId = $param["groupId"];
        $param["endDate"] = $endDate;
        $type = $param["type"];

        $msg = "失败"; //返回信息

        //水印状态
        $isStamped = 0;
        $watermark = null;
        $watermarkArr = Db::name("watermark")->select()->toArray();
        if (sizeof($watermarkArr) > 0) {
            $watermark = $watermarkArr[0];
            $isStamped = $watermark['status'];
        }

        if ($event == "init") { //初始化
            $imgData = $this->imgList($param); //本地
            $localImgList = $imgData["data"];
            $localPagination = [
                "pageSize" => $imgData['per_page'],
                "total" => $imgData['total'],
                "currentPage" => $imgData['current_page'],
                "groupId" => $imgData['groupId']
            ];
            $groupList = $this->groupList($param); //分组
            $imageUtil =  new ImageUtil();
            $limitSize = $imageUtil->getSize();
            $result = ["groupList" => $groupList, "localImgList" => $localImgList, "localPagination" => $localPagination, "isStamped" => $isStamped, 'limitSize' => $limitSize];
            $this->success("成功", "", $result);
        } elseif ($event == "change") { //重新拉去数据
            $handle = $param["handle"]; //行为
            if ($handle == "get") {
                $groupList = $this->groupList($param); //分组
                if ($type == "local") {
                    $imgDa = $this->imgList($param); //本地
                    $result = [
                        "total" => $imgDa["total"],
                        "currentPage" => $imgDa["current_page"],
                        "pageSize" => $imgDa["per_page"],
                        "imgList" => $imgDa["data"],
                        "groupId" => $groupId,
                        "groupList" => $groupList,
                        "type" => $type,
                        "handle" => $handle,
                        "startDate" => $startDate,
                        "endDate" => $endDate,
                        "extractedUrl" => ""
                    ];
                    $this->success("成功", "", $result);
                } elseif ($type == "extract") {
                    $groupId = 0;
                    $url = $param["extractUrl"];
                    $result = $this->getImg($url);
                    if ($result["code"] == 0) {
                        return $result;
                    }
                    $result = [
                        "total" => 0,
                        "currentPage" => 0,
                        "pageSize" => 0,
                        "imgList" => 0,
                        "groupId" => $groupId,
                        "groupList" => $groupList,
                        "type" => $type,
                        "handle" => $handle,
                        "startDate" => $startDate,
                        "endDate" => $endDate,
                        "extractedUrl" => $result["file"],
                        "id" => $result["id"]

                    ];
                    $this->success("成功", "", $result);
                }
            } elseif ($handle == "upload") {
                $imageUtil =  new ImageUtil();
                if ($imageUtil->validationRemote()) { //远程
                    $param['storage'] = 1; //阿里云暂时没配置
                } else {
                    $param['storage'] = 0; //本地
                }
                if ($groupId == 1) { //本地图片
                    if ($imageUtil->validationRemote()) { //远程
                        $this->error("上传失败，请在站点中的附件设置关闭远程上传");
                    }
                } else if ($groupId == 2) { //OSS图片
                    if (!$imageUtil->validationRemote()) { //远程
                        $this->error("上传失败，请在站点中的附件设置开启远程上传");
                    }
                }

                $files = request()->file('file');
                $imageUtil =  new ImageUtil();
                $errFileInfo = [];
                $upload_type = $param["upload_type"] ?? 0;
                foreach ($files as $file) {
                    if (is_file($file)) {
                        $info = $imageUtil->upload($file, $groupId, "image", 1, "files", $upload_type);
                        if ($info["code"] == 0) {
                            array_push($errFileInfo, $info);
                        }
                    }
                }
                if (sizeof($errFileInfo) > 0) {
                    $result = [
                        'code' => 0,
                        'msg'  => $errFileInfo[0]['msg'],
                        'data' => $errFileInfo,
                    ];
                    return json($result);
                }

                $imgData = $this->imgList($param);
                $this->updateImageGroup(); //更新图片组中的图片数
                $groupList = $this->groupList($param); //分组
                $result = [
                    "total" => $imgData["total"],
                    "currentPage" => $imgData["current_page"],
                    "pageSize" => $imgData["per_page"],
                    "imgList" => $imgData["data"],
                    "groupId" => $groupId,
                    "groupList" => $groupList,
                    "type" => $type,
                    "handle" => $handle,
                    "startDate" => $startDate,
                    "endDate" => $endDate,
                    "extractedUrl" => "",
                    "isStamped" => $isStamped
                ];
                $this->success("上传成功", "", $result);
            } elseif ($handle == "deleteGroup") {
                $dictValue = intval($groupId);
                $ddm =  DictDataModel::where(['dict_value' => $dictValue, 'dict_type' => 'fox_pic_group_type'])->find();
                if ($ddm && $ddm['type'] == "system") {
                    return json(["code" => 0, "msg" => "系统分组不允许删除"]);
                }
                $ddm->delete();
                $groupList = $this->groupList($param); //分组
                $imgData = $this->imgList($param); //本地
                $result = [
                    "total" => $imgData["total"],
                    "currentPage" => $imgData["current_page"],
                    "pageSize" => $imgData["per_page"],
                    "imgList" => $imgData["data"],
                    "groupId" => $groupId,
                    "groupList" => $groupList,
                    "type" => $type,
                    "handle" => $handle,
                    "startDate" => $startDate,
                    "endDate" => $endDate,
                    "extractedUrl" => "",
                    "isStamped" => $isStamped
                ];
                $this->success("成功", "", $result);
            } elseif ($handle == "addGroup" || $handle == "modifyGroup") {
                if (!empty($groupId) && !empty($param["groupName"])) {
                    $dictDataModel = DictDataModel::where(['dict_value' => $groupId, 'dict_type' => 'fox_pic_group_type'])->find();
                    $dictDataModel->dict_label = $param['groupName'];
                    $dictDataModel->save();
                } else {
                    //先查分组
                    $dictList = DictDataModel::field('dict_value')->where(['dict_type' => 'fox_pic_group_type'])->select();
                    $dict_value = 0;
                    foreach ($dictList as $dict) {
                        $dictValue = intval($dict['dict_value']);
                        if ($dict_value < $dictValue) {
                            $dict_value = $dictValue;
                        }
                    }
                    $saveData = ['dict_label' => $param['groupName']];
                    $saveData['dict_value'] = intval($dict_value) + 1;
                    $saveData['dict_sort'] = intval($dict_value) + 2;
                    $saveData['dict_type'] = 'fox_pic_group_type';
                    $saveData['remark'] = '图片添加分组';
                    $saveData['type'] = 'custom';
                    $dictDataModel = new DictDataModel();
                    $isSave = $dictDataModel->save($saveData);
                    if ($isSave) {
                    } else {
                        return json(['code' => 0, "添加分组,失败！"]);
                    }
                    $rd = $dictDataModel->where('dict_code', $dictDataModel['dict_code'])->find();
                    $groupId = $rd["dict_value"];
                    $param["groupId"] = $groupId;
                }
                $groupList = $this->groupList($param); //分组
                $imgDa1 = $this->imgList($param); //本地
                $result = [
                    "total" => $imgDa1["total"],
                    "currentPage" => $imgDa1["current_page"],
                    "pageSize" => $imgDa1["per_page"],
                    "imgList" => $imgDa1["data"],
                    "groupId" => $groupId,
                    "groupList" => $groupList,
                    "type" => $type,
                    "handle" => $handle,
                    "startDate" => $startDate,
                    "endDate" => $endDate,
                    "extractedUrl" => "",
                    "isStamped" => $isStamped
                ];
                $this->success("成功", "", $result);
            } elseif ($handle == "deleteImage") {
                $this->delete($param);
                $imgData6 = $this->imgList($param); //本地
                $this->updateImageGroup(); //更新图片组中的图片数
                $groupList = $this->groupList($param); //分组
                $result = [
                    "total" => $imgData6["total"],
                    "currentPage" => $imgData6["current_page"],
                    "pageSize" => $imgData6["per_page"],
                    "imgList" => $imgData6["data"],
                    "groupId" => $groupId,
                    "groupList" => $groupList,
                    "type" => $type,
                    "handle" => $handle,
                    "startDate" => $startDate,
                    "endDate" => $endDate,
                    "extractedUrl" => "",
                    "isStamped" => $isStamped
                ];
                $this->success("成功", "", $result);
            } else {
                $msg = "缺少handle参数";
            }
        } elseif ($event == "stamped") { //是否开启水印
            $isStamped = $param['isStamped'];
            if ($watermark == null) { //没有保存水印值
                $r = Db::name('watermark')->save(['status' => $isStamped]);
            } else {
                if ($isStamped != $watermark['status']) {
                    $r = Db::name('watermark')->update(['id' => $watermark['id'], 'status' => $isStamped]);
                } else {
                    $r = 1;
                }
            }
            if ($r) {
                $this->success("成功");
            }
        } else {
            $msg = "缺少event参数";
        }
        $this->error($msg);
    }

    public function delete($param)
    {
        $id = intval($param['id']);
        !($id > 0) && $this->error('参数错误');
        $file_data = UploadFilesModel::find($id);
        if ($file_data) {
            $storage = $file_data->getData('storage');
            if ($storage == 1) {
                $oss = new Oss();
                //                $key = explode(xn_cfg('upload.oss_endpoint').'/', $file_data->url);
                $key = explode($oss->getOssDomain() . '/', $file_data->url);
                //删除远程oss文件
                if (isset($key[1])) {
                    $oss->delete($key[1]);
                }
            } elseif ($storage == 2) {
                //删除七牛远程文件
                $domain = xn_cfg('upload.qiniu_domain');
                $key = str_replace($domain, '', $file_data->url);
                $qiniu = new Qiniu();
                $qiniu->delete($key);
            } else {

                //删除本地服务器文件
                $file_path = root_path() . "/" . ltrim($file_data->url, '/');
                if (file_exists($file_path)) {
                    unlink($file_path);
                }
            }
            UploadFilesModel::destroy($id);
            xn_add_admin_log('删除文件', "pic");
        }
        return "删除完成";
    }
}
