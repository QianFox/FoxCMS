<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   18:58
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   18:58
 */

namespace app\common\util;

use app\common\lib\Oss;
use app\common\model\Attachment;
use app\common\model\UploadFiles;
use think\facade\Db;
use think\facade\Session;
use think\Image;

// 图片工具类
class ImageUtil
{
    private $attachment; //附件设置
    private $allowImages = ['bmp', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'png', 'jpeg2000']; //默认只允许上传图片
    private $watermark; //水印
    public function __construct()
    {
        $attachments = Attachment::select();
        if (sizeof($attachments) == 1) {
            $this->attachment = $attachments[0];
        }
        $watermarkArr = Db::name("watermark")->select()->toArray();
        if (sizeof($watermarkArr) > 0) {
            $this->watermark = $watermarkArr[0];
        } else {
            $this->watermark = ['status' => 0];
        }
    }

    /**
     * 添加水印
     * @param $image 图片对象
     */
    public function addWatermark($image)
    {
        if ($this->watermark['status'] == 1) { //水印功能状态 1:开启；0:关闭
            $position = $this->watermark['position'] ?? Image::WATER_SOUTHEAST;
            $width = $image->width();
            $height = $image->height();
            $isAdd = false;
            if (($this->watermark['type'] == 2) && ($this->watermark['width'] != 0) && ($this->watermark['height'] != 0)) { //图片
                if ($this->watermark['width'] <= $width && $this->watermark['height'] <= $height) {
                    $isAdd = true;
                }
            } else {
                $isAdd = true;
            }
            if ($this->watermark['type'] == 1) { //文字
                if ($isAdd) {
                    $font_path = root_path() . "static/images/water.ttf";
                    $font_size = $this->watermark['font_size'] ?? "16";
                    //字体颜色
                    if (!empty($this->watermark['font_color'])) {
                        $font_color = rgba_to_hex($this->watermark['font_color']);
                    } else {
                        $font_color = "#ffffff";
                    }
                    $water_font = $this->watermark['water_font'];
                    //设置阴影颜色
                    if (!empty($this->watermark['shadow_color'])) {
                        $shadow_color = rgba_to_hex($this->watermark['shadow_color']);
                    } else {
                        $shadow_color = "#000000";
                    }
                    //添加阴影
                    $image->text($water_font, $font_path, $font_size, $shadow_color, $position, 2);
                    // 添加水印
                    $image->text($water_font, $font_path, $font_size, $font_color, $position);
                }
            } elseif ($this->watermark['type'] == 2) { //图片
                if ($isAdd) {
                    $img_url = $this->watermark['image'];
                    if (!empty($img_url)) {
                        if (!(preg_match('/(http:\/\/)|(https:\/\/)/i', $img_url))) { //判断是否存在https或者http
                            $img_url = root_path() . $img_url;
                        }
                        $image->water($img_url, $position, $this->watermark['opacity']);
                    }
                }
            }
        }
    }

    // 允许上传图片文件
    public function isAllowFile($file)
    {
        $extension = $this->getFileSuffix($file);
        return in_array($extension, $this->allowImages);
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

    // 配置的图片限制后缀
    public function getSuffix()
    {
        return $this->attachment['i_suffixs'];
    }

    // 配置的图片限制大小
    public function getSize()
    {
        return $this->attachment['i_file_size'];
    }

    // 验证图片类型
    public function validationSuffix($imageType)
    {
        if (in_array($imageType, $this->allowImages)) {
            $i_suffixs = $this->attachment['i_suffixs'];
            if (empty($i_suffixs)) { //为空不限制
                return true;
            }
            $i_suffixs_arr = explode(",", $i_suffixs);
            if (sizeof($i_suffixs_arr) == 0) { //为0不限制
                return true;
            }
            return in_array($imageType, $i_suffixs_arr);
        } else {
            return false;
        }
    }

    /**
     * 验证大小
     * @param $size 单位字节
     */
    public function validationSize($size)
    {
        $i_file_size = $this->attachment['i_file_size']; //图片大小限制单位MB
        if (empty($i_file_size)) { //为空不限制
            return true;
        }
        if ($size <= ($i_file_size * 1024 * 1024)) {
            return true;
        }
        return false;
    }

    /**
     * 压缩
     * @param $file 上传文件
     * @param int $quality 质量值 默认不压缩
     * @param string $folder 存放文件夹
     */
    public function compression($file, $quality = 100, $folder = "files")
    {
        $savePath = config('filesystem.disks.folder') . "/" . $folder . "/" . date("Ymd") . "/";
        //创建文件夹
        if (!tp_mkdir($savePath)) {
            return "创建文件夹失败";
        }
        //文件后缀名
        if (method_exists($file, "getOriginalExtension")) {
            $ext = $file->getOriginalExtension();
        } else {
            $ext = $file->getExtension();
        }
        if (($ext != "svg") && ($ext != "ico")) { //svg不开压缩
            $filename = $savePath . func_random_num(8, 16) . "." . $ext;
            $image = Image::open($file);
            //水印
            $this->addWatermark($image);
            //开启压缩
            if ($this->validationThumb()) {
                $quality = $this->attachment['thumb_pic_quality'];
            }
            $image->save($filename, null, $quality);
            $rfile = $filename;
        } else {
            $filename = func_random_num(8, 16) . "." . $ext;
            $file->move($savePath, $filename);
            $rfile =  $savePath . $filename;
        }
        return $rfile;
    }

    // 验证 图片后缀与大小
    public function validation($file)
    {
        $image = Image::open($file);
        $imageType = $image->type(); //图片类型
        $sizeZ = $file->getSize(); //字节
        //        $sizeKB = number_format(($sizeZ/1024),1);//大小单位kb  保留一位小数
        if (!$this->validationSuffix($imageType)) { //验证图片类型
            return false;
        }
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

    // 验证是否开启压缩1:开启；0未开启
    public function validationThumb()
    {
        return ($this->attachment['is_thumb']) == 1;
    }

    /**
     * 上传
     * @param $file 文件
     * @param int $groupId 分组
     * @param string $fileType 文件类型
     * @param int $app 1:后台 0：前台
     * @param string $folder_name 文件路径
     * @param int $upload_type 上传类型 0：表示有远程就远程，没有就本地；1：就本地
     */
    public function upload($file, $groupId = 0, $fileType = "image", $app = 1, $folder_name = "files", $upload_type = 0)
    {
        //记录入文件表
        $OriginalName = "";
        if (method_exists($file, "getOriginalName")) { //判断方法存在
            $OriginalName = $file->getOriginalName();
        } else {
            $OriginalName = $file->getFilename();
        }

        if (!empty($groupId) && $groupId == -1) {
            $groupId = 0;
        }

        //文件后缀名
        if (method_exists($file, "getOriginalExtension")) {
            $imageType = $file->getOriginalExtension();
        } else {
            $imageType = $file->getExtension();
        }

        if (!$this->validationSuffix($imageType)) { //验证图片类型
            return ['code' => 0, 'msg' => '请上传' . $this->attachment['i_suffixs'] . '格式文件', "originalName" => $OriginalName];
        }
        $size = $file->getSize(); //字节
        if (!$this->validationSize($size)) { //验证图片大小
            return ['code' => 0, 'msg' => '请上传小于等于' . $this->attachment['i_file_size'] . 'mb的文件', "originalName" => $OriginalName];
        }
        $file_path = ""; //文件路径
        $storage = 0; //本地
        if (($upload_type == 0) && $this->validationRemote()) { //开启远程
            $storage = 1; //远程
            //上传到阿里云oss
            $oss = new Oss();
            $file_path = $oss->putFile($file, $err, $folder_name);
            if (!$file_path) {
                return ['code' => 0, 'msg' => $err, "originalName" => $OriginalName];
            }
        } else { //未开启远程
            $file_path = $this->compression($file); //文件路径
        }

        //获取图片文件尺寸
        if (!(preg_match('/(http:\/\/)|(https:\/\/)/i', $file_path))) { //判断是否存在https或者http
            $url = root_path() . "/" . $file_path;
        } else {
            $url = $file_path;
        }
        $url = replaceSymbol($url);
        $imageInfo = getimagesize($url);
        $imgSize = "";
        if ($imageInfo) {
            $imgSize = "{$imageInfo[0]}X{$imageInfo[1]}";
        }

        $insert_data = [
            'url' => $file_path,
            'storage' => $storage,
            'app' => $app,
            'user_id' => intval(Session::get('admin_auth.id')),
            'file_name' => $OriginalName,
            'file_size' => $size,
            'file_group_type' => $groupId,
            'file_type' => $fileType,
            'create_time' => date("Y-m-d H:i:s"),
            'extension' => strtolower(pathinfo($OriginalName, PATHINFO_EXTENSION)),
            'size' => $imgSize
        ];
        $id = (new UploadFiles())->insertGetId($insert_data);

        if (!(preg_match('/(http:\/\/)|(https:\/\/)/i', $file_path))) { //判断是否存在
            if (!str_starts_with($file_path, "/")) {
                $file_path = "/" . $file_path;
            }
        }
        return ['code' => 1, 'file' => $file_path, 'id' => $id, 'msg' => "上传成功"];
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