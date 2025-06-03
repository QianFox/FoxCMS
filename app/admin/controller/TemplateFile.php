<?php

/**
 * @Descripttion: FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author: FoxCMS Team
 * @Date: 2023/6/26   15:34
 * @version: V1.08
 * @copyright: ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime: 2024/12/27   15:34
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\facade\View;

/**
 * TemplateFile 类用于管理模板文件，提供文件列表、添加、删除和编辑功能
 */
class TemplateFile extends AdminBase
{
    // 过滤不安全的文件扩展名
    private $filters = [
        '.php', '.php3', '.php4', '.php5', '.phps', '.phtml',
        '.pl', '.py', '.rb', '.sh', '.cgi', '.asp', '.aspx',
        '.jsp', '.jspx', '.cfm', '.cfml', '.js', '.vbs',
        '.hta', '.bat', '.cmd', '.exe', '.dll'
    ];

    /**
     * 显示模板文件列表
     */
    public function index()
    {
        // 获取当前路径参数并处理
        $activepath = ($this->request->param('activepath') ?? $this->relativeTemplateHtml) . DIRECTORY_SEPARATOR;
        $activepath = replaceSymbol($activepath);
        if (str_ends_with($activepath, "/")) {
            $activepath = substr($activepath, 0, -1);
        }

        // 获取绝对路径并验证
        $basePath = realpath(root_path() . "templates" . DIRECTORY_SEPARATOR . $activepath);
        $allowedPath = realpath(root_path() . "templates" . DIRECTORY_SEPARATOR . $this->template['template']);

        if (!$basePath || strpos($basePath, $allowedPath) !== 0) {
            $this->error("非法路径访问");
        }

        // 获取目录下的文件数组
        $fArr = array();
        $arr_file = getDirFile($basePath, $activepath, $fArr, $this->template['template']);
        $templetFilelist = xn_cfg("templet-filelist");
        $view_suffix = config('view.view_suffix'); //文件后缀

        $filenameList = []; //文件目录
        $r_file = []; //返回文件
        foreach ($arr_file as $key => $file) {
            foreach ($templetFilelist as $k => $v) {
                if ($file['filename'] == $k . ".{$view_suffix}") {
                    $file['intro'] = $v;
                    break;
                }
            }
            if (empty($file['intro'])) { //没有描述
                foreach ($templetFilelist as $k => $v) {
                    if (str_starts_with($file['filename'], "list_")) {
                        if (str_ends_with($file['filename'], "_m.{$view_suffix}")) {
                            $file['intro'] = "手机端列表页模板";
                        } else {
                            $file['intro'] = "列表页模板";
                        }
                    } elseif (str_starts_with($file['filename'], "view_")) {
                        if (str_ends_with($file['filename'], "_m.{$view_suffix}")) {
                            $file['intro'] = "手机端内容页模板";
                        } else {
                            $file['intro'] = "内容页模板";
                        }
                    } elseif (str_starts_with($file['filename'], "index_")) {
                        if (!("index_m.{$view_suffix}" == $file['filename']) && str_ends_with($file['filename'], "_m.{$view_suffix}")) {
                            $file['intro'] = "手机端单页面模板";
                        } else {
                            $file['intro'] = "单页面模板";
                        }
                    } else {
                        if (str_ends_with($file['filename'], "_m.{$view_suffix}")) {
                            $file['intro'] = "手机端其他模板";
                        } else {
                            $file['intro'] = "其他模板";
                        }
                    }
                }
            }
            if ($file['filemine'] == "file") {
                array_push($filenameList, $file['filename']);
            } else {
                array_push($r_file, $file);
            }
            $arr_file[$key] = $file;
        }
        sort($filenameList);
        foreach ($filenameList as $key => $filename) {
            foreach ($arr_file as $kk => $af) {
                if ($af['filename'] == $filename) {
                    array_push($r_file, $af);
                    break;
                }
            }
        }

        // 分配模板变量并渲染模板
        View::assign("arrFile", $r_file);
        View::assign("activepath", $activepath);
        return view('index');
    }

/**
 * 添加新模板文件
 */
public function addFile()
{
    $activepath = $this->request->param("activepath") ?? $this->relativeTemplateHtml;

    // 获取绝对路径并验证
    $basePath = realpath(root_path() . "templates" . DIRECTORY_SEPARATOR . $activepath);
    $allowedPath = realpath(root_path() . "templates" . DIRECTORY_SEPARATOR . $this->template['template']);

    if (!$basePath || strpos($basePath, $allowedPath) !== 0) {
        $this->error("非法路径访问");
    }

    if ($this->request->isAjax()) {
        $fileName = input("fileName", '', null);
        if (empty($fileName)) {
            $this->error("文件名称为空");
        }

        // 过滤不安全的文件扩展名
        foreach ($this->filters as $filter) {
            if (strpos($fileName, $filter) !== false) {
                $this->error("文件名包含不安全的扩展名");
            }
        }

        // 确保文件扩展名为 .html 或 .htm
        if (!str_ends_with($fileName, ".html") && !str_ends_with($fileName, ".htm")) {
            $fileName .= ".html";
        }

        $content = input("content", '', null);
        $file = $basePath . DIRECTORY_SEPARATOR . $fileName;

        if (!is_writable(dirname($file))) {
            return "请把模板文件目录设置为可写入权限！" . $file;
        }

        // 检查文件内容是否包含 PHP 代码
        if (
            preg_match('#<([^?]*)\?php#i', $content) ||
            (preg_match('#<\?#i', $content) && preg_match('#\?>#i', $content)) ||
            preg_match('#\{fox\:php([^\}]*)\}#i', $content) ||
            preg_match('#\{php([^\}]*)\}#i', $content)
        ) {
            return "模板里不允许有php语法，为了安全考虑，请通过FTP工具进行编辑上传。";
        }

        // 写入文件内容
        $fp = fopen($file, "w");
        fputs($fp, $content);
        fclose($fp);

        $this->success('操作成功！');
    }

    View::assign('filePosition', $activepath);
    return view("add_file");
}

    /**
     * 删除模板文件
     */
    public function deleteFile()
    {
        $filePath = $this->request->param("filePath");

        // 获取绝对路径并验证
        $file = realpath(root_path() . "templates" . DIRECTORY_SEPARATOR . $filePath);
        $allowedPath = realpath(root_path() . "templates" . DIRECTORY_SEPARATOR . $this->template['template']);

        if (!$file || strpos($file, $allowedPath) !== 0) {
            $this->error("非法路径访问");
        }

        if (!unlink($file)) {
            $this->error('操作失败！');
        } else {
            $this->success('操作成功！');
        }
    }

/**
 * 编辑模板文件
 */
public function editFile()
{
    $activepath = $this->request->param("activepath");

    // 获取绝对路径并验证
    $file = realpath(root_path() . "templates" . DIRECTORY_SEPARATOR . $activepath);
    $allowedPath = realpath(root_path() . "templates" . DIRECTORY_SEPARATOR . $this->template['template']);

    if (!$file || strpos($file, $allowedPath) !== 0) {
        $this->error("非法路径访问");
    }

    $arr = explode("/", $activepath);
    $fileName = $arr[count($arr) - 1];

    if ($this->request->isAjax()) {
        // 确保文件扩展名为 .html 或 .htm
        if (!str_ends_with($fileName, ".html") && !str_ends_with($fileName, ".htm")) {
            $this->error("{$fileName}不能被修改");
        }

        $content = input("content", '', null);

        if (!is_writable(dirname($file))) {
            return "请把模板文件目录设置为可写入权限！";
        }

        // 检查文件内容是否包含 PHP 代码
        if (
            preg_match('#<([^?]*)\?php#i', $content) ||
            (preg_match('#<\?#i', $content) && preg_match('#\?>#i', $content)) ||
            preg_match('#\{fox\:php([^\}]*)\}#i', $content) ||
            preg_match('#\{php([^\}]*)\}#i', $content)
        ) {
            return "模板里不允许有php语法，为了安全考虑，请通过FTP工具进行编辑上传。";
        }

        // 写入文件内容
        $fp = fopen($file, "w");
        fputs($fp, $content);
        fclose($fp);

        $this->success('操作成功！');
    }

    /*读取文件内容*/
    $content = "";
    if (is_file($file)) {
        $filesize = filesize($file);
        if (0 < $filesize) {
            $fp = fopen($file, "r");
            $content = fread($fp, $filesize);
            fclose($fp);
        }
    }

    View::assign('fileName', $fileName);
    View::assign('filePosition', $activepath);
    View::assign('content', $content);
    return view('edit_file');
}
}