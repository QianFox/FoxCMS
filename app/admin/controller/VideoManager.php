<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   15:47
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   15:47
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\lib\Oss;
use app\common\lib\Qiniu;
use app\common\model\VideoFiles;
use app\common\util\VideoUtil;

// 视频管理器
class VideoManager extends AdminBase
{
    protected $noLogin = ["videoManagerTest"]; //不用登录

    // 获取视频文件
    private function videoList($param)
    {
        $startDate = $param['startDate'];
        $endDate = $param['endDate'];
        $currentPage = intval($param['currentPage']);
        $pageSize = intval($param['pageSize']);;
        //        $storage = intval($param['storage']?$param['storage']:0);//存储地方  0：本地; 1:阿里云
        //        $where = ['storage'=> intval($storage)];
        $where = [];
        $ufmWObj = VideoFiles::field('id, file_name as name, url as videoSrc, duration, file_size as size, pic as imgSrc')->where($where);
        if (!empty($startDate)) {
            $ufmWObj->whereTime("create_time", '>=', $startDate);
        }
        if (!empty($endDate)) {
            $ufmWObj->whereTime("create_time", '<=', $endDate);
        }
        $imgObj = $ufmWObj->order("create_time", 'desc')->paginate(['page' => $currentPage, 'list_rows' => $pageSize]);
        $rdata = [
            "current_page" => $imgObj->currentPage(),
            "total" => intval($imgObj->total()),
            "per_page" => intval($pageSize),
            "data" => $imgObj->all()
        ];
        return $rdata;
    }

    // 文件管理
    public function videoManager()
    {
        $param = $this->request->param();
        $event = $param["event"]; //行为

        $currentPage = $param["currentPage"] ?? 1;
        $param["currentPage"] = $currentPage;
        $pageSize = $param["pageSize"] ?? 10;
        $param["pageSize"] = $pageSize;
        $startDate = $param["startDate"] ?? "";
        $param["startDate"] = $startDate;
        $endDate = $param["endDate"] ?? "";
        $param["endDate"] = $endDate;
        $groupId = intval($param["groupId"]);
        $param["endDate"] = $endDate;
        $type = $param["type"];
        $handle = $param["handle"]; //行为

        if ($event == "init") { //初始化
            //            $param["storage"] = 0;
            $videoDa = $this->videoList($param); //本地
            $videoUtil = new VideoUtil();
            $limitSize = $videoUtil->getSize();
            $result = [
                "total" => $videoDa["total"],
                "currentPage" => $videoDa["current_page"],
                "pageSize" => $videoDa["per_page"],
                "videoList" => $videoDa["data"],
                "groupId" => $groupId,
                "type" => $type,
                "handle" => $handle,
                "startDate" => $startDate,
                "endDate" => $endDate,
                'limitSize' => $limitSize
            ];
            $this->success("查询成功", "", $result);
        } elseif ($event == "change") { //重新拉去数据
            if ($handle == "upload") {
                $temp = explode(".", $_FILES["file"]["name"]);
                $extension = end($temp);     // 获取文件后缀名
                $fileType = $_FILES["file"]["type"]; //文件类型
                $videoUtil = new VideoUtil();
                if (!$videoUtil->validationSuffix($extension)) {
                    $this->error("上传的媒体文件必须是" . $videoUtil->suffix());
                }
                if ($extension == "mp4") {
                    if (($fileType == "video/mp4")) {
                        if ($_FILES["file"]["error"] > 0) {
                            $this->error("上传的媒体文件出错");
                        }
                    } else {
                        $this->error("上传的媒体文件内容必须是" . $videoUtil->suffix());
                    }
                }

                $upload_type = $param["upload_type"] ?? 0;
                $file = request()->file('file');
                $info = $videoUtil->upload($file, "videos", 1, $upload_type);
                if ($info["code"] == 0) {
                    $this->error($info["msg"]);
                }
                $videoData = $this->videoList($param); //本地
                $result = [
                    "total" => $videoData["total"],
                    "currentPage" => $videoData["current_page"],
                    "pageSize" => $videoData["per_page"],
                    "videoList" => $videoData["data"],
                    "type" => $type,
                    "handle" => $handle,
                    "startDate" => $startDate,
                    "endDate" => $endDate
                ];
                $this->success("查询成功", "", $result);
            } elseif ($handle == "get") {
                //                $param["storage"] = 0;
                $videoDa = $this->videoList($param); //本地
                $result = [
                    "total" => $videoDa["total"],
                    "currentPage" => $videoDa["current_page"],
                    "pageSize" => $videoDa["per_page"],
                    "videoList" => $videoDa["data"],
                    "groupId" => $groupId,
                    "type" => $type,
                    "handle" => $handle,
                    "startDate" => $startDate,
                    "endDate" => $endDate
                ];
                $this->success("查询成功", "", $result);
            } elseif ($handle == "deleteVideo") {
                $this->delete($param);
                $videoData = $this->videoList($param); //本地
                $result = [
                    "total" => $videoData["total"],
                    "currentPage" => $videoData["current_page"],
                    "pageSize" => $videoData["per_page"],
                    "videoList" => $videoData["data"],
                    "type" => $type,
                    "handle" => $handle,
                    "startDate" => $startDate,
                    "endDate" => $endDate,
                ];
                $this->success("查询成功", "", $result);
            }
        }
    }

    //    public function videoManagerTest(){
    //        $param = $this->request->param();
    //        $event = $param["event"];//行为
    //
    //        $currentPage = $param["currentPage"]??1;
    //        $param["currentPage"] = $currentPage;
    //        $pageSize = $param["pageSize"]??10;
    //        $param["pageSize"] = $pageSize;
    //        $startDate = $param["startDate"]??"";
    //        $param["startDate"] = $startDate;
    //        $endDate = $param["endDate"]??"";
    //        $param["endDate"] = $endDate;
    //        $groupId = intval($param["groupId"]);
    //        $param["endDate"] = $endDate;
    //        $type = $param["type"];
    //        $handle = $param["handle"];//行为
    //
    //        if($event == "init") {//初始化
    ////            $param["storage"] = 0;
    //            $videoDa = $this->videoList($param);//本地
    //            $videoUtil = new VideoUtil();
    //            $limitSize = $videoUtil->getSize();
    //            $result = [
    //                "total"=>$videoDa["total"],
    //                "currentPage"=>$videoDa["current_page"],
    //                "pageSize"=>$videoDa["per_page"],
    //                "videoList"=>$videoDa["data"],
    //                "groupId"=>$groupId,
    //                "type"=>$type,
    //                "handle"=>$handle,
    //                "startDate"=>$startDate,
    //                "endDate"=>$endDate,
    //                'limitSize'=> $limitSize
    //            ];
    //            $result = [
    //                'code' => 1,
    //                'msg'  => "查询成功",
    //                'data' => $result
    //            ];
    //            return json($result);
    //        }elseif($event == "change") {//重新拉去数据
    //            if ($handle == "upload"){
    //                $temp = explode(".", $_FILES["file"]["name"]);
    //                $extension = end($temp);     // 获取文件后缀名
    //                $fileType = $_FILES["file"]["type"];//文件类型
    //                $videoUtil = new VideoUtil();
    //                if(!$videoUtil->validationSuffix($extension)){
    //                    $this->error("上传的媒体文件必须是".$videoUtil->suffix());
    //                }
    //                if($extension == "mp4"){
    //                    if (($fileType == "video/mp4")){
    //                        if ($_FILES["file"]["error"] > 0){
    //                            $this->error("上传的媒体文件出错");
    //                        }
    //                    }else{
    //                        $this->error("上传的媒体文件内容必须是".$videoUtil->suffix());
    //                    }
    //                }
    //
    //                $upload_type = $param["upload_type"]??0;
    //                $file = request()->file('file');
    //                $info = $videoUtil->upload($file, "videos", 1, $upload_type);
    //                if($info["code"] == 0){
    //                    $this->error($info["msg"]);
    //                }
    //                $videoData = $this->videoList($param);//本地
    //                $result = [
    //                    "total"=>$videoData["total"],
    //                    "currentPage"=>$videoData["current_page"],
    //                    "pageSize"=>$videoData["per_page"],
    //                    "videoList"=>$videoData["data"],
    //                    "type"=>$type,
    //                    "handle"=>$handle,
    //                    "startDate"=>$startDate,
    //                    "endDate"=>$endDate
    //                ];
    //                $result = [
    //                    'code' => 1,
    //                    'msg'  => "查询成功",
    //                    'data' => $result
    //                ];
    //                return json($result);
    //            }elseif ($handle == "get"){
    ////                $param["storage"] = 0;
    //                $videoDa = $this->videoList($param);//本地
    //                $result = [
    //                    "total"=>$videoDa["total"],
    //                    "currentPage"=>$videoDa["current_page"],
    //                    "pageSize"=>$videoDa["per_page"],
    //                    "videoList"=>$videoDa["data"],
    //                    "groupId"=>$groupId,
    //                    "type"=>$type,
    //                    "handle"=>$handle,
    //                    "startDate"=>$startDate,
    //                    "endDate"=>$endDate
    //                ];
    //                $result = [
    //                    'code' => 1,
    //                    'msg'  => "查询成功",
    //                    'data' => $result
    //                ];
    //                return json($result);
    //            }elseif ($handle == "deleteVideo"){
    //                $this->delete($param);
    //                $videoData = $this->videoList($param);//本地
    //                $result = [
    //                    "total"=>$videoData["total"],
    //                    "currentPage"=>$videoData["current_page"],
    //                    "pageSize"=>$videoData["per_page"],
    //                    "videoList"=>$videoData["data"],
    //                    "type"=>$type,
    //                    "handle"=>$handle,
    //                    "startDate"=>$startDate,
    //                    "endDate"=>$endDate,
    //                ];
    //                $result = [
    //                    'code' => 1,
    //                    'msg'  => "查询成功",
    //                    'data' => $result
    //                ];
    //                return json($result);
    //            }
    //
    //        }
    //
    //    }

    public function delete($param)
    {
        $id = intval($param['id']);
        !($id > 0) && $this->error('参数错误');
        $file_data = VideoFiles::find($id);
        $storage = $file_data->getData('storage');
        if ($storage == 1) {
            $oss = new Oss();
            //            $key = explode(xn_cfg('upload.oss_endpoint').'/', $file_data->url);
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
        VideoFiles::destroy($id);
        xn_add_admin_log("删除视频信息", "videoManager"); //添加日志
    }
}