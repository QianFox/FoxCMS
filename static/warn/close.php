<?php
$base = require __DIR__ . '/../../config/cfg/base.php';
$vinfo = require __DIR__ . '/../../data/update/version/info.php';

// 读取变量
$status_desc = $base['status_desc'] ?? "网站已关闭";
$version = $vinfo['version'] ?? "未知版本";

$html = <<<EOF
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{$status_desc}</title>
    <link rel="stylesheet" href="/static/css/foxui-1.32.min.css" />
    <link rel="stylesheet" href="/static/css/warning.min.css" />
</head>
<body>
<div class="warning-container">
    <div class="panel">
        <div class="panel-header">
            <h3>FoxCMS 系统提示信息</h3>
        </div>
        <div class="panel-main">
            <h2>{$status_desc}</h2>
            <ul>
                <li>该站点已被网站管理员停止</li>
                <li>您访问的网站已过服务期</li>
            </ul>
        </div>
        <div class="panel-footer">
            <p>Powered by FoxCMS V{$version}</p>
        </div>
    </div>
</div>
</body>
</html>
EOF;

echo $html;
