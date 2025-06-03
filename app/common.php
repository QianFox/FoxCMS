<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   16:55
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   16:55
 */

use think\facade\Db;
use think\facade\Log;

include_once $this->getAppPath() . 'func/commont.php';

// 字节数Byte转换为KB、MB、GB、TB
function xn_file_size($size)
{
    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
    return round($size, 2) . $units[$i];
}

// 当前目录下的文件列表
function getDirFile($directory, $activepath = '',  &$arr_file = array(), &$curTemplate = "default")
{
    if (!file_exists($directory)) {
        return false;
    }

    $fileArr = $dirArr = $parentArr = array();

    $mydir = dir($directory);
    while (false !== $file = $mydir->read()) {
        $filesize = $filetime = $intro = '';
        $filemine = 'file';

        if ($file != "." && $file != ".." && !is_dir("$directory/$file")) {
            @$filesize = filesize("$directory/$file");
            @$filesize = xn_file_size($filesize);
            @$filetime = date('Y-m-d', filemtime("$directory/$file"));
        }
        Log::info($file);
        if ($file == '.') {
            continue;
        } else if ($file == "..") {
            if ($activepath == "" || $activepath == (DIRECTORY_SEPARATOR . $curTemplate)) {
                continue;
            }
            $parentArr = array(
                array(
                    'filepath'  => preg_replace("#[\/][^\/]*$#i", "", $activepath),
                    'filename'  => '上级目录',
                    'filesize'  => '',
                    'filetime'  => '',
                    'filemine'  => 'dir',
                    'filetype'  => 'dir2',
                    'icon'      => 'file_topdir.gif',
                    'intro'  => '（当前目录：' . $activepath . '）',
                ),
            );
            continue;
        } else if (is_dir("$directory/$file")) {
            if (preg_match("#^_(.*)$#i", $file)) continue; #屏蔽FrontPage扩展目录和linux隐蔽目录
            if (preg_match("#^\.(.*)$#i", $file)) continue;
            $filepath = $activepath . '/' . $file;
            $filepath = replaceSymbol($filepath);
            $file_info = array(
                'filepath'  => $filepath,
                'filename'  => $file,
                'filesize'  => '',
                'filetime'  => '',
                'filemine'  => 'dir',
                'filetype'  => 'dir',
                'icon'      => 'dir.gif',
                'intro'     => '',
            );
            array_push($dirArr, $file_info);
            continue;
        } else if (preg_match("#\.(gif|png)#i", $file)) {
            $filemine = 'image';
            $filetype = 'gif';
            $icon = 'gif.gif';
        } else if (preg_match("#\.(jpg|jpeg|bmp|webp)#i", $file)) {
            $filemine = 'image';
            $filetype = 'jpg';
            $icon = 'jpg.gif';
        } else if (preg_match("#\.(svg)#i", $file)) {
            $filemine = 'image';
            $filetype = 'svg';
            $icon = 'jpg.gif';
        } else if (preg_match("#\.(swf|fla|fly)#i", $file)) {
            $filetype = 'flash';
            $icon = 'flash.gif';
        } else if (preg_match("#\.(zip|rar|tar.gz)#i", $file)) {
            $filetype = 'zip';
            $icon = 'zip.gif';
        } else if (preg_match("#\.(exe)#i", $file)) {
            $filetype = 'exe';
            $icon = 'exe.gif';
        } else if (preg_match("#\.(mp3|wma)#i", $file)) {
            $filetype = 'mp3';
            $icon = 'mp3.gif';
        } else if (preg_match("#\.(wmv|api)#i", $file)) {
            $filetype = 'wmv';
            $icon = 'wmv.gif';
        } else if (preg_match("#\.(rm|rmvb)#i", $file)) {
            $filetype = 'rm';
            $icon = 'rm.gif';
        } else if (preg_match("#\.(txt|inc|pl|cgi|asp|xml|xsl|aspx|cfm)#", $file)) {
            $filetype = 'txt';
            $icon = 'txt.gif';
        } else if (preg_match("#\.(htm|html)#i", $file)) {
            $filetype = 'htm';
            $icon = 'htm.gif';
        } else if (preg_match("#\.(php)#i", $file)) {
            $filetype = 'php';
            $icon = 'php.gif';
        } else if (preg_match("#\.(js)#i", $file)) {
            $filetype = 'js';
            $icon = 'js.gif';
        } else if (preg_match("#\.(css)#i", $file)) {
            $filetype = 'css';
            $icon = 'css.gif';
        } else {
            $filetype = 'other';
            $icon = 'other.gif';
        }

        $filepath = $activepath . '/' . $file;
        $filepath = replaceSymbol($filepath);
        $file_info = array(
            'filepath'  => $filepath,
            'filename'  => $file,
            'filesize'  => $filesize,
            'filetime'  => $filetime,
            'filemine'  => $filemine,
            'filetype'  => $filetype,
            'icon'      => $icon,
            'intro'     => $intro,
        );
        array_push($fileArr, $file_info);
    }
    $mydir->close();

    $arr_file = array_merge($parentArr, $dirArr, $fileArr);

    return $arr_file;
}

// 驼峰命名转下划线命名
function xn_uncamelize($camelCaps, $separator = '_')
{
    return strtolower(preg_replace('/([a-z])([A-Z])/', "$1" . $separator . "$2", $camelCaps));
}

// 密码加密函数
function xn_encrypt($password)
{
    $salt = 'fox';
    return md5(md5($password . $salt));
}

// 密码验证必须包含数字、大小写字母、特殊字符中至少3种,且不少于8位
function verify_password($password, $maxLength = "")
{
    $pattern  = '/^(?![a-zA-Z]+$)(?![A-Z0-9]+$)(?![A-Z\W_]+$)(?![a-z0-9]+$)(?![a-z\W_]+$)(?![0-9\W_]+$)[a-zA-Z0-9\W_]{8,' . $maxLength . '}$/';
    if (preg_match($pattern, $password)) {
        return true;
    }
    return false;
}

// 管理员操作日志
function xn_add_admin_log($remark, $type = 'system', $content = "")
{
    if ($type == "login") {
        $param = "";
    } else {
        $param = json_encode(request()->param(), JSON_UNESCAPED_UNICODE);
    }
    $data = [
        'admin_id' => session('admin_auth.id') ?? 0,
        'url' => request()->url(true),
        'ip' => request()->ip(),
        'remark' => $remark,
        'method' => request()->method(),
        'param' => $param,
        'create_time' => date('Y-m-d H:i:s', time()),
        'type' => $type,
        'content' => $content
    ];
    \app\common\model\AdminLog::insert($data);
}

/**
 * 获取自定义config/cfg目录下的配置
 * 用法： xn_cfg('base') 或 xn_cfg('base.website') 不支持无限极
 */
function xn_cfg($name, $default = '', $path = 'cfg')
{
    if (false === strpos($name, '.')) {
        $name = strtolower($name);
        $config  = \think\facade\Config::load($path . '/' . $name, $name);
        return $config ?? [];
    }
    $name_arr    = explode('.', $name);
    $name_arr[0] = strtolower($name_arr[0]);
    $filename = $name_arr[0];
    $config  = \think\facade\Config::load($path . '/' . $filename, $filename);
    return $config[$name_arr[1]] ?? $default;
}

// 根目录物理路径
function xn_root()
{
    return app()->getRootPath() . 'public';
}

/**
 * 构建图片上传HTML 单图
 * @param string $value
 * @param string $file_name
 * @param null $water 是否添加水印 null-系统配置设定 1-添加水印 0-不添加水印
 * @param null $thumb 生成缩略图，传入宽高，用英文逗号隔开，如：200,200（仅对本地存储方式生效，七牛、oss存储方式建议使用服务商提供的图片接口）
 */
function xn_upload_one($value, $file_name, $water = null, $thumb = null)
{
    $html = <<<php
    <div class="xn-upload-box">
        <div class="t layui-col-md12 layui-col-space10">
            <input type="hidden" name="{$file_name}" class="layui-input xn-images" value="{$value}">
            <div class="layui-col-md4">
                <div type="button" class="layui-btn webuploader-container" id="{$file_name}" data-water="{$water}" data-thumb="{$thumb}" style="width: 113px;"><i class="layui-icon layui-icon-picture"></i>上传图片</div>
                <div type="button" class="layui-btn chooseImage" data-num="1"><i class="layui-icon layui-icon-table"></i>选择图片</div>
            </div>
        </div>
        <ul class="upload-ul clearfix">
            <span class="imagelist"></span>
        </ul>
        <script>$('#{$file_name}').uploadOne();</script>
    </div>
php;
    return $html;
}

/**
 * 构建图片上传HTML 多图
 * @param string $value
 * @param string $file_name
 * @param null $water 是否添加水印 null-系统配置设定 1-添加水印 0-不添加水印
 * @param null $thumb 生成缩略图，传入宽高，用英文逗号隔开，如：200,200（仅对本地存储方式生效，七牛、oss存储方式建议使用服务商提供的图片接口）
 */
function xn_upload_multi($value, $file_name, $water = null, $thumb = null)
{
    $html = <<<php
    <div class="xn-upload-box">
        <div class="t layui-col-md12 layui-col-space10">
            <div class="layui-col-md8">
                <input type="text" name="{$file_name}" class="layui-input xn-images" value="{$value}">
            </div>
            <div class="layui-col-md4">
                <div type="button" class="layui-btn webuploader-container" id="{$file_name}" data-water="{$water}" data-thumb="{$thumb}" style="width: 113px;"><i class="layui-icon layui-icon-picture"></i>上传图片</div>
                <div type="button" class="layui-btn chooseImage"><i class="layui-icon layui-icon-table"></i>选择图片</div>
            </div>
        </div>
        <ul class="upload-ul clearfix">
            <span class="imagelist"></span>
        </ul>
        <script>$('#{$file_name}').upload();</script>
    </div>
php;
    return $html;
}

// 格式化标签，将空格、中文逗号替换成英文半角分号
function xn_format_tags($tags)
{
    $tags = trim($tags);
    $tags = str_replace(' ', ',', str_replace('，', ',', $tags));
    $arr = explode(',', $tags);
    $data = [];
    foreach ($arr as $v) {
        if ($v != '') {
            $data[] = $v;
        }
    }
    return implode(',', $data);
}

// 生成全局唯一标识符
function xn_create_guid()
{
    $charid = strtoupper(md5(uniqid(mt_rand(), true)));
    $hyphen = chr(45); // "-"
    $uuid = chr(123) // "{"
        . substr($charid, 0, 8) . $hyphen
        . substr($charid, 8, 4) . $hyphen
        . substr($charid, 12, 4) . $hyphen
        . substr($charid, 16, 4) . $hyphen
        . substr($charid, 20, 12)
        . chr(125); // "}"
    return $uuid;
}

// 生成订单号
function xn_create_order_no()
{
    $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
    $orderSn = $yCode[intval(date('Y')) - 2020] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
    return $orderSn;
}

// 保存缓存
function saveToCache($key, $value = '', $expire_in = -1)
{
    if ($expire_in == -1) {
        $expire_in = config("secure.token_expire_in");
    }
    $result = cache($key, $value, $expire_in);
    return $result;
}

if (!function_exists('tp_mkdir')) {
    /**
     * 递归创建目录
     * @param string $path 目录路径，不带反斜杠
     * @param intval $purview 目录权限码
     */
    function tp_mkdir($path, $purview = 0777)
    {
        if (!is_dir($path)) {
            tp_mkdir(dirname($path), $purview);
            if (!mkdir($path, $purview)) {
                return false;
            }
        }
        return true;
    }
}

if (!function_exists('dirFile')) {
    /**
     * 递归读取文件夹文件
     * @param string $directory 目录路径
     * @param string $dir_name 显示的目录前缀路径
     * @param array $arr_file 是否删除空目录
     */
    function dirFile($directory, $dir_name = '', &$arr_file = array())
    {
        if (!file_exists($directory)) {
            return false;
        }

        $mydir = dir($directory);
        while ($file = $mydir->read()) {
            $df = $directory . DIRECTORY_SEPARATOR . $file;
            $dnf = $dir_name . DIRECTORY_SEPARATOR . $file;
            if ((is_dir("$df")) and ($file != ".") and ($file != "..")) {
                if ($dir_name) {
                    dirFile("$df", "$dnf", $arr_file);
                } else {
                    dirFile("$df", "$file", $arr_file);
                }
            } else if (($file != ".") and ($file != "..")) {
                if ($dir_name) {
                    $arr_file[] = "$dnf";
                } else {
                    $arr_file[] = "$file";
                }
            }
        }
        $mydir->close();

        return $arr_file;
    }
}

if (!function_exists('foreachDirFile')) {
    /**
     * 递归读取文件夹文件
     * @param string $rootPath 根目录路径
     * @param string $directory 目录路径
     * @param string $dir_name 显示的目录前缀路径
     * @param array $arr_file 是否删除空目录
     * @param array $extarr 文件后缀数组
     * @param array $findarr 查找文件数组
     */
    function foreachDirFile($rootPath, $directory, $dir_name = '', &$arr_file = array(), $extarr = array(), $findarr = array())
    {
        if (!file_exists($directory)) {
            return false;
        }
        $mydir = dir($directory);
        while ($file = $mydir->read()) {
            $df = $directory . DIRECTORY_SEPARATOR . $file;
            $dnf = $dir_name . DIRECTORY_SEPARATOR . $file;
            if ((is_dir("$df")) and ($file != ".") and ($file != "..")) {
                if ($dir_name) {
                    foreachDirFile($rootPath, "$df", "$dnf", $arr_file, $extarr, $findarr);
                } else {
                    foreachDirFile($rootPath, "$df", "$file", $arr_file, $extarr, $findarr);
                }
            } else if (($file != ".") and ($file != "..")) {
                if ($dir_name) {
                    $gfile = "$dnf";
                } else {
                    $gfile = "$file";
                }
                $filePath = $rootPath . "/" . $gfile;
                $file = new \think\File($filePath);
                $extension = $file->getExtension();
                if (sizeof($extarr) > 0) {
                    if (!in_array($extension, $extarr)) {
                        continue;
                    }
                }
                $filesize = $file->getSize();
                $filename = $file->getFilename();
                $filesizeZ = xn_file_size($filesize);
                $filetime = date('Y-m-d H:i:s', filemtime("{$filePath}"));
                $dir = replaceSymbol($directory);
                $dirArr = explode("/", $dir);
                $dir = $dirArr[(sizeof($dirArr) - 1)];
                $arrFile = ["file" => $gfile, 'filesize' => $filesizeZ, "fsize" => $filesize, 'filetime' => $filetime, "filename" => $filename, "extension" => $extension, "dir" => $dir];
                if (sizeof($findarr) > 0) {
                    foreach ($findarr as $fileN) {
                        if (strpos($filename, $fileN) !== false) {
                            array_push($arr_file, $arrFile);
                            break;
                        }
                    }
                } else {
                    array_push($arr_file, $arrFile);
                }
            }
        }
        $mydir->close();

        return $arr_file;
    }
}

if (!function_exists('isDirFile')) {
    /**
     * 递归读取文件夹判断是否存在某个文件
     * @param string $directory 目录路径
     * @param string $fileName 文件名
     */
    function isDirFile($directory, $fileName = '', &$arr_file = array())
    {
        if (!file_exists($directory)) {
            return false;
        }
        if (empty($fileName)) {
            return false;
        }

        $mydir = dir($directory);
        while ($file = $mydir->read()) {
            if ($file == $fileName) {
                return ture;
            }
            $df = $directory . DIRECTORY_SEPARATOR . $file;
            if ((is_dir("$df")) and ($file != ".") and ($file != "..")) {
                isDirFile("$df", "$fileName", $arr_file);
            }
        }
        $mydir->close();
        return false;
    }
}

if (!function_exists('getFile')) {
    /**
     * 递归读取文件夹中的文件
     * @param string $directory 目录路径
     * @param string $fileName 文件名
     * @param boolean $isFull 是否全称对比
     */
    function getFile($directory, $fileName = '', $isFull = true)
    {
        if (!file_exists($directory)) {
            return "1";
        }
        if (empty($fileName)) {
            return "2";
        }

        $mydir = dir($directory);
        while ($file = $mydir->read()) {
            if (($file != ".") and ($file != "..")) {
                if ($isFull) {
                    if ($file == $fileName) {
                        return $directory . DIRECTORY_SEPARATOR . $file;
                    }
                } else {
                    if (strpos($file, $fileName) !== false) {
                        return $directory . DIRECTORY_SEPARATOR . $file;
                    }
                }
                $df = $directory . DIRECTORY_SEPARATOR . $file;
                if (is_dir("$df")) {
                    getDirFile("$df", "$fileName");
                }
            }
        }
        $mydir->close();
        return false;
    }
}

if (!function_exists('write')) {
    /**
     * 写文件
     * @param $path 文件路径 如D:text/ 等
     * @param $fileName 文件名 如fox.sql
     * @param $content 文件内容
     * @param $flag 默认文件内容追加
     */
    function write($path, $fileName, $content, $flag = FILE_APPEND)
    {
        if (tp_mkdir($path)) {
            $pfile = $path . $fileName;
            @file_put_contents($pfile, $content, $flag); //内容
            return $pfile;
        }
        return "";
    }
}

if (!function_exists('delDir')) {
    // 删除文件夹及所有文件
    function delDir($directory)
    { //自定义函数递归的函数整个目录
        if (file_exists($directory)) {
            if ($dir_handle = @opendir($directory)) { //打开目录返回目录资源，并判断是否成功
                while ($filename = readdir($dir_handle)) { //遍历目录，读出目录中的文件或文件夹
                    if ($filename != '.' && $filename != '..') { //一定要排除两个特殊的目录
                        $subFile = $directory . DIRECTORY_SEPARATOR . $filename; //将目录下的文件与当前目录相连
                        if (is_dir($subFile)) { //如果是目录条件则成了
                            delDir($subFile); //递归调用自己删除子目录
                        }
                        if (is_file($subFile)) { //如果是文件条件则成立
                            @unlink($subFile); //直接删除这个文件
                        }
                    }
                }
                closedir($dir_handle); //关闭目录资源
                @rmdir($directory); //删除空目录
            }
        }
    }
}

if (!function_exists('xCopy')) {
    /**
     * 文件夹复制文件
     * @param $source 源目录名
     * @param $destination 目的目录名
     * @param int $child 1:包括子目录，否则传0:不包括子目录；
     */
    function xCopy($source, $destination, $child = 1)
    {

        if (!is_dir($source)) {
            echo ("Error:the {$source} is not a direction!");
            return 0;
        }
        if (!is_dir($destination)) {
            mkdir($destination, 0777);
        }
        $handle = dir($source);
        while ($entry = $handle->read()) {
            if (($entry != ".") && ($entry != "..")) {
                if (is_dir($source . DIRECTORY_SEPARATOR . $entry)) {
                    if ($child) {
                        xCopy($source . DIRECTORY_SEPARATOR . $entry, $destination . DIRECTORY_SEPARATOR . $entry, $child);
                    }
                } else {
                    copy($source . DIRECTORY_SEPARATOR . $entry, $destination . DIRECTORY_SEPARATOR . $entry);
                }
            }
        }
        return 1;
    }
}

if (!function_exists('copyFile')) {
    /**
     * 复制文件
     * @param $sourceFile 源文件
     * @param $targetFile 目标文件
     */
    function copyFile($sourceFile, $targetFile)
    {
        if (!is_file($sourceFile)) {
            echo ("Error:the {$sourceFile} is not a file!");
            return false;
        }
        $targetDir = substr($targetFile, 0, strrpos($targetFile, DIRECTORY_SEPARATOR));
        if (tp_mkdir($targetDir)) {
            return copy($sourceFile, $targetFile);
        }
        return false;
    }
}


if (!function_exists('func_preg_replace')) {
    /**
     * 替换指定的符号
     * @param array $arr 特殊字符的数组集合
     * @param string $replacement 符号
     * @param string $str 字符串
     */
    function func_preg_replace($arr = array(), $replacement = ',', $str = '')
    {
        if (empty($arr)) {
            $arr = array('，');
        }
        foreach ($arr as $key => $val) {
            if (is_array($replacement)) {
                $replacevalue = isset($replacement[$key]) ? $replacement[$key] : current($replacement);
            } else {
                $replacevalue = $replacement;
            }
            $val = str_replace('/', '\/', $val);
            $str = preg_replace('/(' . $val . ')/', $replacevalue, $str);
        }

        return $str;
    }
}

if (!function_exists('func_param_pack')) {
    /**
     * 封装解析封装
     * @param array $params 参数数组
     * @param string $separator 分割符号
     */
    function func_param_pack($params = array(), $separator = '&')
    {
        $newParamArr = [];
        foreach ($params as $key => $v) {
            array_push($newParamArr, $key . "=" . $v);
        }
        return implode($separator, $newParamArr);
    }
}

if (!function_exists('func_random_num')) {
    /**
     * 生成随机数
     * @param int $offset 偏移量 默认0
     * @param int $length 长度 默认32
     */
    function func_random_num($offset = 0, $length = 32)
    {
        return substr(md5((string) microtime(true)), $offset, $length);
    }
}

if (!function_exists('createNonceStr')) {
    // 创建随机字符串
    function createNonceStr($length = 16)
    { //生成随机16个字符的字符串
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
}


if (!function_exists('serverIP')) {
    // 服务器端IP
    function serverIP()
    {
        // 因为解析问题可能导致后台卡
        if (!empty($_SERVER['SERVER_ADDR']) && !preg_match('/127\.0\.\d{1,3}\.\d{1,3}/i', $_SERVER['SERVER_ADDR']) && !preg_match('/192\.168\.\d{1,3}\.\d{1,3}/i', $_SERVER['SERVER_ADDR'])) {
            $serviceIp = $_SERVER['SERVER_ADDR'];
        } else {
            $serviceIp = @gethostbyname($_SERVER["SERVER_NAME"]);
        }
        return $serviceIp;
    }
}

if (!function_exists('getAccessIP')) {
    // 获取访问者id
    function getAccessIP()
    {
        static $realip;
        if (isset($_SERVER)) {
            if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
                $realip = $_SERVER["HTTP_X_FORWARDED_FOR"];
            } else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
                $realip = $_SERVER["HTTP_CLIENT_IP"];
            } else {
                $realip = $_SERVER["REMOTE_ADDR"];
            }
        } else {
            if (getenv("HTTP_X_FORWARDED_FOR")) {
                $realip = getenv("HTTP_X_FORWARDED_FOR");
            } else if (getenv("HTTP_CLIENT_IP")) {
                $realip = getenv("HTTP_CLIENT_IP");
            } else {
                $realip = getenv("REMOTE_ADDR");
            }
        }
        return $realip;
    }
}

if (!function_exists('getPageTitle')) {
    // 获取页面标题
    function getPageTitle($url)
    {
        if (empty($url)) {
            return "";
        }
        $result = get_url_content($url);
        $title = preg_match('!<title>(.*?)</title>!i', $result, $matches) ? $matches[1] : '没有标题';
        return $title;
    }
}

if (!function_exists('getBrowser')) {
    // 获取用户浏览器类型
    function getBrowser()
    {
        $agent = $_SERVER["HTTP_USER_AGENT"];
        if (strpos($agent, 'MSIE') !== false || strpos($agent, 'rv:11.0')) //ie11判断
            return "ie";
        else if (strpos($agent, 'Firefox') !== false)
            return "firefox";
        else if (strpos($agent, 'Chrome') !== false)
            return "chrome";
        else if (strpos($agent, 'Opera') !== false)
            return 'opera';
        else if ((strpos($agent, 'Chrome') == false) && strpos($agent, 'Safari') !== false)
            return 'safari';
        else
            return 'unknown';
    }
}

if (!function_exists('getFromPage')) {
    // 获取网站来源
    function getFromPage()
    {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
    }
}

if (!function_exists('search_word_from')) {
    // 搜索关键词来源
    function search_word_from()
    {
        $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        if (strstr($referer, 'baidu.com')) { //百度
            preg_match("|baidu.+wo?r?d=([^\\&]*)|is", $referer, $tmp);
            $keyword = urldecode($tmp[1]);
            $from = 'baidu';
        } elseif (strstr($referer, 'google.com') or strstr($referer, 'google.cn')) { //谷歌
            preg_match("|google.+q=([^\\&]*)|is", $referer, $tmp);
            $keyword = urldecode($tmp[1]);
            $from = 'google';
        } elseif (strstr($referer, 'so.com')) { //360搜索
            preg_match("|so.+q=([^\\&]*)|is", $referer, $tmp);
            $keyword = urldecode($tmp[1]);
            $from = '360';
        } elseif (strstr($referer, 'sogou.com')) { //搜狗
            preg_match("|sogou.com.+query=([^\\&]*)|is", $referer, $tmp);
            $keyword = urldecode($tmp[1]);
            $from = 'sogou';
        } elseif (strstr($referer, 'soso.com')) { //搜搜
            preg_match("|soso.com.+w=([^\\&]*)|is", $referer, $tmp);
            $keyword = urldecode($tmp[1]);
            $from = 'soso';
        } else {
            $keyword = '';
            $from = '';
        }
        return array('keyword' => $keyword, 'from' => $from);
    }
}

if (!function_exists('foxDate')) {
    /**
     * 时间转化日期格式
     * @param     string $format 日期格式
     * @param     intval $t 时间戳
     */
    function foxDate($format = 'Y-m-d', $t = '')
    {
        if (!empty($t)) {
            if (is_string($t)) {
                $t = strtotime($t);
            }
            $t = date($format, $t);
        }
        return $t;
    }
}

if (!function_exists("get_column_down")) {
    /**
     * 查询栏目及子栏目
     * @param $columnId 栏目id
     * @param $model 模型
     * @param $lang 语言标识
     */
    function get_column_down($columnId, $model = "", $lang = "")
    {

        $condition = "1=1";
        if (!empty($model)) {
            $modelArr = explode(",", $model);
            $modelsArr = [];
            foreach ($modelArr as $k => $md) {
                array_push($modelsArr, "'$md'");
            }
            if (sizeof($modelsArr) > 0) {
                $models = implode(",", $modelsArr);
                $condition = "`data`.column_model in ($models)";
            }
        }
        if (!empty($lang)) {
            $condition .= " and `data`.lang='{$lang}'";
        }
        $sql = <<<php
                SELECT
                    ID.`level`,
                   `data`.* 
                FROM
                    (
                    SELECT
                        @ids AS _ids,
                        ( SELECT @ids := GROUP_CONCAT( id ) FROM fox_column WHERE FIND_IN_SET( pid, @ids ) ) AS cids,
                        @l := @l + 1 AS `level` 
                    FROM
                        fox_column,
                        ( SELECT @ids:= $columnId, @l := 0 ) b 
                    WHERE
                        @ids IS NOT NULL 
                    ) ID,
                    fox_column `data` 
                WHERE
                    FIND_IN_SET( `data`.id, ID._ids ) 
                    AND $condition
                ORDER BY
                    ID.`level`
php;
        return Db::query($sql);
    }
}

if (!function_exists("get_down")) {
    /**
     * 查询自身及子数据
     * @param $tableName 表名
     * @param $id Id状态
     */
    function get_down($tableName, $id)
    {
        $sql = <<<php
                SELECT
                    ID.`level`,
                    `data`.*
                FROM
                    (
                    SELECT
                        @ids AS _ids,
                        ( SELECT @ids := GROUP_CONCAT( id ) FROM {$tableName} WHERE FIND_IN_SET( pid, @ids ) ) AS cids,
                        @l := @l + 1 AS `level` 
                    FROM
                        {$tableName},
                        ( SELECT @ids:= $id, @l := 0 ) b 
                    WHERE
                        @ids IS NOT NULL 
                    ) ID,
                    {$tableName} `data` 
                WHERE
                    FIND_IN_SET( `data`.id, ID._ids )
                ORDER BY
                    ID.`level`
php;
        return $sql;
        return Db::query($sql);
    }
}

if (!function_exists("get_column_up")) {
    /**
     * 查询栏目及父栏目
     * @param $columnId 栏目id
     * @param $lang 语言标识
     */
    function get_column_up($columnId, $model = "", $lang = "")
    {
        $condition = "1=1";
        if (!empty($model)) {
            $modelArr = explode(",", $model);
            $modelsArr = [];
            foreach ($modelArr as $k => $md) {
                array_push($modelsArr, "'$md'");
            }
            if (sizeof($modelsArr) > 0) {
                $models = implode(",", $modelsArr);
                $condition = "t2.column_model in ({$models})";
            }
        }
        if (!empty($lang)) {
            $condition .= " and t2.lang='{$lang}'";
        }
        $sql = <<<php
                SELECT
                    t1.lvl as `level`,
                    t2.*
                FROM
                    (
                    SELECT
                        @pid AS _id,
                        ( SELECT @pid := pid FROM fox_column WHERE id = _id ) AS pid,
                        @LEVEL := @LEVEL + 1 AS lvl
                    FROM
                        ( SELECT @pid := $columnId, @LEVEL := 0 ) vars,
                        fox_column c
                    WHERE
                        @pid <> 0
                    ) t1
                    JOIN ( SELECT * FROM fox_column ) t2 ON t1._id = t2.id
                where
                   {$condition}
                ORDER BY
                    t1.lvl DESC
php;
        return Db::query($sql);
    }
}

if (!function_exists("get_up")) {
    // 查询自身及父数据
    function get_up($tableName, $id)
    {
        $sql = <<<php
                SELECT
                    t1.lvl as `level`,
                    t2.*
                FROM
                    (
                    SELECT
                        @pid AS _id,
                        ( SELECT @pid := pid FROM {$tableName} WHERE id = _id ) AS pid,
                        @LEVEL := @LEVEL + 1 AS lvl 
                    FROM
                        ( SELECT @pid := {$id}, @LEVEL := 0 ) vars,
                        {$tableName} c 
                    WHERE
                        @pid <> 0 
                    ) t1
                    JOIN ( SELECT * FROM {$tableName} ) t2 ON t1._id = t2.id 
                ORDER BY
                    t1.lvl DESC
php;
        return Db::query($sql);
    }
}


if (!function_exists("cn_substr")) {
    /**
     * 去掉html标签
     * @param $content
     * @param int $length
     */
    function cn_substr(String $content, int $length = 10)
    {
        if (!empty($content) && $content != null) {
            $content = html_entity_decode($content); // 对传入的文章内容
            $content = strip_tags($content); // 去掉所有的html标签
            $content = mb_ereg_replace("\t", "", $content);
            $content = mb_ereg_replace("\r\n", "", $content);
            $content = mb_ereg_replace("\n", "", $content);
            $content = mb_ereg_replace(" ", "", $content);
            $content = mb_ereg_replace("  ", "", $content);

            if ($length > 0 && mb_strlen(trim($content), 'utf-8') > $length) {
                return mb_substr(trim($content), 0, $length, 'utf-8') . "...";
            } else {
                return trim($content);
            }
        } else {
            return $content;
        }
    }
}

if (!function_exists("en_substr")) {
    /**
     * 英文截取
     * @param $content
     * @param int $length
     */
    function en_substr(String $content, int $length = 10)
    {
        if (!empty($content) && $content != null) {
            $content = strip_tags($content); //去掉所有的html标签
            $content = mb_ereg_replace("\t", "", $content);
            $content = mb_ereg_replace("\r\n", "", $content);
            $content = mb_ereg_replace("\n", "", $content);
            $content = preg_replace('!\s+!', ' ', $content); // 多个空格变成一个
            $search = "&nbsp;"; // 多个&nbsp;变成一个&nbsp;
            $contentArr = explode($search, $content);
            $contentArr = array_unique($contentArr); // 去重
            $newContentArr = [];
            foreach ($contentArr as $key => $c) {
                if (!empty($c)) {
                    array_push($newContentArr, $c);
                }
            }
            $content = implode($search, $newContentArr);
            if ($length > 0 && mb_strlen(trim($content), 'utf-8') > $length) {
                return mb_substr(trim($content), 0, $length, 'utf-8') . "...";
            } else {
                return trim($content);
            }
        } else {
            return $content;
        }
    }
}

if (!function_exists("myTitle")) {
    /**
     * 内容长度省略
     * @param $content
     * @param int $length
     */
    function myTitle(String $content, int $length = 10)
    {
        $content = html_entity_decode($content); // 对传入的文章内容
        if ($length > 0 && mb_strlen(trim($content), 'utf-8') > $length) {
            return mb_substr(trim($content), 0, $length, 'utf-8') . "...";
        } else {
            return trim($content);
        }
    }
}

if (!function_exists("replaceSymbol")) {
    // 固定替换多个 // 与\ 符号为 /
    function replaceSymbol(string $content, $symbol = "/", $search = array('\\', '/'))
    {
        if (empty($content)) {
            return "";
        }
        $header = "";
        $end = "";
        if (str_ends_with($content, "/") || str_ends_with($content, "\\")) {
            $end = $symbol;
        }
        if (preg_match('/(http:\/\/)|(https:\/\/)/i', $content)) { // 判断是否存在
            if (preg_match('/(http:\/\/)/i', $content)) {
                $header = "http://";
                $content = mb_substr($content, 7);
            } else {
                $header = "https://";
                $content = mb_substr($content, 8);
            }
        } else {
            if (str_starts_with($content, "/") || str_starts_with($content, "\\")) {
                $header = $symbol;
            }
        }
        $content = str_replace($search, ',', $content);
        $contentArr = explode(",", $content);
        $contentArrR  = [];
        foreach ($contentArr as $k => $val) {
            if (!empty($val)) {
                array_push($contentArrR, $val);
            }
        }
        $content = $header . implode($symbol, $contentArrR) . $end;
        return $content;
    }
}

if (!function_exists("my_array_unique")) {
    /**
     * 数组对象去重
     * @param $array
     * @param bool $keep_key_assoc
     */
    function my_array_unique($array, $keep_key_assoc = false)
    {
        $duplicate_keys = array();
        $tmp = array();
        foreach ($array as $key => $val) {
            if (is_object($val))
                $val = (array)$val;

            if (!in_array($val, $tmp))
                $tmp[] = $val;
            else
                $duplicate_keys[] = $key;
        }
        foreach ($duplicate_keys as $key)
            unset($array[$key]);
        return $keep_key_assoc ? $array : array_values($array);
    }
}

if (!function_exists('add_slash')) {
    /**
     * 判断字符串斜杠 没有就加上
     * @param  $str $url 字符串
     */
    function add_slash($str)
    {
        if (empty($str)) {
            return "";
        } elseif (strpos($str, "/") !== false || strpos($str, "\\") !== false) {
            return replaceSymbol($str);
        } else {
            return "/" . $str;
        }
    }
}


if (!function_exists('is_mobile')) {
    function is_mobile()
    {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
            return TRUE;
        }
        // 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER['HTTP_VIA'])) {
            return stristr($_SERVER['HTTP_VIA'], "wap") ? TRUE : FALSE; // 找不到为flase,否则为TRUE
        }
        // 判断手机发送的客户端标志,兼容性有待提高
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $clientkeywords = array(
                'mobile',
                'nokia',
                'sony',
                'ericsson',
                'mot',
                'samsung',
                'htc',
                'sgh',
                'lg',
                'sharp',
                'sie-',
                'philips',
                'panasonic',
                'alcatel',
                'lenovo',
                'iphone',
                'ipod',
                'blackberry',
                'meizu',
                'android',
                'netfront',
                'symbian',
                'ucweb',
                'windowsce',
                'palm',
                'operamini',
                'operamobi',
                'openwave',
                'nexusone',
                'cldc',
                'midp',
                'wap'
            );
            // 从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return TRUE;
            }
        }
        if (isset($_SERVER['HTTP_ACCEPT'])) { // 协议法，因为有可能不准确，放到最后判断
            // 如果只支持wml并且不支持html那一定是移动设备
            // 如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== FALSE) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === FALSE || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return TRUE;
            }
        }
        return FALSE;
    }
}



if (!function_exists('time_diff')) {
    /**
     * 计算时间差
     * @param int $timestamp1 时间戳开始
     * @param int $timestamp2 时间戳结束
     */
    function time_diff($timestamp1, $timestamp2)
    {
        if ($timestamp2 <= $timestamp1) {
            return ['hours' => 0, 'minutes' => 0, 'seconds' => 0];
        }
        $timediff = $timestamp2 - $timestamp1;
        // 时
        $remain = (int)$timediff % 86400;
        $hours = (int)($remain / 3600);
        // 分
        $remain = (int)$timediff % 3600;
        $mins = (int)($remain / 60);
        // 秒
        $secs = (int)$remain % 60;
        $time = ['hours' => $hours, 'minutes' => $mins, 'seconds' => $secs];
        return $time;
    }
}

if (!function_exists('diff_time')) {
    // 时间差值转换
    function diff_time($timestamp)
    {
        // 时
        $remain = (int)$timestamp % 86400;
        $hours = (int)($remain / 3600);
        // 分
        $remain = (int)$timestamp % 3600;
        $mins = (int)($remain / 60);
        // 秒
        $secs = (int)$remain % 60;
        $time = ['hours' => $hours, 'minutes' => $mins, 'seconds' => $secs];
        return $time;
    }
}

if (!function_exists('num_en')) {
    // 获取字符串中的数字和英文
    function num_en($str)
    {
        preg_match_all('/[a-zA-Z0-9_]/u', $str, $matches);
        return join('', $matches[0]);
    }
}

if (!function_exists('get_between')) {
    /**
     * 截取
     * @param $str
     * @param $start 开始字符
     * @param $end 结束字符
     */
    function get_between($str, $start, $end)
    {
        $substr = substr($str, strlen($start) + strpos($str, $start), (strlen($str) - strpos($str, $end)) * (-1));
        return $substr;
    }
}

if (!function_exists('set_php_arr')) {
    /**
     * 设置php文件数组
     * @param $phpfile
     * @param $saveData
     */
    function set_php_arr($phpPath, $filename, $saveData)
    {
        //创建文件夹
        if (!tp_mkdir($phpPath)) {
            return "创建文件夹失败";
        }
        $phpfile = $phpPath . $filename;
        $str = "<?php\r\nreturn [\r\n";
        foreach ($saveData as $key => $val) {
            $str .= "\t'$key' => '$val',";
            $str .= "\r\n";
        }
        $str .= '];';
        file_put_contents($phpfile, $str);
    }
}

if (!function_exists('get_url_content')) {
    // 获取url内容
    function get_url_content($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($ch);
        return $result;
    }
}

if (!function_exists('post_url_content')) {
    // post 方式获取url内容
    function post_url_content($url, $data = array())
    {
        // 构建 POST 请求的选项
        $options = array(
            'http' => array(
                'method' => 'POST',
                'content' => http_build_query($data)
            )
        );
        // 创建上下文
        $context = stream_context_create($options);
        // 发送 POST 请求并获取响应结果
        $response = file_get_contents($url, false, $context);
        return $response;
    }
}

if (!function_exists('check_url')) {
    /**
     * 检测url网站地址是否能够访问
     * @param $url
     * @return bool
     */
    function check_url($url)
    {
        $ch = curl_init();
        $timeout = 1; // 设置超时的时间[单位：秒]
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_exec($ch);
        # 获取状态码赋值
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode == 200) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('get_dates')) {
    /**
     * 获取日期数组
     * @param string $time 默认今日
     * @param int $dateCount 最近日期数
     * @param string $format 日期结构
     */
    function get_dates($time = '', $dateCount = 7, $format = 'Y-m-d')
    {
        $time = $time != '' ? $time : time();
        //组合数据
        $dates = [];
        for ($i = 1; $i <= $dateCount; $i++) {
            $dates[$i] = date($format, strtotime('+' . $i - $dateCount . ' days', $time));
        }
        return $dates;
    }
}

if (!function_exists('get_week_first_last')) {
    // 获取一周的第一天和最后一天
    function get_week_first_last($time)
    {
        $rdata = [];
        // 当前日期
        $sdefaultDate = date("Y-m-d", $time);
        // $first =1 表示每周星期一为开始日期 0表示每周日为开始日期
        $first = 1;
        // 获取当前周的第几天 周日是 0 周一到周六是 1 - 6
        $w = date('w', strtotime($sdefaultDate));
        // 获取本周开始日期，如果$w是0，则表示周日，减去 6 天
        $week_start = date('Y-m-d', strtotime("$sdefaultDate -" . ($w ? $w - $first : 6) . ' days'));
        $rdata['week_first'] = $week_start;
        // 本周结束日期
        $week_end = date('Y-m-d', strtotime("$week_start +6 days"));
        $rdata['week_last'] = $week_end;
        return $rdata;
    }
}

if (!function_exists('get_month_first_last')) {
    // 获取当月的第一天和最后一天
    function get_month_first_last($time)
    {
        $rdata = [];
        $year = date("Y", $time);
        $month = date("m", $time);
        $allday = date("t", $time);
        $strat_time = strtotime($year . "-" . $month . "-1");
        $month_first = date('Y-m-d', $strat_time);
        $rdata['month_first'] = $month_first;
        $end_time = strtotime($year . "-" . $month . "-" . $allday);
        $month_last = date('Y-m-d', $end_time);
        $rdata['month_last'] = $month_last;
        return $rdata;
    }
}

if (!function_exists('get_app_name')) {
    // 获取获取应用项目名称
    function get_app_name()
    {
        $rp = root_path();
        $rpArr = explode(DIRECTORY_SEPARATOR, $rp);
        $ap = "foxcms";
        if (sizeof($rpArr) >= 2) {
            $ap = $rpArr[sizeof($rpArr) - 2];
        }
        return $ap;
    }
}

if (!function_exists('new_is_writeable')) {
    // 判断 文件/目录 是否可写（取代系统自带的 is_writeable 函数）
    function new_is_writeable($file)
    {
        if (is_dir($file)) {
            $dir = $file;
            if ($fp = @fopen("$dir/test.txt", 'w')) {
                @fclose($fp);
                @unlink("$dir/test.txt");
                $writeable = 1;
            } else {
                $writeable = 0;
            }
        } else {
            if ($fp = @fopen($file, 'a+')) {
                @fclose($fp);
                $writeable = 1;
            } else {
                $writeable = 0;
            }
        }
        return $writeable;
    }
}

if (!function_exists('php_path')) {
    // 获取php路径
    function php_path()
    {
        $phpArr = explode(DIRECTORY_SEPARATOR, PHP_BINARY);
        $length = sizeof($phpArr);
        $phpArr[($length - 1)] = "php";
        $php_path = implode(DIRECTORY_SEPARATOR, $phpArr);
        return $php_path;
    }
}

if (!function_exists('remove_str_bom')) {
    // 去除bom特殊字符  b"ï"  b"»"  b"¿"
    function remove_str_bom($content)
    {
        if (strlen($content) < 3) {
            return $content;
        }
        if (substr($content, 0, 3) == pack('CCC', 0xef, 0xbb, 0xbf)) {
            $content = substr($content, 3);
        }
        return $content;
    }
}

if (!function_exists('rgba_to_hex')) {
    // RGBA颜色值转十六进制颜色值
    function rgba_to_hex($rgba)
    {
        $rgba = str_replace('rgba(', '', $rgba);
        $rgba = str_replace(')', '', $rgba);
        $colors = explode(',', $rgba);
        $r = dechex($colors[0]);
        $g = dechex($colors[1]);
        $b = dechex($colors[2]);
        $a = dechex(round($colors[3] * 255));
        if (strlen($r) == 1) $r = '0' . $r;
        if (strlen($g) == 1) $g = '0' . $g;
        if (strlen($b) == 1) $b = '0' . $b;
        if (strlen($a) == 1) $a = '0' . $a;
        $hex = '#' . $r . $g . $b;
        return $hex;
    }
}

if (!function_exists('pattern_replace')) {
    // 正则替换
    function pattern_replace($pattern, $replace, $subject)
    {
        $rcontent = preg_replace($pattern, $replace, $subject);
        $is_replace = 0;
        if ($subject != $rcontent) {
            $is_replace = 1;
        }
        $rdata = [
            'subject' => $rcontent,
            'is_replace' => $is_replace
        ];
        return $rdata;
    }
}

if (!function_exists('form_replace')) {
/**
 * 替换和清理表单输入值中的潜在威胁
 */
function form_replace($fieldVal)
{
    // 使用 filter_var 进行基本的输入验证
    $fieldVal = filter_var($fieldVal, FILTER_SANITIZE_STRING);
    // 使用白名单机制，只允许特定字符通过，包括中文字符
    $fieldVal = preg_replace('/[^a-zA-Z0-9\s.,!?-，。！？、《》“”‘’（）【】《》\x{4e00}-\x{9fa5}]/u', '', $fieldVal);
    // 使用黑名单机制过滤特定模式
    $patterns = [
        '/sleep\(\d+\)/i', // 过滤 sleep(数字)
        '/(Response\.Write|print|die|eval|system|exec|passthru|shell_exec|popen|proc_open|\.php)/i', // 过滤特定单词和文件扩展名
        '/(by|By|bY)\s*\d+/i', // 去掉 by 加数字
        '/\d+\'|(\d+-+)/', // 去掉数字'或数字--
        '/\d+=\d+/', // 去掉数字=数字
        '/(\.*\/)+/', // 去掉多个 ./ 和 /
        '/\)+/', // 去掉过多 )
        '/\.+/', // 去掉多余的点
        '/(and|or)/i', // 过滤 and 或 or
        '/(<|>)/', // 过滤 < 或 >
        '/--/', // 过滤 --
        '/\bselect\b|\binsert\b|\bupdate\b|\bdelete\b|\bdrop\b|\bcreate\b/i', // 过滤 SQL 关键字
        '/\bjavascript\b|\bon\w+\b/i', // 过滤 JavaScript 关键字和事件处理程序
        '/\balert\b|\bconfirm\b|\bprompt\b/i', // 过滤 JavaScript 函数
    ];

    foreach ($patterns as $pattern) {
        $fieldVal = preg_replace($pattern, '', $fieldVal);
    }
    // 输入长度限制
    $maxLength = 255;
    if (strlen($fieldVal) > $maxLength) {
        $fieldVal = substr($fieldVal, 0, $maxLength);
    }
    // 使用 htmlentities 转换特殊字符为 HTML 实体
    $fieldVal = htmlentities($fieldVal, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return $fieldVal;
}
}
if (!function_exists('dataD')) {
    function dataD($string, $key = 'foxcms')
    {
        $ckey_length = 4;
        $key = md5($key);
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = substr($string, 0, $ckey_length);
        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);
        $string =  base64_decode(substr($string, $ckey_length));
        $string_length = strlen($string);
        $result = '';
        $box = range(0, 255);
        $rndkey = array();
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    }
}

if (!function_exists('err_code_translate')) {
    // 自定义异常错误码翻译
    function err_code_translate($code, $message)
    {
        $errcode = xn_cfg('errcode');
        $isFail = false;
        foreach ($errcode as $ecode => $emessage) {
            if (strpos($message, $ecode) !== false) {
                $message = $emessage;
                $isFail = true;
                break;
            }
        }
  
        return $message;
    }
}