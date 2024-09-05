{__NOLAYOUT__}<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
    <title>跳转提示</title>
    <style type="text/css">
        *{ padding: 0; margin: 0; }
        body{ background: #fff; font-family: "Microsoft Yahei","Helvetica Neue",Helvetica,Arial,sans-serif; color: #333; font-size: 16px; }
        .system-message{ padding: 24px 48px; text-align: center; color: red;}
        .system-message h1{ font-size: 100px; font-weight: normal; line-height: 120px; margin-bottom: 12px; }
        .system-message .success,.system-message .error{ line-height: 1.8em; font-size: 36px; }
        .system-message .detail{ font-size: 12px; line-height: 20px; margin-top: 12px; display: none; }
    </style>
</head>
<body>
<div class="system-message">
    <?php switch ($code) {?>
    <?php case 1:?>
    <h1>:)</h1>
    <p class="success"><?php echo(strip_tags($msg));?></p>
    <?php break;?>
    <?php case 0:?>
    <h1>:(</h1>
    <p class="error"><?php echo(strip_tags($msg));?></p>
    <?php break;?>
    <?php } ?>
    <p class="detail"></p>
</div>
</body>
</html>


<!--
 * @Descripttion : QianFox让数字化营销更简单
 * @Author       : QianFox Team
 * @Date         : 2024-01-15 09:06:11
 * @Version      : V1.08
 * @Copyright    : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditors  : QianFox Team
 * @LastEditTime : 2024-01-15 09:58:57
-->
<!DOCTYPE html>
<html lang="cn">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>FoxCMS系统提示</title>
    <link rel="stylesheet" href="/static/css/foxui-1.32.min.css" />
    <link rel="stylesheet" href="/static/css/warning.min.css" />

</head>
<body>
<div class="warning-container">
    <div class="panel">
        <div class="panel-header">
            <h3>对不起，系统运行错误</h3>
        </div>
        <div class="panel-main">
            <p>系统发生了一个未知错误，稍后再试</p>
            <p>系统无法完成您的请求，请联系管理员</p>
        </div>
        <div class="panel-footer">
            <?php $vinfo = require_once('./data/update/version/info.php'); $v = $vinfo['version'];?>
            <p>Powered by FoxCMS V<?php echo $v; ?></p>
        </div>
    </div>
</div>
</body>
</html>

