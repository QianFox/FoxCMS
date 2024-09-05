<?php

// [ 应用入口文件 ]
namespace think;

if(version_compare(PHP_VERSION,'7.1','<') || version_compare(PHP_VERSION,'8.0','>=')) {
    $vinfo = require_once('./data/update/version/info.php');
    $pve = require_once('./static/warn/php_version_error.html');
    echo $pve;
    exit();
}

define('INSTALL_PATH', str_replace('\\', '/', dirname(__FILE__)));//安装路径
if (file_exists(INSTALL_PATH."/install/") && !is_file(INSTALL_PATH . '/install/install.lock')) {//没有安装
    header('Location:/install/index.php');
    exit();
}

$base = require('./config/cfg/base.php');

try {
    require __DIR__ . '/vendor/autoload.php';
} catch (\Throwable $e) {
    if($base['frame_exception'] == '0'){
        $pve = require_once('./static/warn/php_version_error.html');
        echo $pve;
        exit();
    }
}

//检查
$seo = require('./config/cfg/seo.php');
if($seo['pseudo_status'] == 1){
    $serverSoftware = strtolower($_SERVER['SERVER_SOFTWARE']);
    if (strpos($serverSoftware, 'apache') !== false) {
        $wFile = __DIR__."/.htaccess";
        $contet = '<IfModule mod_rewrite.c> 
    Options +FollowSymlinks -Multiviews 
    RewriteEngine on 
    RewriteCond %{REQUEST_FILENAME} !-d 
    RewriteCond %{REQUEST_FILENAME} !-f 
    RewriteRule ^(.*)$ index.php [L,E=PATH_INFO:$1] 
</IfModule>';
        config($contet, $wFile);
    } elseif (strpos($serverSoftware, 'nginx') !== false) {
    } elseif (strpos($serverSoftware, 'iis') !== false) {
        $wFile = __DIR__."/web.config";
        $contet = '<?xml version="1.0" encoding="UTF-8"?>  
<configuration>  
  <system.webServer>  
    <rewrite>  
      <rules>  
        <rule name="WPurls" enabled="true" stopProcessing="true">  
          <match url=".*" />  
          <conditions logicalGrouping="MatchAll">  
            <add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />  
            <add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />  
          </conditions>  
          <action type="Rewrite" url="index.php/{R:0}" />  
        </rule>  
      </rules>  
    </rewrite>
  </system.webServer>  
</configuration>';
        config($contet, $wFile);
    }
}

if($_SERVER['REQUEST_URI'] == "/"){
    if($_SERVER["SERVER_PORT"] == 80){
        $domain = $_SERVER["SERVER_NAME"];
    }else{
        $domain = $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"];
    }
    $prefixURL = 'http';
    if ($_SERVER["HTTPS"] == "on")
    {
        $prefixURL .= "s";
    }
    $url = "{$prefixURL}://{$domain}/plus/Access/check";
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

    if (!($httpcode == 200)) {
        header('Location:index.php');
        die("访问失败");
    }
}

// 执行HTTP应用并响应
$http = (new App())->http;

define("RUNTIME", __DIR__);

if(isApply()){
    $response = $http->run();
}else{
    if($base["status"] == 0){
        $vinfo = require_once('./data/update/version/info.php');
        setcookie("status_desc", $base["status_desc"]);
        setcookie("version", $vinfo['version']);
        require_once('./static/warn/close.php');
        exit();
    }
    $response = $http->name('home')->run();
}
$response->send();
$http->end($response);

/**
 * 判断应用
 * @return bool
 */
function isApply(){
    $uri = $_SERVER["REQUEST_URI"];
    $adminconfig = require('./config/adminconfig.php');
    $applys = require('./config/cfg/apply.php');
    $isApply = false;
    $uriArr = explode("/", $uri);
    if(str_starts_with($uri, "/index.php")){
        $app_name = $uriArr[2];
    }else{
        $app_name = $uriArr[1];
    }
    if($adminconfig["admin_path"] == $app_name){
        $isApply = true;
    }
    if(!$isApply){
        foreach ($applys as $apply){
            if($apply == $app_name){
                $isApply = true;
                break;
            }
        }
    }
    return $isApply;
}

/**
 * 配置伪静态
 */
function config($content, $file){
    if(file_exists($file)){
        $dh = file_get_contents($file);
        if (empty($dh)){
            file_put_contents($file,$content);
        }
    }else{
        file_put_contents($file,$content);
    }
}