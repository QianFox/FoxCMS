<?php

/**
 * 获取安装步骤
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2022/6/27   8:26
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2022/6/27   8:26
 */
function getStep()
{
    $step1 = $_GET['step'];
    // 初始化参数
    $step2 = $_POST['step'];
    if (!empty($step1)) {
        $step2 = $step1;
    }
    if (empty($step2)) {
        $step2 = 1;
    }
    return $step2;
}

/**
 * 服务器信息
 */
function getServerInfo()
{
    $serverInfo = [
        "server_domain" => $_SERVER['SERVER_NAME'],
        "server_os" => PHP_OS,
        "web_server_environment" => $_SERVER['SERVER_SOFTWARE'],
        "php_version" => phpversion(),
        "system_dir" => ROOT_PATH
    ];
    return $serverInfo;
}

/**
 * 判断session的启动状态
 */
function getSessionState()
{
    $phpV = phpversion();
    $state = true;
    if ($phpV >= "5.4.0") {
        if (session_status() == PHP_SESSION_NONE) { //没有启动
            $state = false;
        }
    } else {
        if (session_id() == '') {
            $state = false;
        }
    }
    return $state;
}

/**
 * 服务器环境监测
 */
function getEnvCheck()
{

    $phpV = phpversion();
    $php_status = false;
    if (version_compare(PHP_VERSION, '7.1.9', '>=')) {
        $php_status = true;
    }
    $mysql_status = (extension_loaded('PDO') && extension_loaded('pdo_mysql'));
    $mysql_desc = "不支持无法安装数据库，导致不能使用本系统";
    if ($mysql_status) {
        $mysql_desc = "";
    }
    $data = [
        [
            "name" => "PHP7.2+",
            "status" => $php_status,
            "desc" => "PHP版本过低",
            "ask" => "支持",
            "flag" => "php",
            "remark" => "本系统程序基本运行环境"
        ],
        [
            "name" => "MySQL",
            "status" => $mysql_status,
            "desc" => $mysql_desc,
            "ask" => "支持",
            "remark" => "本系统数据库基本环境"
        ],
        [
            'name' => 'OpenSSL',
            'status' => extension_loaded('openssl'),
            "desc" => "",
            "ask" => "支持",
            "remark" => "如不支持将导致与图片处理相关的功能无法使用"
        ],
        [
            'name' => 'GD',
            'status' => extension_loaded('gd'),
            "desc" => "",
            "ask" => "支持",
            "remark" => "用于实现加密和解密功能的扩展, 用于安全通信"
        ]
    ];
    return $data;
}

/**
 * 目录监测
 */
function getDirCheck()
{
    $data = [
        [
            "dir" => "/",
            "read_status" => is_readable(ROOT_PATH . "/"),
            "write_status" => is_writeable(ROOT_PATH . "/")
        ],
        [
            "dir" => "/config",
            "read_status" => is_readable(ROOT_PATH . "/config"),
            "write_status" => is_writeable(ROOT_PATH . "/config")
        ],
        [
            "dir" => "/uploads",
            "read_status" => is_readable(ROOT_PATH . "/uploads"),
            "write_status" => is_writeable(ROOT_PATH . "/uploads")
        ],
        [
            "dir" => "/data",
            "read_status" => is_readable(ROOT_PATH . "/data"),
            "write_status" => is_writeable(ROOT_PATH . "/data")
        ],
        [
            "dir" => "/install",
            "read_status" => is_readable(ROOT_PATH . "/install"),
            "write_status" => is_writeable(ROOT_PATH . "/install")
        ]
    ];

    return $data;
}

/**
 * 连接数据库
 */
function connDb($dbhost, $dbuser, $dbpwd, $dbport)
{
    try {
        $dsn = "mysql:host=$dbhost;port={$dbport};charset=utf8";
        $pdo = new PDO($dsn, $dbuser, $dbpwd, array(
            PDO::ATTR_TIMEOUT => 6000,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ));
        return $pdo;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * 递归创建目录
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

/**
 * 保存数据
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

function getServerURL()
{
    $prefixURL = 'http';
    if ($_SERVER["HTTPS"] == "on") {
        $prefixURL .= "s";
    }
    $prefixURL .= "://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $domain = $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"];
    } else {
        $domain = $_SERVER["SERVER_NAME"];
    }
    return [
        'url_prefix' => $prefixURL,
        'domain' => $domain
    ];
}

/**
 * 密码加密函数
 */
function xn_encrypt($password)
{
    $salt = 'fox';
    return md5(md5($password . $salt));
}

/**
 * 生成随机数
 */
function func_random_num($offset = 0, $length = 32)
{
    return substr(md5((string) microtime(true)), $offset, $length);
}

/**
 * 截取
 */
function get_between($str, $start, $end)
{
    $substr = substr($str, strlen($start) + strpos($str, $start), (strlen($str) - strpos($str, $end)) * (-1));
    return $substr;
}

/**
 * 数组下标查询
 */
function find_by_foreach($array, $find) //$array数组  $find需要查找的值
{
    foreach ($array as $key => $v) {
        if (strcmp(trim($v), $find) == 0) {
            return $key; //返回对应值的下标
        }
    }
}

function dataD($string, $key = '', $operation = 'DECODE', $expiry = 0)
{
    $ckey_length = 4;
    $key = md5($key);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';
    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) :  sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
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
    if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&  substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
        return substr($result, 26);
    } else {
        return '';
    }
}

function end_with($str, $pattern)
{
    $length = strlen($pattern);
    if ($length == 0) {
        return true;
    }
    return (substr($str, -$length) === $pattern);
}