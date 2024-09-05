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
            <h3>FoxCMS 系统提示信息</h3>
        </div>
        <div class="panel-main">
            <?php echo $message ?>
        </div>
        <div class="panel-footer">
            <?php $vinfo = require_once('./data/update/version/info.php'); $v = $vinfo['version'];?>
            <p>Powered by FoxCMS V<?php echo $v; ?></p>
        </div>
    </div>
</div>
</body>
</html>
