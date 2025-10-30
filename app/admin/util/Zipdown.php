<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   15:57
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   15:57
 */

namespace app\admin\util;

use ZipArchive;

class Zipdown
{
    /**
     * 获取某目录下所有子文件和子目录
     * @param $path 目录
     */
    function getDirContent($path)
    {
        if (!is_dir($path)) {
            return false;
        }
        //scandir方法
        $arr = array();
        $data = scandir($path);
        foreach ($data as $value) {
            if ($value != '.' && $value != '..') {
                $arr[] = $value;
            }
        }
        return $arr;
    }

    // 截取字符串内容 去掉后最
    function cutStr($str)
    {
        $extension = strtolower(pathinfo($str, PATHINFO_EXTENSION));
        $endL = strpos($str, $extension);
        $cont = substr($str, 0, ($endL - 1)); //文件名
        return $cont;
    }

    // 逐行读取text文件
    function getTxtContent($filePath)
    {
        $file = fopen($filePath, "r");
        $contentArr = array();
        if (!$file) {
            return 'file open fail';
        } else {
            $i = 0;
            while (!feof($file)) {
                $contentArr[$i] = trim(fgets($file)); //fgets()函数从文件指针中读取一行
                $i++;
            }
            fclose($file);
            $contentArr = array_filter($contentArr);
        }
        return $contentArr;
    }

    /**
     * 压缩文件解压
     * @param $file 文件
     * @param $dirname 解压目录
     */
    public function unZipFile($file, $dirname)
    {
        if (!file_exists($file)) {
            return false;
        }
        // zip实例化对象
        $zipArc = new \ZipArchive();
        // 打开文件
        if (!$zipArc->open($file)) {
            return false;
        }
        // 解压文件
        if (!$zipArc->extractTo($dirname)) {
            // 关闭
            $zipArc->close();
            return false;
        }
        return $zipArc->close();
    }

    /**
     * 打包模型压缩文件及文件夹
     * @param array $files 文件路径 文件必须存在的
     * @param string $zipName 压缩包名称
     * @param boolean $wen
     * @param boolean $isDown
     */
    public function zip_file($files = [], $zipName = '', $wen = true, $isDown = true)
    {
        $zip_file_path = app()->getRootPath() . 'zip' . DIRECTORY_SEPARATOR;
        if (!tp_mkdir($zip_file_path)) {
            return "创建文件夹失败";
        }
        // 文件名为空则生成文件名
        if (empty($zipName)) {
            $zipName = $zip_file_path . date('YmdHis') . '.zip';
        } else {
            $zipName = $zip_file_path . $zipName . '.zip';
        }
        // 实例化类,使用本类，linux需开启zlib，windows需取消php_zip.dll前的注释
        $zip = new \ZipArchive();
        $res = $zip->open($zipName, \ZipArchive::OVERWRITE | \ZipArchive::CREATE);
        if (!$res) {
            exit('无法打开文件，或者文件创建失败');
        }
        // 文件夹打包处理
        if (is_string($files)) {
            // 文件夹整体打包
            $this->addFileToZip($files, $zip);
        } else {
            // 文件打包
            foreach ($files as $file) {
                if ($wen) {
                    $localname = "";
                    $fileArr = explode(DIRECTORY_SEPARATOR, $file);
                    if (in_array("app", $fileArr)) { //存在后台控制
                        $localname = strstr($file, "app" . DIRECTORY_SEPARATOR);
                    } elseif (in_array("templates", $fileArr)) { //模板
                        $localname = strstr($file, "templates" . DIRECTORY_SEPARATOR);
                    } elseif (in_array(config('filesystem.sql.model'), $fileArr)) { //模板
                        $localname = strstr($file, config('filesystem.sql.model') . DIRECTORY_SEPARATOR);
                    }
                    //                    if(strpos($file, DIRECTORY_SEPARATOR."app".DIRECTORY_SEPARATOR) != false){//存在后台控制
                    //                        $localname = strstr($file, "app".DIRECTORY_SEPARATOR);
                    //                    }elseif (strpos($file, DIRECTORY_SEPARATOR."templates".DIRECTORY_SEPARATOR) != false){//模板
                    //                        $localname = strstr($file, "templates".DIRECTORY_SEPARATOR);
                    //                    }elseif (strpos($file, DIRECTORY_SEPARATOR.config('filesystem.sql.model').DIRECTORY_SEPARATOR) != false){//模板
                    //                        $localname = strstr($file, config('filesystem.sql.model').DIRECTORY_SEPARATOR);
                    //                    }
                    $zip->addFile($file, $localname);
                }
            }
        }
        // 关闭
        $zip->close();
        // 验证文件是否存在
        if (!file_exists($zipName)) {
            exit("文件不存在" . $zipName);
        }
        if ($isDown) {
            // ob_clean();
            // 下载压缩包
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header('Content-disposition: attachment; filename=' . basename($zipName)); //文件名
            header("Content-Type: application/zip"); //zip格式的
            header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
            header('Content-Length: ' . filesize($zipName)); //告诉浏览器，文件大小
            @readfile($zipName); //ob_end_clean();
            @unlink($zipName); //删除压缩包
        } else {
            // 直接返回压缩包地址
            return $zipName;
        }
    }

    /**
     * 打包模板压缩文件及文件夹
     * @param array $files 文件必须存在的
     * @param string $zipName 压缩包名称
     * @param boolean $isDown
     */
    public function zipTemplate($files = [], $zipName = '', $isDown = true)
    {
        $fzipName = $zipName;
        $zip_file_path = app()->getRootPath() . 'zip' . DIRECTORY_SEPARATOR;
        if (!tp_mkdir($zip_file_path)) {
            return "创建文件夹失败";
        }
        // 文件名为空则生成文件名
        if (empty($fzipName)) {
            $fzipName = $zip_file_path . date('YmdHis') . '.zip';
        } else {
            $fzipName = $zip_file_path . $fzipName . '.zip';
        }
        // 实例化类,使用本类，linux需开启zlib，windows需取消php_zip.dll前的注释
        $zip = new \ZipArchive();
        $res = $zip->open($fzipName, \ZipArchive::OVERWRITE | \ZipArchive::CREATE);
        if (!$res) {
            exit('无法打开文件，或者文件创建失败');
        }
        // 文件夹打包处理
        if (is_string($files)) {
            // 文件夹整体打包
            $this->addFileToZip($files, $zip);
        } else {
            // 文件打包
            foreach ($files as $file) {
                $localname = strstr($file, $zipName . DIRECTORY_SEPARATOR);
                $zip->addFile($file, $localname);
            }
        }
        // 关闭
        $zip->close();
        // 验证文件是否存在
        if (!file_exists($fzipName)) {
            exit("文件不存在" . $fzipName);
        }
        if ($isDown) {
            // ob_clean();
            // 下载压缩包
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header('Content-disposition: attachment; filename=' . basename($fzipName)); //文件名
            header("Content-Type: application/zip"); //zip格式的
            header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件
            header('Content-Length: ' . filesize($fzipName)); //告诉浏览器，文件大小
            @readfile($fzipName); //ob_end_clean();
            @unlink($fzipName); //删除压缩包
        } else {
            // 直接返回压缩包地址
            return $fzipName;
        }
    }

    /**
     * 添加文件至压缩包
     * @param [type] $path
     * @param [type] $zip
     */
    public function addFileToZip($path, $zip)
    {
        // 打开文件夹
        $handler = opendir($path);
        while (($filename = readdir($handler)) !== false) {
            if ($filename != "." && $filename != "..") {
                // 编码转换
                $filename = iconv('gb2312', 'utf-8', $filename);
                // 文件夹文件名字为'.'和‘..’，不要对他们进行操作
                if (is_dir($path . "/" . $filename)) {
                    // 如果读取的某个对象是文件夹，则递归
                    $this->addFileToZip($path . "/" . $filename, $zip);
                } else {
                    // 将文件加入zip对象
                    $file_path = $path . "/" . $filename;
                    $zip->addFile($file_path, basename($file_path));
                }
            }
        }
        // 关闭文件夹
        @closedir($path);
    }

    // 打包压缩文件及文件夹
    public function zipGlob($path, $zipName)
    {
        if (!file_exists($path)) {
            return false;
        }
        $zip = new ZipArchive();
        $ret = $zip->open($zipName, ZipArchive::OVERWRITE);
        if (!$ret) {
            return false;
        } else {
            $r = is_dir($path) ? $zip->addGlob("{$path}/*") : $zip->addFile($path);
            if (!$r) {
                $zip->close();
                return false;
            }
            return $zip->close();
        }
    }
}