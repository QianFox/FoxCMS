<?php

namespace app\admin\controller;

use app\admin\util\TableUtil;
use app\admin\util\Zipdown;
use app\common\controller\AdminApplyBase;
use app\common\model\Apply as applyModel;
use app\common\util\FileUtil;
use think\facade\Db;
use think\facade\View;

class Apply extends AdminApplyBase
{

    public function initialize()
    {
        parent::initialize();
        $breadcrumb = array();
        array_push($breadcrumb, ['id'=>'42', 'title'=>'应用', 'name'=>'','url'=>url("Apply/index").'?columnId=42']);
        array_push($breadcrumb, ['id'=>'', 'title'=>'应用中心', 'name'=>'','url'=>url("Apply/index").'?columnId=42']);
        View::assign("breadcrumb", $breadcrumb);//面包屑
        View::assign("clickName", "应用中心");//面包屑
    }

    private function getInfo($mark){
        $imagePath = root_path()."app/".$mark."/info.ini";
        $infoContent = file_get_contents($imagePath);
        $infoContent = str_replace("\r\n", "\n", $infoContent);
        $infoArr = explode("\n", $infoContent);
        $result = [];
        foreach ($infoArr as $vo){
            $iconArr = explode("=", $vo);
            if (sizeof($iconArr) == 2){
                $result[trim($iconArr[0])] = trim($iconArr[1]);
            }
        }
        return $result;
    }

    public function stopPlugin(){

        $where = array();
        array_push($where,['status', '=', 0]);
        $condition = array();
        $list = (new applyModel())->where([$where])->where($condition)->order("sort","desc")->select()->each(function($item, $key){

            if(str_starts_with(trim($item['icon']), "foxui-icon-")){
                $item['iconHtml'] = '<i class="'.$item['icon'].'"></i>';
            }else{
                if($item['type'] != 0){//系统内置
                    $item['icon'] = url('/'.$item["mark"].'/Picture/icon');
                    $item['iconHtml'] = '<img src="'.$item['icon'].'"/>';
                }
                $item['iconHtml'] = '<img src="'.$item['icon'].'"/>';
            }
            $item['operate_status'] = 1;//操作状态 1：已安装
            $version = "1.0";
            if($item['type'] != 0){
                $info = $this->getInfo($item["mark"]);
                $version = $info['version'];
            }
            $item['version'] = $version;
            return $item;
        });
        $this->success('查询成功', '',$list);
    }

    public function index()
    {
        $param = $this->request->param();
        if($this->request->isAjax()){
            $where = array();
            array_push($where,['status', '=', 1]);
            $condition = array();
            if(array_key_exists('name', $param) && !empty($param['name'])){
                array_push($condition,['name', 'like', '%'.$param['name'].'%']);
            }
            $admin_path = config('adminconfig.admin_path');
            $list = (new applyModel())->where([$where])->where($condition)->order("sort","desc")->select()->each(function($item, $key) use ($admin_path){
                if(!empty($item["path"])){
                    if($item['type'] == 0 && $item['mark'] == "admin"){
                        $url = url($admin_path.$item["path"])."?columnId=".$item['column_id']."&apply_plugin_id=".$item['id'];
                    }else{
                        $url = url($item["mark"].$item["path"])."?columnId=".$item['column_id']."&apply_plugin_id=".$item['id'];
                    }
                    if(!empty($item['apply_menu_id'])){
                        $url = $url."&id=".$item['apply_menu_id'];
                    }
                    $item["path"] = $url;
                }
                if(str_starts_with(trim($item['icon']), "foxui-icon-")){
                    $item['iconHtml'] = '<i class="'.$item['icon'].'"></i>';
                }else{
                    if($item['type'] != 0){//系统内置
                        $item['icon'] = url('/'.$item["mark"].'/Picture/icon');
                        $item['iconHtml'] = '<img src="'.$item['icon'].'"/>';
                    }
                    $item['iconHtml'] = '<img src="'.$item['icon'].'"/>';
                }
                $item['operate_status'] = 1;//操作状态 1：已安装
                $version = "1.0";
                if($item['type'] != 0){
                    $info = $this->getInfo($item["mark"]);
                    $version = $info['version'];
                }
                $item['version'] = $version;
                return $item;
            })->toArray();
            $this->success('查询成功', '', $list);
        }
        return view('index');
    }

    public function extendPlugin(){
        $foxcmsDomain = config("adminconfig.foxcms_domain");//foxcms官网地址
        $foxcmsPathUrl = $foxcmsDomain.url("/api/apply/applyList")."?domain={$this->domainNo}&type=3";
        $resJson = get_url_content($foxcmsPathUrl);
        if(empty($resJson)){
            $this->success("查询成功", "", []);
        }
        $res = json_decode($resJson, true);
        if($res['code'] == 0){
            $this->success("查询成功", "", []);
        }
        $data = $res['data'];
        $this->success("查询成功", "", $data);
    }

    private function pluginIndexData($domain){
        $foxcmsDomain = config("adminconfig.foxcms_domain");//foxcms官网地址
        $foxcmsPathUrl = $foxcmsDomain.url("/api/apply/list")."?domain={$domain}";
        $resJson = get_url_content($foxcmsPathUrl);
        if(empty($resJson)){
            return [];
        }
        $res = json_decode($resJson, true);
        if($res['code'] == 0){
            return [];
        }
        $data = $res['data'];
        $hData = $data['data'];
        $rdata = $hData;
        if(TableUtil::check_table("fox_apply")){
            $nData = [];
            foreach ($hData as $plugin){
                $apply = Db::name('apply')->where(['mark'=>$plugin['mark']])->find();
                $url = "javascript:void(0)";
                if($apply){
                    $plugin['operate_status'] = 1;
                    if(!empty($apply["path"])){
                        $url = url($apply["mark"].$apply["path"])."?columnId=".$apply['column_id']."&apply_plugin_id=".$apply['id'];
                        if(!empty($apply['apply_menu_id'])){
                            $url = $url."&id=".$apply['apply_menu_id'];
                        }
                    }
                    if($apply['status'] == 0){
                        $plugin['operate_status'] = 3;//停用
                    }
                }else{
                    if($plugin['buy_state'] == 1){
                        $plugin['operate_status'] = 2;//未购买或授权
                    }else{
                        $plugin['operate_status'] = 0;//未安装
                    }
                }
                $plugin["path"] = $url;
                array_push($nData, $plugin);
            }
            $rdata = $nData;
        }
        return $rdata;
    }

    public function pluginIndex(){
        $data = $this->pluginIndexData($this->domainNo);
        $rdata['data'] = $data;
        $this->success("查询成功", "", $rdata);
    }

    public function getPlugin($apply_pack_id, $domain){
        $foxcmsDomain = config("adminconfig.foxcms_domain");//foxcms官网地址
        $foxcmsPathUrl = $foxcmsDomain.url("/api/apply/getPlugin");
        $resJson = post_url_content($foxcmsPathUrl, ['apply_pack_id'=>$apply_pack_id, 'domain'=>$domain]);
        return $resJson;
    }

    public function installIndex(){
        $param = $this->request->param();
        if (empty($param['id'])){
            $this->error("抱歉缺少安装参数");
        }
        $resJson = $this->getPlugin($param['id'], $this->domainNo);
        if(empty($resJson)){
            $this->error("安装失败");
        }
        $res = json_decode($resJson, true);
        if($res['code'] == 0){
            $this->error($res['msg']);
        }
        View::assign("apply_pack_id", $param['id']);
        $rdata = $res['data'];
        $applyKey = "apply-mark-{$param['id']}";
        saveToCache($applyKey, $rdata['mark']);
        $url = $rdata['url'];
        $urlKey = "apply-url-{$param['id']}";
        saveToCache($urlKey, $url);
        $envCheckList = [];
        if (!empty($url)){
            $url = replaceSymbol($url);
            $infoArr = explode("/", $url);
            $filename = $infoArr[sizeof($infoArr)-1];
            $filenameArr = explode(".", $filename);
            $foxcmsDomain = config("adminconfig.foxcms_domain");//foxcms官网地址
            $applyInfoPath = $foxcmsDomain."/plugins/{$filenameArr[0]}.ini";
            $content = get_url_content($applyInfoPath);
            $envInfo = json_decode($content, true);
            $apply = $envInfo[0];
            View::assign("version", $apply);
            $version_flag = "apply-{$param['id']}";
            View::assign("version_flag", $version_flag);
            saveToCache($version_flag, json_encode($apply));
            foreach ($envInfo as $key=>$env){
                if($key == 0){
                    continue;
                }
                if(str_starts_with($env['name'], "PHP")){
                    $php_status = false;
                    if(version_compare(PHP_VERSION,'7.1.9','>=')) {
                        $php_status = true;
                    }
                    $env['status'] = $php_status;
                }elseif ($env['name'] == "MySQL"){
                    $mysql_status = (extension_loaded('PDO') && extension_loaded('pdo_mysql'));
                    $env['status'] = $mysql_status;
                }elseif ($env['name'] == $env['desc']){
                    $write_status = is_writeable(root_path()."{$env['dir']}");
                    $env['status'] = $write_status;
                }
                array_push($envCheckList, $env);
            }
        }
        View::assign("envCheckList", $envCheckList);
        return view();
    }

    public function download(){
        $param =  $this->request->param();
        $apply_pack_id = $param['apply_pack_id'];
        $urlKey = "apply-url-{$apply_pack_id}";
        $url = saveToCache($urlKey);
        if (empty($url)){
            $this->error("下载失败");
        }
        $fileUtil = new FileUtil();
        $path = root_path().'plugins/';
        $path = replaceSymbol($path);
        if(!tp_mkdir($path)){
            $this->error("创建文件夹失败");
        }
        $filepath = $fileUtil->download($url, "", $path);
        $fpArr = explode(".", $filepath);
        if(sizeof($fpArr) <= 0){
            $this->error("下载失败");
        }
        $apply_download_key = "apply_download_{$apply_pack_id}";
        saveToCache($apply_download_key, $filepath);
        $this->success("下载完成", "", $apply_download_key);
    }

    public function install(){
        $param =  $this->request->param();
        $apply_pack_id = $param['apply_pack_id'];

        $apply_download_key = "apply_download_{$apply_pack_id}";
        $filepath = saveToCache($apply_download_key);
        $applyKey = "apply-mark-{$apply_pack_id}";
        $mark = saveToCache($applyKey);
        if(empty($mark) || empty($filepath)){

            $resJson = $this->getPlugin($apply_pack_id, $this->domainNo);
            if(empty($resJson)){
                $this->error("安装失败", "", $resJson);
            }
            $res = json_decode($resJson, true);
            if($res['code'] == 0){
                $this->error("安装失败");
            }
            $rdata = $res['data'];
            if(empty($mark) && !empty($rdata['mark'])){
                $mark = $rdata['mark'];
                saveToCache($applyKey, $mark);
            }
            if(empty($filepath)){
                $url = $rdata['url'];
                if(!empty($url)){
                    $position = strpos($url, $mark);
                    $filename= substr($rdata['url'], $position);
                    $path = root_path().'plugins/'.$filename;
                    $path = replaceSymbol($path);
                    if(file_exists($path)){
                        $filepath = $path;
                        saveToCache($apply_download_key, $filepath);
                    }
                }
            }
            if(empty($mark) || empty($filepath)) {
                $this->error("安装失败1", "", $mark."-".$filepath);
            }
        }

        $filepath = replaceSymbol($filepath);

        $zipdown = new Zipdown();
        $apply_path = app()->getBasePath();
        $r = $zipdown->unZipFile($filepath, $apply_path);
        if($r){
            $plugin_path = $apply_path."{$mark}".DIRECTORY_SEPARATOR."data".DIRECTORY_SEPARATOR;
            if(is_dir($plugin_path)){
                $arr_file = array();
                $activepath = $plugin_path;
                $arr_file = dirFile($plugin_path, $activepath, $arr_file);
                $exeErrSql = [];//执行错误的sql语句

                foreach ($arr_file as $file){
                    if(str_ends_with($file, ".sql")){//判断是否为sql文件
                        $sqlContent = @file_get_contents($file);
                        $sqlArr = TableUtil::sql_split($sqlContent);
                        foreach ($sqlArr as $sql){
                            if(trim($sql) == ''){
                                continue;
                            }
                            $sqlItms = explode(" ", $sql);
                            if(sizeof($sqlItms) <= 0){
                                continue;
                            }
                            $firstItem = trim($sqlItms[0]);
                            $tableName = "";
                            if($firstItem == "DROP"){//删除表
                                continue;
                            }elseif ($firstItem == "CREATE"){//创建表
                                if(sizeof($sqlItms) >= 3){
                                    $tableName = trim($sqlItms[2]);
                                    if(TableUtil::check_table($tableName)){//判断表是否存在
                                        continue;
                                    }
                                }
                            }elseif ($firstItem == "INSERT"){//插入数据
                                if(sizeof($sqlItms) >= 3){
                                    $tableName = trim($sqlItms[2]);
                                }
                            }
                            if(empty($tableName)){
                                continue;
                            }
                            //执行sql语句
                            try{
                                Db::execute($sql);
                            }catch (\Exception $e){
                                array_push($exeErrSql, $sql);
                            }
                        }
                    }
                }

                delDir($plugin_path);//删除插件数据
                $apply = xn_cfg("apply");
                if(!in_array($mark, $apply)){
                    array_push($apply, $mark);
                    set_php_arr(config_path()."cfg/",  'apply.php', $apply);
                }
                if(sizeof($exeErrSql) > 0){
                    $this->error("抱歉你安装的应用数据存在冲突！", "", $exeErrSql);
                }
                @unlink($filepath);//删除插件下载包
                $this->success("安装成功");
            }
        }
    }

    public function nextInstallIndex(){
        $param = $this->request->param();
        $step = $param['step']??1;
        $version_flag = "apply-{$param['apply_pack_id']}";
        $vserionStr = saveToCache($version_flag);
        $vserion = [];
        if(empty($vserionStr) && !empty($param['apply_pack_id'])){
            $resJson = $this->getPlugin($param['apply_pack_id'], $this->domainNo);
            $res = json_decode($resJson, true);
            View::assign("apply_pack_id", $param['apply_pack_id']);
            $rdata = $res['data'];
            $url = $rdata['url'];
            if (!empty($url)) {
                $url = replaceSymbol($url);
                $infoArr = explode("/", $url);
                $filename = $infoArr[sizeof($infoArr) - 1];
                $filenameArr = explode(".", $filename);
                $foxcmsDomain = config("adminconfig.foxcms_domain");//foxcms官网地址
                $applyInfoPath = "{$foxcmsDomain}/plugins/{$filenameArr[0]}.ini";
                $content = get_url_content($applyInfoPath);
                $envInfo = json_decode($content, true);
                $vserion = $envInfo[0];
            }
        }else{
            $vserion = json_decode($vserionStr, true);
        }
        View::assign("version", $vserion);
        if($step == 1){
            View::assign("apply_pack_id", $param['apply_pack_id']);
            return view("install_index1");
        }else{
            $myApply = url("Apply/index?columnId=42");
            View::assign("myApply", $myApply);

            $applyKey = "apply-mark-{$param['apply_pack_id']}";
            $mark = saveToCache($applyKey);
            $apply = \app\common\model\Apply::where(['mark'=>$mark])->find();
            if($apply){
                $url = url($apply["mark"].$apply["path"])."?columnId=".$apply['column_id']."&apply_plugin_id=".$apply['id'];
            }else{
                $url = "javascript:void(0)";
            }
            View::assign("apply_url", $url);
            return view("install_index2");
        }
    }

    public function uninstallIndex(){
        $param = $this->request->param();
        if(empty($param['id'])){
            $this->error("打包失败，缺少参数");
        }
        View::assign("id", $param['id']);
        $apply = \app\common\model\Apply::find($param['id']);
        $version = $this->getInfo($apply['mark']);
        $version['icon'] = url('/'.$apply["mark"].'/Picture/icon');
        $version['link'] = $apply['link'];
        View::assign("version", $version);
        return view();
    }

    public function uninstallPlugin(){
        $param = $this->request->param();
        if(empty($param['id'])){
            $this->error("打包失败，缺少参数");
        }
        $id = $param['id'];
        try{
            $apply = \app\common\model\Apply::find($id);
            if($apply == null){
                $this->error("抱歉没找卸载应用");
            }else{
                $apply_path = app()->getBasePath()."{$apply['mark']}";
                if(is_dir($apply_path)){
                    delDir($apply_path);//删除插件目录
                }
            }
            \app\common\model\Apply::destroy($id);//删除应用数据
            if(TableUtil::check_table("fox_apply_menu")){
                Db::name('apply_menu')->where(['apply_plugin_id'=>$id])->delete();//删除插件应用菜单数据
            }
            if(TableUtil::check_table("fox_apply_table")){
                $atList = Db::name('apply_table')->where(['apply_plugin_id'=>$id])->select();//查询插件表
                foreach ($atList as $at){
                    if (TableUtil::check_table($at['table'])){
                        TableUtil::del_table($at['table']);//删除表
                    }
                }
                Db::name('apply_table')->where(['apply_plugin_id'=>$id])->delete();
            }
        }catch (\Exception $e){
            $this->error("卸载插件失败");
        }
        $this->success("卸载插件成功");
    }

    public function updateStatus()
    {
        $param = $this->request->param();
        if(empty($param['id'])){
            $this->error("缺少更新参数");
        }
        $apply = \app\common\model\Apply::field("status")->find($param['id']);
        if(!$apply){
            $this->error("没找到修改数据");
        }
        $status = $apply['status'] == '启用'?0:1;
        $r = \app\common\model\Apply::update(['id'=>$param['id'], 'status'=>$status]);
        if($r <= 0){
            $this->error("更新失败");
        }
        $this->success('操作成功', "", $apply);
    }
}