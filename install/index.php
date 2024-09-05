<?php

header('Content-Type:text/html; charset=utf-8');
// 不限制响应时间
set_time_limit(0);
define('INSTALL_PATH', str_replace('\\', '/', dirname(__FILE__)));//安装文件路径
define('ROOT_PATH', str_replace('\\', '/', dirname(INSTALL_PATH)));//项目路径
$adminconfig = require(ROOT_PATH.'/config/adminconfig.php');
define('ADMIN_PATH', $adminconfig["admin_path"]);
$commonPath = INSTALL_PATH."/install.common.php";
require_once($commonPath);

$step = getStep();
$ss = getSessionState();
if(is_file(INSTALL_PATH."/install.lock") && $step != md5("done")){//已经安装完毕
    require_once(INSTALL_PATH."/step/step0.php");
    exit();
}
if($step == 1){//还没安装本程序
    require_once(INSTALL_PATH . '/step/step1.php');
    exit();
}elseif($step == 2){//阅读许可协议
    require_once(INSTALL_PATH . '/step/step2.php');
    exit();
}elseif($step == 3){//服务器信息
    $serverURLInfo = getServerURL();
    $serverInfo = getServerInfo();
    $envInfo = getEnvCheck();
    $dirInfo = getDirCheck();
    $bg_warning = "";
    $bg_text = "";
    if($serverInfo['server_os'] !='Linux'){
        $bg_warning = "bg-warning";
        $bg_text = "建议使用Linux系统以提升程序性能";
    }
    require_once(INSTALL_PATH . '/step/step3.php');
    exit();
}elseif($step == 4){//安装完成

    $serverURLInfo = getServerURL();
    $template = require_once(INSTALL_PATH."/data/template.php");
    if (strpos($template['name'], "FoxUI") !== false) {
        $templateInfo = '<div class="foxui-input-group align-top">
                            <label>安装模板：</label>
                            <div class="foxui-radio-group">
                                <div class="foxui-radio is-checked">
                                    <span class="foxui-radio-input">
                                        <i class="foxui-radio-icon"></i>
                                        <input type="radio" value="" checked="checked" />
                                    </span>
                                    <span class="foxui-radio-label">'.$template['name'].'</span>
                                    <mark class="foxui-margin-left-8">
                                        <i class="foxui-icon-huangguan-f"></i>
                                        <span>官方出品</span>
                                    </mark>
                                </div>
                            </div>
                        </div>';
    }else{
        $templateInfo = '<div class="foxui-input-group align-top">
                            <label>安装模板：</label>
                            <div class="foxui-radio-group">
                                <div class="foxui-radio is-checked">
                                    <span class="foxui-radio-input">
                                        <i class="foxui-radio-icon"></i>
                                        <input type="radio" value="" checked="checked" />
                                    </span>
                                    <span class="foxui-radio-label">'.$template['title'].'</span>
                                </div>
                                <p class="input-info foxui-margin-left-20">模板作者：'.$template['author'].'</p>
                            </div>
                        </div>';
    }
    $dbname = "foxcms".func_random_num(0,6);
    require_once(INSTALL_PATH . '/step/step4.php');
    exit();
}elseif($step == 5){//安装选项
    $serverURLInfo = getServerURL();
    $adminPathNew = 'admin'.random_int(1000,9999);

    $oldAdminFile = ROOT_PATH."/".ADMIN_PATH.".php";
    if(file_exists($oldAdminFile)){
        $newAdminFile = ROOT_PATH."/".$adminPathNew.".php";
        $xr = rename($oldAdminFile, $newAdminFile);
        if(!$xr){
            print_r("<br/>安装失败，请联系客服人员");
        }
        $adminconfig['admin_path'] = $adminPathNew;
        set_php_arr(ROOT_PATH.'/config/',  'adminconfig.php', $adminconfig);
    }

    $adminconfig = require(ROOT_PATH.'/config/adminconfig.php');
    $adminPathNew = $adminconfig['admin_path'];

    $adminPath = $serverURLInfo['url_prefix'].$serverURLInfo['domain']."/".$adminPathNew.".php";
    $indexPath = $serverURLInfo['url_prefix'].$serverURLInfo['domain']."/index.php";
    $inp = dataD($adminconfig['install'],"foxcms");
    define('INP', $inp);
    require_once(INSTALL_PATH . '/step/step5.php');
    $fp = fopen(INSTALL_PATH . '/install.lock', 'w');
    fwrite($fp, 'FoxCMS程序已正确安装，重新安装请删除本文件');
    fclose($fp);
    exit();
}