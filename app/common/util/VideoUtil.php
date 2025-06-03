<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   19:10
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   19:10
 */

namespace app\common\util;

use app\common\lib\Oss;
use app\common\model\Attachment;
use app\common\model\VideoFiles;
use think\facade\Filesystem;
use think\facade\Session;

// 音视频工具类
class VideoUtil
{
    private $attachment; //附件设置
    private $allowMedias = ["mp4", "ogg", "webm", "mp3", "wav"]; //默认只允许上传媒体
    public function __construct()
    {
        $attachments = Attachment::select();
        if (sizeof($attachments) == 1) {
            $this->attachment = $attachments[0];
        }
    }

    // 支持上传文件后缀
    public function suffix()
    {
        $a_suffixs = $this->attachment['a_suffixs'];
        $f_suffixs_arr = explode(",", $a_suffixs);
        return $f_suffixs_arr;
    }

    /**
     * 验证文件类型
     * @param $fileType 文件类型 如mp3,mp4
     */
    public function validationSuffix($fileType)
    {
        if (in_array($fileType, $this->allowMedias)) {
            $a_suffixs = $this->attachment['a_suffixs'];
            if (empty($i_suffixs)) { //为空不限制
                return true;
            }
            $f_suffixs_arr = explode(",", $a_suffixs);
            if (sizeof($f_suffixs_arr) == 0) { //为0不限制
                return true;
            }
            return in_array($fileType, $f_suffixs_arr);
        } else {
            return false;
        }
    }

    // 配置的图片限制大小
    public function getSize()
    {
        return $this->attachment['a_file_size'];
    }

    /**
     * 验证大小
     * @param $size 单位kb
     */
    public function validationSize($size)
    {
        $a_file_size = $this->attachment['a_file_size'];
        if (empty($a_file_size)) { //为空不限制
            return true;
        }
        if ($size <= ($a_file_size * 1024 * 1024)) {
            return true;
        }
        return false;
    }

    // 验证后缀与大小
    public function validation($file)
    {
        //文件后缀名
        //记录入文件表
        $OriginalName = $file->getOriginalName();
        $extension = strtolower(pathinfo($OriginalName, PATHINFO_EXTENSION)); //后缀
        if (!$this->validationSuffix($extension)) { //验证图片类型
            return false;
        }
        $sizeZ = $file->getSize(); //字节
        if (!$this->validationSize($sizeZ)) { //验证图片大小
            return false;
        }
        return true;
    }

    // 验证是否开启远程 1:开启；0未开启
    public function validationRemote()
    {
        return ($this->attachment['is_remote']) == 1;
    }

    /**
     * 上传 根据路径
     * @param $file 文件
     * @param int $file_name 文件目录名
     * @param int $groupId 分组
     * @param string $fileType 文件类型
     * @param int $app 1:后台 0：前台
     * @param string $folder_name 文件路径
     * @param int $upload_type 上传类型 0：表示有远程就远程，没有就本地；1：就本地
     */
    public function upload($file, $folder_name = "videos", $app = 1, $upload_type = 0)
    {

        //记录入文件表
        $OriginalName = $file->getOriginalName();
        $extension = strtolower(pathinfo($OriginalName, PATHINFO_EXTENSION)); //后缀
        $sizeZ = $file->getSize(); //字节
        if (!$this->validationSize($sizeZ)) { //验证图片大小
            return ['code' => 0, 'msg' => '请上传小于等于' . $this->attachment['a_file_size'] . 'M的音频/视频'];
        }
        //上传到本地服务器
        //        $folder = "/".$folder_name."/";
        $folder = "/" . $folder_name . "/" . date("Ymd") . "/"; //根路径文件夹
        //创建文件夹
        $savePath = replaceSymbol(root_path() . config('filesystem.disks.folder') . $folder);
        if (!tp_mkdir($savePath)) {
            return "创建文件夹失败";
        }
        $filename = func_random_num(8, 16) . "." . $extension;
        $file->move($savePath, $filename);
        $savename = $folder . $filename;
        if (!$savename) {
            return ['code' => 0, 'msg' => '上传失败'];
        }
        $file_path = replaceSymbol($savename);
        $videoFile = root_path() . config('filesystem.disks.folder') . $file_path;
        $videoFileInfo = (new VideoUtil())->getVideoInfo($videoFile);
        $file_type = "video";
        $duration = "";
        if ($videoFileInfo) {
            $file_type = $videoFileInfo['mime_type'];
            $duration = $videoFileInfo['playtime_string'];
        }
        $storage = 0; //本地
        if (($upload_type == 0) && $this->validationRemote()) { //开启远程
            $storage = 1; //远程
            //上传到阿里云oss
            $oss = new Oss();
            $file_path = $oss->putFileByPath($file, $err, $folder_name, $videoFile);
            if (!$file_path) {
                return ['code' => 0, 'msg' => $err];
            }
            //删除本地文件
            if (file_exists($videoFile)) { //存在的话就删除
                unlink($videoFile);
            }
        } else {
            $file_path = config('filesystem.disks.folder') . $file_path;
        }
        if (!preg_match('/(http:\/\/)|(https:\/\/)/i', $file_path)) { //判断是否存在
            $file_path = "/" . $file_path;
        }
        //记录入文件表
        $insert_data = [
            'duration' => $duration,
            'url' => $file_path,
            'storage' => $storage,
            'app' => $app,
            'user_id' => intval(Session::get('admin_auth.id')),
            'file_name' => $OriginalName,
            'file_size' => $sizeZ,
            'file_type' => $file_type,
            'extension' => $extension,
            'pic' => '/static/images/video.png'
        ];
        $id = (new VideoFiles())->insertGetId($insert_data);
        return ['code' => 1, 'file' => $file_path, 'id' => $id, 'msg' => "上传成功", "storage" => $storage];
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
            $filename = basename($url);
        }
        $filename = $filePath . DIRECTORY_SEPARATOR . $filename;
        $content = get_url_content($url);
        file_put_contents($filename, $content);
        return $filename;
    }

    // 获取视频信息
    public function getVideoInfo($videoFile)
    {
        $getID3 = new \getID3();
        $videoFileInfo = $getID3->analyze($videoFile);
        return $videoFileInfo;
    }
}