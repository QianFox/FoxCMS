<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   14:49
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   14:49
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\util\FileUtil;
use app\common\util\ImageUtil;
use app\common\util\VideoUtil;
use think\App;

// 文件管理器
class FileManager extends AdminBase
{
    public function upload()
    {
        $param = $this->request->param();
        $groupId = $param["groupId"] ?? 0;
        $upload_type = $param["upload_type"] ?? 0;
        $files = request()->file('file');
        $result = ['code' => 0, 'msg' => '上传失败'];

        $picExtArr = ["png", "jpg", "jpeg", "gif", "svg"]; //图片
        $mediaExtArr = ["mp4", "ogg", "webm", "mp3", "wav"]; //媒体
        $attaExtArr = ['doc', 'docx', 'pdf', 'txt', 'xls', 'xlsx', 'ppt', "zip", "rar"]; //附件

        $errFileInfo = [];
        foreach ($files as $file) {
            if (is_file($file)) {
                //文件后缀名
                if (method_exists($file, "getOriginalExtension")) {
                    $imageType = $file->getOriginalExtension();
                } else {
                    $imageType = $file->getExtension();
                }
                //记录入文件表
                if (method_exists($file, "getOriginalName")) { //判断方法存在
                    $OriginalName = $file->getOriginalName();
                } else {
                    $OriginalName = $file->getFilename();
                }
                if (in_array($imageType, $picExtArr)) { //图片
                    $imageUtil = new ImageUtil();
                    if (!$imageUtil->isAllowFile($file)) {
                        array_push($errFileInfo, ["msg" => "默认只允许上传常见图片格式", "originalName" => $OriginalName]);
                        continue;
                    }
                    if (!$imageUtil->validation($file)) {
                        array_push($errFileInfo, ["msg" => "上传图片后缀或大小和附件设置中不满足", "originalName" => $OriginalName]);
                        continue;
                    }
                    $result = $imageUtil->upload($file, 0, "image", 1, "files", $upload_type);
                } elseif (in_array($imageType, $mediaExtArr)) { //媒体
                    $videoUtil = new VideoUtil();
                    if (!$videoUtil->validationSuffix($imageType)) {
                        $this->error("上传的媒体文件必须是" . $videoUtil->suffix());
                    }
                    $result = $videoUtil->upload($file, "videos", 1, $upload_type);
                } elseif (in_array($imageType, $attaExtArr)) { //附件

                    $fileUtil = new FileUtil();
                    if (!$fileUtil->isAllowFile($file)) {
                        array_push($errFileInfo, ["msg" => "默认只允许上传文档和附件设置中不满足", "originalName" => $OriginalName]);
                        continue;
                    }
                    if (!$fileUtil->validation($file)) {
                        array_push($errFileInfo, ["msg" => "上传文件后缀或大小和后台设置不满足", "originalName" => $OriginalName]);
                        continue;
                    }
                    $result = $fileUtil->upload($file, $groupId, "file", 1, $upload_type);
                }
                if ($result["code"] == 0) {
                    $result["originalName"] = $OriginalName;
                    array_push($errFileInfo, $result);
                }
            }
        }
        if (sizeof($errFileInfo) > 0) {
            $this->error("有未上传成功文件", "", $errFileInfo);
        } else {
            $this->success("上传成功", $result->code == 0, $result);
        }
    }
}