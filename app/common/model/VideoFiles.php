<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   18:34
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   18:34
 */

namespace app\common\model;

use app\common\lib\Oss;
use app\common\lib\Qiniu;
use app\common\util\VideoUtil;
use think\exception\ValidateException;
use think\facade\Filesystem;
use think\facade\Session;
use think\Model;

class VideoFiles extends Model
{
    protected $autoWriteTimestamp = "datetime";

    public function getUrlAttr($value)
    {
        if (empty($value)) {
            return "";
        }
        if (preg_match('/(http:\/\/)|(https:\/\/)/i', $value)) { //判断是否存在
            return $value;
        } else {
            if (!str_starts_with($value, "/")) {
                return "/" . $value;
            }
            return $value;
        }
    }

    public function getAppAttr($value)
    {
        $status = [0 => '前台', 1 => '后台'];
        return $status[$value];
    }

    public function getStorageAttr($value)
    {
        $status = [0 => '本地', 1 => 'OSS', 2 => '七牛'];
        return $status[$value];
    }

    public function getFileSizeAttr($value)
    {
        return xn_file_size($value);
    }

    public function upload($folder_name = 'files', $app = 1, $storage = "", $type = 1)
    {
        try {
            $param = request()->param();
            $file = request()->file('file');
            //文件后缀名
            $ext = $file->getOriginalExtension();
            //配置信息
            //            $config = xn_cfg('upload');
            //存储类型
            if ($type == 1) {
                $storage = $param['storage'];
            }
            if ($storage == 1) {
                //上传到阿里云oss
                $oss = new Oss();
                $file_path = $oss->putFile($file, $err, $folder_name);
                if (!$file_path) {
                    return ['code' => 0, 'msg' => $err];
                }
            } elseif ($storage == 2) {
                //上传到七牛云
                $qiniu = new Qiniu();
                $savename = date('Ymd') . '/' . md5((string) microtime(true)) . '.' . $ext;
                $file_path = $qiniu->putFile($savename, $file->getRealPath());
                if (!$file_path) {
                    return ['code' => 0, 'msg' => '上传失败'];
                }
            } else {
                //上传到本地服务器
                $folder =  '/' . $folder_name; //存文件目录
                $savename = Filesystem::disk('public')->putFile($folder, $file);
                if (!$savename) {
                    return ['code' => 0, 'msg' => '上传失败'];
                }
                $file_path = '/' . str_replace("\\", "/", $savename);
            }
            $file_path = config('filesystem.disks.folder') . $file_path;
            $videoFile = root_path() . $file_path;
            $videoFileInfo = (new VideoUtil())->getVideoInfo($videoFile);
            $file_type = "video";
            $duration = "";
            if ($videoFileInfo) {
                $file_type = $videoFileInfo['mime_type'];
                $duration = $videoFileInfo['playtime_string'];
            }

            //记录入文件表
            $insert_data = [
                'duration' => $duration,
                'url' => $file_path,
                'storage' => $storage,
                'app' => $app,
                'user_id' => intval(Session::get('admin_auth.id')),
                'file_name' => $file->getOriginalName(),
                'file_size' => $file->getSize(),
                'file_type' => $file_type,
                'extension' => strtolower(pathinfo($file->getOriginalName(), PATHINFO_EXTENSION))
            ];
            $id = self::insertGetId($insert_data);
            return ['code' => 1, 'file' => $file_path, 'id' => $id];
        } catch (ValidateException $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }

    // 下载文件到本地
    public function download($url = "", $filename = "", $path = "")
    {
        $filePath = config('filesystem.disks.folder') . DIRECTORY_SEPARATOR . date("Ymd");
        if (!empty($path)) {
            $filePath = $path;
        }
        if (!is_dir($filePath)) {
            tp_mkdir($filePath);
        }
        if (empty($filename)) {
            //上传到本地服务器
            $filename =  basename($url);
        }
        $filename = $filePath . DIRECTORY_SEPARATOR . $filename;
        $content = get_url_content($url);
        file_put_contents($filename, $content);
        return $filename;
    }
}