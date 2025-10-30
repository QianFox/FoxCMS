<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   15:37
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2024/12/27   18:45
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\facade\View;

class TemplateStyle extends AdminBase
{
    private $filters = ['.php', '.php3', '.php4', '.php5', '.phps', '.phtml',
        '.pl', '.py', '.rb', '.sh', '.cgi', '.asp', '.aspx',
        '.jsp', '.jspx', '.cfm', '.cfml', '.js', '.vbs',
        '.hta', '.bat', '.cmd', '.exe', '.dll'
    ]; // 过滤不安全的文件扩展名
    private $fontArr = ['eot', 'otf', 'fon', 'ttf', 'ttc', 'woff', 'woff2']; //字体文件后缀
    private $allowImages = ['bmp', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'png', 'jpeg2000']; //图片文件后缀

    public function index()
    {
        $activepath = $this->request->param('activepath') ?? DIRECTORY_SEPARATOR . "skin";
        $activepath = replaceSymbol($activepath);
        if (!preg_match('/^\/skin\/[a-zA-Z0-9\/\-_\.]+$/', $activepath)) {
            $activepath = DIRECTORY_SEPARATOR . "skin";
        }
        $basePath = root_path() . DIRECTORY_SEPARATOR . 'templates' . $activepath;
        $allowedPath = root_path() . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'skin';

        if (realpath($basePath) !== realpath($allowedPath) && strpos(realpath($basePath), realpath($allowedPath)) !== 0) {
            $this->error("非法路径访问");
        }

        $fArr = array();
        $arr_file = getDirFile($basePath, $activepath, $fArr, $this->template['template']);

        $filenameList = []; // 文件目录
        $r_file = []; // 返回文件
        foreach ($arr_file as $key => $file) {
            if (str_ends_with($file['filename'], ".css")) {
                $file['intro'] = "样式文件";
            } elseif (str_ends_with($file['filename'], ".js")) {
                $file['intro'] = "JS脚本文件";
            } else {
                $filenameArr = explode(".", $file['filename']);
                $suffix = $filenameArr[sizeof($filenameArr) - 1];
                if (in_array($suffix, $this->fontArr)) { // 字体文件
                    $file['intro'] = "字体文件";
                } elseif (in_array($suffix, $this->allowImages)) {
                    $file['intro'] = "图片文件";
                } else {
                    if (!($file['filetype'] == "dir2" || $file['filetype'] == "dir")) {
                        $file['intro'] = "其它文件";
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
        View::assign("arrFile", $r_file);
        View::assign("activepath", $activepath);
        return view('index');
    }

    // 新增文件
    public function addFile()
    {
        $activepath = $this->request->param("activepath") ?? DIRECTORY_SEPARATOR . "skin";
        $activepath = replaceSymbol($activepath);
        if (!preg_match('/^\/skin\/[a-zA-Z0-9\/\-_\.]+$/', $activepath)) {
            $activepath = DIRECTORY_SEPARATOR . "skin";
        }
        $basePath = root_path() . DIRECTORY_SEPARATOR . 'templates' . $activepath;
        $allowedPath = root_path() . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'skin';

        if (realpath($basePath) !== realpath($allowedPath) && strpos(realpath($basePath), realpath($allowedPath)) !== 0) {
            $this->error("非法路径访问");
        }

        if ($this->request->isAjax()) {
            $columnId = $this->request->param("columnId");
            $fileName = input("fileName", '', null);
            if (empty($fileName)) {
                $this->error("文件名称为空");
            }
            // 确保文件扩展名为 .css
            if (!str_ends_with($fileName, ".css")) {
                $fileName .= ".css";
            }
            // 过滤不安全的文件扩展名
            foreach ($this->filters as $filter) {
                if (strpos($fileName, $filter) !== false) {
                    $fileName = str_replace($filter, "", $fileName);
                }
            }
            $content = input("content", '', null);
            $file = $basePath . DIRECTORY_SEPARATOR . $fileName;

            if (!is_writable(dirname($file))) {
                return "请把模板文件目录设置为可写入权限！";
            }
            if (preg_match('#<([^?]*)\?php#i', $content) || (preg_match('#<\?#i', $content) && preg_match('#\?>#i', $content)) || preg_match('#\{fox\:php([^\}]*)\}#i', $content) || preg_match('#\{php([^\}]*)\}#i', $content)) {
                return "模板里不允许有php语法，为了安全考虑，请通过FTP工具进行编辑上传。";
            }
            $fp = fopen($file, "w");
            fputs($fp, $content);
            fclose($fp);
            $this->success('操作成功！', url(
                '/' . config('adminconfig.admin_path') . '/TemplateStyle/index?columnId=' . $columnId,
                array('activepath' => $activepath)
            ));
        }
        View::assign('filePosition', $activepath);
        return view("add_file");
    }

    // 删除文件
    public function deleteFile()
    {
        $filePath = $this->request->param("filePath");
        $filePath = replaceSymbol($filePath);
        if (!preg_match('/^\/skin\/[a-zA-Z0-9\/\-_\.]+\.css$/', $filePath)) {
            $this->error("非法路径访问");
        }
        $file = root_path() . DIRECTORY_SEPARATOR . 'templates' . $filePath;
        $allowedPath = root_path() . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'skin';

        if (realpath($file) !== realpath($allowedPath) && strpos(realpath($file), realpath($allowedPath)) !== 0) {
            $this->error("非法路径访问");
        }

        if (!file_exists($file)) {
            $this->error('文件不存在！');
        }
        if (!is_writable($file)) {
            $this->error('文件没有写入权限！');
        }
        if (!unlink($file)) {
            $this->error('操作失败！');
        } else {
            $this->success('操作成功！');
        }
    }

    // 编辑文件
    public function editFile()
    {
        $activepath = $this->request->param("activepath");
        $activepath = replaceSymbol($activepath);
        if (!preg_match('/^\/skin\/[a-zA-Z0-9\/\-_\.]+\.css$/', $activepath)) {
            $this->error("非法路径访问");
        }
        $file = root_path() . DIRECTORY_SEPARATOR . 'templates' . $activepath;
        $allowedPath = root_path() . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'skin';

        if (realpath($file) !== realpath($allowedPath) && strpos(realpath($file), realpath($allowedPath)) !== 0) {
            $this->error("非法路径访问");
        }

        $arr = explode("/", $activepath);
        $fileName = $arr[count($arr) - 1];

        if ($this->request->isAjax()) {
            if (!str_ends_with($fileName, ".css")) {
                $this->error("{$fileName}不能被修改");
            }
            $content = input("content", '', null);

            if (!is_writable(dirname($file))) {
                return "请把模板文件目录设置为可写入权限！";
            }
            if (preg_match('#<([^?]*)\?php#i', $content) || (preg_match('#<\?#i', $content) && preg_match('#\?>#i', $content)) || preg_match('#\{fox\:php([^\}]*)\}#i', $content) || preg_match('#\{php([^\}]*)\}#i', $content)) {
                return "模板里不允许有php语法，为了安全考虑，请通过FTP工具进行编辑上传。";
            }
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
        View::assign(
            'content',
            $content
        );
        return view('edit_file');
    }
}