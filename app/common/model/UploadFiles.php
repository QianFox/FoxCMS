<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   18:32
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   18:32
 */

namespace app\common\model;

use app\common\lib\Oss;
use app\common\lib\Qiniu;
use think\exception\ValidateException;
use think\facade\Filesystem;
use think\facade\Session;
use think\File;
use think\Image;
use think\Model;

class UploadFiles extends Model
{
    protected $autoWriteTimestamp = "datetime";
    protected $append = ['storagetext'];

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

    public function getStoragetextAttr($value, $data)
    {
        $status = [0 => '本地', 1 => 'OSS', 2 => '七牛'];
        return $status[$data['storage']];
    }

    public function getFileSizeAttr($value)
    {
        return xn_file_size($value);
    }

    public function saveUpload($file, $folder_name = 'files', $app = 1)
    {
        try {
            $allowedExts = array("gif", "jpeg", "jpg", "png");
            //文件后缀名
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (!in_array($ext, $allowedExts)) {
                return ['code' => 0, 'msg' => $file->getFilename() . "非法的文件格式"];
            }
            //配置信息
            $config = xn_cfg('upload');
            //存储类型
            $storage = $config['storage'];
            $groupId = 0;
            if ($groupId == '-1') {
                $groupId = 0;
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
                $folder = config('filesystem.disks.folder') . '/' . $folder_name; //存文件目录
                $savename = Filesystem::disk('public')->putFile($folder, $file);
                if (!$savename) {
                    return ['code' => 0, 'msg' => '上传失败'];
                }
                $file_path = '/' . str_replace("\\", "/", $savename);
            }
            $file_path = '/' . 'public' . $file_path;
            //记录入文件表
            $insert_data = [
                'url' => $file_path,
                'storage' => $storage,
                'app' => $app,
                'user_id' => intval(Session::get('admin_auth.id')),
                'file_name' => $file->getFilename(),
                'file_size' => $file->getSize(),
                'file_group_type' => $groupId,
                'file_type' => 'image',
                'extension' => strtolower(pathinfo($file->getFilename(), PATHINFO_EXTENSION))
            ];
            $id = self::insertGetId($insert_data);
            return ['code' => 1, 'file' => $file_path, 'id' => $id];
        } catch (ValidateException $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }

    public function upload($folder_name = 'files', $app = 1, $groupId = "", $storage = "", $type = 1)
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
                $groupId = $param['groupId'];
            }
            if ($groupId == '-1') {
                $groupId = 0;
            }
            //图片水印处理
            if (in_array(strtolower($ext), ['png', 'jpg', 'jpeg', 'gif', 'bmp'])) {
                if (self::setWater($file, $param['water']) === false) {
                    return ['code' => 0, 'msg' => '水印配置有误'];
                }
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
                $folder = config('filesystem.disks.folder') . '/' . $folder_name; //存文件目录
                $savename = Filesystem::disk('public')->putFile($folder, $file);
                if (!$savename) {
                    return ['code' => 0, 'msg' => '上传失败'];
                }
                $file_path = '/' . str_replace("\\", "/", $savename);

                //缩略图
                if ($param['thumb'] != '') {
                    self::createThumb($file, $param['thumb'], $savename);
                }
            }

            $file_path = '/' . 'public' . $file_path;
            //记录入文件表
            $insert_data = [
                'url' => $file_path,
                'storage' => $storage,
                'app' => $app,
                'user_id' => intval(Session::get('admin_auth.id')),
                'file_name' => $file->getOriginalName(),
                'file_size' => $file->getSize(),
                'file_group_type' => $groupId,
                'file_type' => 'image',
                'extension' => strtolower(pathinfo($file->getOriginalName(), PATHINFO_EXTENSION))
            ];
            $id = self::insertGetId($insert_data);
            return ['code' => 1, 'file' => $file_path, 'id' => $id];
        } catch (ValidateException $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }

    public function uploadFile($folder_name = 'files', $app = 1)
    {
        try {
            $file = request()->file('file');
            //文件后缀名
            $ext = $file->getOriginalExtension();
            //配置信息
            $config = xn_cfg('upload');
            //存储类型
            $storage = $config['storage'];

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
                $folder = config('filesystem.disks.folder') . $folder_name; //存文件目录
                $savename = Filesystem::disk('public')->putFile($folder, $file);
                if (!$savename) {
                    return ['code' => 0, 'msg' => '上传失败'];
                }
                $file_path = '/' . str_replace("\\", "/", $savename);
            }

            $file_path = '/' . 'public' . $file_path;
            //记录入文件表
            $insert_data = [
                'url' => $file_path,
                'storage' => $storage,
                'app' => $app,
                'user_id' => intval(Session::get('admin_auth.id')),
                'file_name' => $file->getOriginalName(),
                'file_size' => $file->getSize(),
                'file_group_type' => 0,
                'file_type' => 'file',
                'extension' => strtolower(pathinfo($file->getOriginalName(), PATHINFO_EXTENSION))
            ];
            $id = self::insertGetId($insert_data);
            return ['code' => 1, 'file' => $file_path, 'id' => $id];
        } catch (ValidateException $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }

    public function getImg($filename = "", $folder_name = 'files', $app = 0)
    {
        try {
            $file = new File($filename);
            //文件后缀名
            $ext = pathinfo(basename($filename), PATHINFO_EXTENSION);
            //配置信息
            $config = xn_cfg('upload');
            //存储类型
            $storage = $config['storage'];
            $groupId = 0;
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
                //                $file_path = '/' . str_replace("\\","/",$filename);
                $file_path = '/' . $filename;
            }
            $file_path = '/' . 'public' . $file_path;
            //记录入文件表
            $insert_data = [
                'url' => $file_path,
                'storage' => $storage,
                'app' => $app,
                'user_id' => intval(Session::get('admin_auth.id')),
                'file_name' => basename($filename),
                'file_size' => $file->getSize(),
                'file_group_type' => $groupId,
                'file_type' => 'image',
                'extension' => strtolower(pathinfo(basename($filename), PATHINFO_EXTENSION))
            ];
            $id = self::insertGetId($insert_data);
            return ['code' => 1, 'file' => $file_path, 'id' => $id, 'storage' => $storage];
        } catch (ValidateException $e) {
            return ['code' => 0, 'msg' => $e->getMessage()];
        }
    }

    // 图片添加水印
    public function setWater($file, $is_water = null)
    {
        if ($is_water == '0') {
            return true;
        }
        $config = xn_cfg('upload');
        //图片水印
        if ($config['is_water'] == 1 || $is_water == '1') {
            $water_path = xn_root() . "/" . ltrim($config['water_img'], '/');
            if (!file_exists($water_path)) {
                return false;
            }
            $image = Image::open($file);
            $image->water($water_path, $config['water_locate'], $config['water_alpha']);
            $image->save($file->getPathName());
        }
        return true;
    }

    /**
     * 生成缩略图
     * @param object $file
     * @param string $thumb 缩略图格式,如 200,200  多张用 | 好隔开
     * @param string $save_path 保存文件的路径
     */
    public function createThumb($file, $thumb, $save_path)
    {
        $thumbs = explode('|', $thumb);
        foreach ($thumbs as $w_h) {
            $arr = explode('.', $save_path);
            $w_h = explode(',', $w_h);
            $thumb_name = $arr[0] . '_' . $w_h[0] . '_' . $w_h[1] . '.' . $arr[1];
            $image = Image::open($file);
            $image->thumb($w_h[0], $w_h[1], 3);
            $image->save(xn_root() . "\\" . $thumb_name);
        }
    }

    /**
     * 下载文件到本地
     * @param string $url
     * @param string $filename
     * @param string $path 路径文件
     */
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