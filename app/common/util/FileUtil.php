<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   18:56
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   18:56
 */

namespace app\common\util;

use app\common\lib\Oss;
use app\common\model\Attachment;
use app\common\model\UploadFiles;
use think\facade\Session;
use think\File;

// 文件工具类
class FileUtil
{
    private $allowFiles = ['doc', 'docx', 'pdf', 'txt', 'xls', 'xlsx', 'ppt', "zip", "rar"]; //默认只允许上传文件
    private $attachment; //附件设置
    public function __construct()
    {
        $attachments = Attachment::select();
        if (sizeof($attachments) == 1) {
            $this->attachment = $attachments[0];
        }
    }

    // 允许上传文件
    public function isAllowFile($file)
    {
        $extension = $this->getFileSuffix($file);
        return in_array($extension, $this->allowFiles);
    }

    /**
     * 验证文件类型
     * @param $fileType 文件类型 如pdf
     */
    public function validationSuffix($fileType)
    {
        if (in_array($fileType, $this->allowFiles)) {
            $f_suffixs = $this->attachment['f_suffixs'];
            if (empty($i_suffixs)) { //为空不限制
                return true;
            }
            $f_suffixs_arr = explode(",", $f_suffixs);
            if (sizeof($f_suffixs_arr) == 0) { //为0不限制
                return true;
            }
            return in_array($fileType, $f_suffixs_arr);
        } else {
            return false;
        }
    }

    /**
     * 验证大小
     * @param $size 单位kb
     */
    public function validationSize($size)
    {
        $file_size = $this->attachment['file_size'];
        if (empty($file_size)) { //为空不限制
            return true;
        }
        if ($size <= ($file_size * 1024 * 1024)) {
            return true;
        }
        return false;
    }

    /**
     * 压缩
     * @param $file 上传文件
     * @param string $folder 存放文件夹
     */
    public function compression($file, $folder = "files")
    {
        $savePath = "/" . $folder . "/" . date("Ymd");
        //创建文件夹
        $sPath = root_path() . config('filesystem.disks.folder') . $savePath;
        if (!tp_mkdir($sPath)) {
            return "创建文件夹失败";
        }
        //文件后缀名
        $ext = "";
        if (method_exists($file, "getOriginalExtension")) {
            $ext = $file->getOriginalExtension();
        } else {
            $ext = $file->getExtension();
        }
        $filename = func_random_num(8, 16) . "." . $ext; //保存文件名
        $fileInfo = pathinfo($file);
        // 获取文件地址和名称
        $filePath = $fileInfo['dirname'] . '/' . $fileInfo['basename'];
        // 文件地址转文件类
        $fileUp = new File($filePath);
        $fileUp->move($sPath, $filename);
        $savename = config('filesystem.disks.folder') . '/' . $savePath . "/" . $filename;
        $savename = replaceSymbol($savename);
        return $savename;
    }

    // 文件后缀
    public function getFileSuffix($file)
    {
        //文件后缀名
        //记录入文件表
        $OriginalName = "";
        if (method_exists($file, "getOriginalName")) { //判断方法存在
            $OriginalName = $file->getOriginalName();
        } else {
            $OriginalName = $file->getFilename();
        }
        $extension = strtolower(pathinfo($OriginalName, PATHINFO_EXTENSION)); //后缀
        return $extension;
    }

    // 验证 文件后缀与大小
    public function validation($file)
    {
        $extension = $this->getFileSuffix($file); //后缀
        if (!$this->validationSuffix($extension)) { //验证文件类型
            return false;
        }
        $sizeZ = $file->getSize(); //字节
        //        $sizeKB = number_format(($sizeZ/1024),1);//大小单位kb  保留一位小数
        if (!$this->validationSize($sizeZ)) { //验证文件大小
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
     * 上传
     * @param $file 文件
     * @param int $groupId 分组
     * @param string $fileType 文件类型
     * @param int $app 1:后台 0：前台
     * @param int $upload_type 上传类型 0：表示有远程就远程，没有就本地；1：就本地
     */
    public function upload($file, $groupId = 0, $fileType = "file", $app = 1, $upload_type = 0)
    {
        if (!empty($groupId) && $groupId == -1) {
            $groupId = 0;
        }
        //记录入文件表
        $OriginalName = "";
        if (method_exists($file, "getOriginalName")) { //判断方法存在
            $OriginalName = $file->getOriginalName();
        } else {
            $OriginalName = $file->getFilename();
        }
        $extension = strtolower(pathinfo($OriginalName, PATHINFO_EXTENSION)); //后缀
        $sizeZ = $file->getSize(); //字节

        $file_path = ""; //文件路径
        $storage = 0; //本地
        if (($upload_type == 0) && $this->validationRemote()) { //开启远程
            $storage = 1; //远程
            $folder_name = "files";
            //上传到阿里云oss
            $oss = new Oss();
            $file_path = $oss->putFile($file, $err, $folder_name);
            if (!$file_path) {
                return ['code' => 0, 'msg' => $err];
            }
        } else { //未开启远程
            $file_path = $this->compression($file); //文件路径
        }
        $insert_data = [
            'url' => $file_path,
            'storage' => $storage,
            'app' => $app,
            'user_id' => intval(Session::get('admin_auth.id')),
            'file_name' => $OriginalName,
            'file_size' => $sizeZ,
            'file_group_type' => $groupId,
            'file_type' => $fileType,
            'extension' => $extension
        ];
        $id = (new UploadFiles())->insertGetId($insert_data);
        return ['code' => 1, 'file' => $file_path, 'id' => $id, 'msg' => "上传成功", "storage" => $storage, 'file_size' => $sizeZ];
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
}