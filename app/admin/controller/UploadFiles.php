<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   15:39
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   15:39
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\lib\Oss;
use app\common\lib\Qiniu;
use app\common\util\ImageUtil;
use app\common\model\UploadFiles as UploadFilesModel;

class UploadFiles extends AdminBase
{
    protected $noAuth = ['upload', 'select'];

    public function index()
    {
        $param = $this->request->param();
        halt($param);
        $model = new UploadFilesModel();
        //构造搜索条件
        if ($param['storage'] != '') {
            $model = $model->where('storage', $param['storage']);
        }
        if ($param['start_date'] != '' && $param['end_date'] != '') {
            $model = $model->whereBetweenTime('create_time', $param['start_date'], $param['end_date']);
        }
        $list = $model->order('id desc')->paginate(['query' => $param]);
        return view('', ['list' => $list]);
    }

    public function select()
    {
        $list = UploadFilesModel::where('app', 1)->where('user_id', $this->getAdminId())->order('id desc')->paginate(['list_rows' => 24, 'query' => $this->request->param()]);
        $num = intval($this->request->get('num'));
        return view('', ['list' => $list, 'num' => $num]);
    }

    public function delete()
    {
        $id = intval($this->request->get('id'));
        !($id > 0) && $this->error('参数错误');
        $file_data = UploadFilesModel::find($id);
        $storage = $file_data->getData('storage');
        if ($storage == 1) {
            $key = explode(xn_cfg('upload.oss_endpoint') . '/', $file_data->url);
            //删除远程oss文件
            if (isset($key[1])) {
                $oss = new Oss();
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
            $file_path = xn_root() . "/" . ltrim($file_data->url, '/');
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        UploadFilesModel::destroy($id);
        xn_add_admin_log('删除文件');
        $this->success('删除成功');
    }

    public function upload()
    {
        // 允许上传的图片后缀
        $allowedExts = array("gif", "jpeg", "jpg", "png");
        $temp = explode(".", $_FILES["file"]["name"]);
        $extension = end($temp);     // 获取文件后缀名
        $file_tmp  = $_FILES["file"]["tmp_name"];
        if ((($_FILES["file"]["type"] == "image/gif")
                || ($_FILES["file"]["type"] == "image/jpeg")
                || ($_FILES["file"]["type"] == "image/jpg")
                || ($_FILES["file"]["type"] == "image/pjpeg")
                || ($_FILES["file"]["type"] == "image/x-png")
                || ($_FILES["file"]["type"] == "image/png"))
            //            && ($_FILES["file"]["size"] < 204800)   // 小于 200 kb
            && in_array($extension, $allowedExts) && getimagesize($file_tmp)
        ) {
            if ($_FILES["file"]["error"] > 0) {
                return ['code' => 0, 'msg' => "错误：: " . $_FILES["file"]["error"]];
            } else {
                // 判断当期目录下的 upload 目录是否存在该文件
                // 如果没有 upload 目录，你需要创建它，upload 目录权限为 777
                if (file_exists("upload/" . $_FILES["file"]["name"])) {
                    return ['code' => 0, 'msg' => $_FILES["file"]["name"] . " 文件已经存在。 "];
                } else {
                    $result = UploadFilesModel::upload();
                    return json($result);
                }
            }
        } else {
            return ['code' => 0, 'msg' => $_FILES["file"]["name"] . "非法的文件格式"];
        }
    }

    // 获取图片
    public function imgList()
    {
        $param = $this->request->param();
        $startDate = $param['startDate'];
        $endDate = $param['endDate'];
        $curPage = intval($param['curPage']);
        $pageSize = intval($param['pageSize']);;
        $storage = strval($param['storage']); //存储地方  0：本地; 1:阿里云
        $groupId = intval($param['groupId']); //分组
        $where = ['storage' => strval($storage)];
        if (array_key_exists('groupId', $param) &&  $param['groupId'] != -1) {
            $where['file_group_type'] = $param['groupId'];
        }
        $ufmWObj = UploadFilesModel::field('id, file_name as name, url')->where($where);
        if ($startDate != '') {
            $ufmWObj->whereTime("create_time", '>=', $startDate);
        }
        if ($endDate != '') {
            $ufmWObj->whereTime("create_time", '<=', $endDate);
        }
        $imgObj = $ufmWObj->order("create_time", 'desc')->paginate(['page' => $curPage, 'list_rows' => $pageSize]);

        $rdata = [
            "current_page" => $imgObj->currentPage(),
            "total" => intval($imgObj->total()),
            "per_page" => intval($pageSize),
            "groupId" => intval($groupId),
            "data" => $imgObj->all()
        ];

        $this->success("查询成功", null, $rdata);
    }

    // 通过curl方式获取制定的图片到本地
    public function getImg($url = "", $filename = "")
    {
        $fileName = UploadFilesModel::downloadImg($url, $filename); //本地文件全路径
        if (empty($fileName)) {
            return ['code' => 0, 'msg' => "拉取文件失败"];
        }
        $result = UploadFilesModel::getImg($fileName);
        return json($result);
    }

    // 上传图片
    public function upimg()
    {
        $file = request()->file('file');
        $imageUtil =  new ImageUtil();
        $upload_type = 0;
        $location = "";
        if (is_file($file)) {
            $info = $imageUtil->upload($file, 0, "image", 1, "files", $upload_type);
            if ($info['code'] == 1) {
                $location = $info['file'];
            } else {
                return json_encode($info, JSON_UNESCAPED_UNICODE);
            }
        }
        return json_encode(['location' => $location]);
    }
}