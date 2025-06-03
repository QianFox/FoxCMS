<?php

    /**
     * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
     * @Author : FoxCMS Team
     * @Date : 2023/3/12   10:19
     * @version : V1.08
     * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
     * @LastEditTime : 2023/3/12   10:19
    */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\model\ColumnLevel;
use app\common\util\SitemapUtil;
use app\taglib\fox\TagList;
use think\facade\Cache;
use think\facade\Db;
use think\facade\Session;
use think\facade\View;

// SEO 管理
class Seo extends AdminBase
{
    //配置文件目录
    protected $folder = 'cfg';

    private $level = 3;
    private $pageColumnList = [];//记录生成栏目的数据顺序
    private $html_save_path = "html";//静态文件跟目录
    private $rootUrl = "";//站点根网址
    private $mobileUrl = "";//手机域名
    private $buildCount = 0;//需要生成静态文章数
    protected $view_suffix;//文件后缀
    protected $home_lang;

    public function initialize()
    {
        parent::initialize();
        $breadcrumb = array();
        array_push($breadcrumb, ['id'=>'42', 'title'=>'SEO', 'name'=>DIRECTORY_SEPARATOR. config('adminconfig.admin_path').'/Seo/index?columnId=53','url'=>DIRECTORY_SEPARATOR. config('adminconfig.admin_path').'/Seo/index?columnId=53']);
        array_push($breadcrumb, ['id'=>'', 'title'=>'SEO设置', 'name'=>'','url'=>'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);//面包屑
        View::assign("clickName", "SEO");//面包屑

        //栏目列表
        $columnLevels = ColumnLevel::select();
        $this->level = 3;
        if(sizeof($columnLevels) > 0){
            $this->level = $columnLevels[0]['level'];
        }
        $columns = \app\common\model\Column::where('level', '<=' , $this->level)->where([['lang', '=', $this->getMyLang()]])->order('sort ASC,id DESC')->select();
        $columnList = $this->channelLevel($columns);
        array_unshift($columnList, ["columnName"=>"所有栏目", "columnId"=>-1]);
        View::assign("columnList", $columnList);
        //获取静态文件存放目录
        $basic = getBasic();
        if($basic){
            $home_lang = xn_cfg("base.home_lang");//默认语言
            $cur_lang = $this->getMyLang();
            if($cur_lang == $home_lang){
                if(!empty($basic["html_save_path"])){
                    $this->html_save_path = $basic["html_save_path"];
                }else{
                    $this->html_save_path = "html";
                }
            }else{
                $this->html_save_path = "{$cur_lang}";
            }
            $this->rootUrl = $this->domain;
            if(!empty($basic["mobile_domain"])){
                $this->mobileUrl = "/".$basic["mobile_domain"];
            }
        }
        View::assign("html_save_path", $this->html_save_path);
        $this->view_suffix = config('view.view_suffix');
        $this->home_lang = xn_cfg("base.home_lang");
    }

    private function config($content, $file){
        if(file_exists($file)){
            $dh = file_get_contents($file);
            if (empty($dh)){
                $dh = file_put_contents($file,$content);
            }
        }else{
            $dh = file_put_contents($file,$content);
        }
        if(!$dh){
            $this->error("生成伪静态配置失败，请手动配置");
        }
    }

    // 伪静态状态修改
    public function pseudoUpdate(){
        $param = $this->request->param();
        $data = xn_cfg("seo");
        $data['pseudo_status'] = $param['pseudo_status'];
        $this->_set($data);

        if($param['pseudo_status'] == 1){//开启伪静态配置，且生成配置
            $serverSoftware = strtolower($_SERVER['SERVER_SOFTWARE']) ?? '';
            if (strpos($serverSoftware, 'apache') !== false) {
                $wFile = root_path().".htaccess";
                $contet = '<IfModule mod_rewrite.c> 
    Options +FollowSymlinks -Multiviews 
    RewriteEngine on 
    RewriteCond %{REQUEST_FILENAME} !-d 
    RewriteCond %{REQUEST_FILENAME} !-f 
    RewriteRule ^(.*)$ index.php [L,E=PATH_INFO:$1] 
</IfModule>';
                $this->config($contet, $wFile);
                $this->success("Apache服务器伪静配置生成成功");
            } elseif (strpos($serverSoftware, 'nginx') !== false) {
                $this->success("Nginx服务请手动配置");
            } elseif (strpos($serverSoftware, 'iis') !== false) {
                $wFile = root_path()."/web.config";
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
                $this->config($contet, $wFile);
                $this->success("IIS服务器伪静配置生成成功");
            }
        }
        $this->success("操作成功");
    }

    public function index(){
        //生成路由 //1:动态url,2:伪静态化,3:静态页面
        $seo = xn_cfg("seo");
        View::assign("url_model", $seo['url_model']);
        View::assign("column_page", $seo['column_page']);
        View::assign("document_page", $seo['document_page']);
        View::assign("pseudo_status", $seo['pseudo_status']);
        $webServer = strtolower($_SERVER['SERVER_SOFTWARE']);
        if (stristr($webServer, 'apache')) {
            $webServer = 'apache';
        } else if (stristr($webServer, 'iis')) {
            $webServer = 'iis';
        } else if (stristr($webServer, 'nginx')) {
            $webServer = 'nginx';
        }else{
            $webServer = 'other';
        }
        View::assign("webServer", $webServer);
        return view();
    }

    // 返回多层栏目
    private function channelLevel($dataArr, $pid = 0, $fieldPri = 'id', $fieldPid = 'pid', $level = 1)
    {
        if (empty($dataArr)) {
            return array();
        }
        $rArr = [];
        foreach ($dataArr as $v) {
            if ($v[$fieldPid] == $pid) {
                $arr = array();
                $arr['columnId'] = $v->id;
                $arr['columnName'] = $v->name;
                $arr['isShow'] = $v->status;
                $arr['columnSort'] = $v->sort;
                $arr['level'] = $level;
                $arr['children'] = self::channelLevel($dataArr, $v[$fieldPri], $fieldPri, $fieldPid, $level + 1);
                foreach ($this->modelRecords as $k=>$vo){
                    if($vo->id == $v->column_model){
                        $arr['modelTitle'] = $vo->title;
                        break;
                    }
                }
                array_push($rArr, $arr);
            }
        }
        return $rArr;
    }

    // 获取栏目页顺序
    private function getColumns($dataArr, $pid = 0, $fieldPri = 'id', $fieldPid = 'pid', $level = 1)
    {
        foreach ($dataArr as $v) {
            if ($v[$fieldPid] == $pid) {
                array_push($this->pageColumnList, $v);
                self::getColumns($dataArr, $v[$fieldPri], $fieldPri, $fieldPid, $level + 1);
            }
        }
    }

    // 获取生成静态文件数
    private function getBuildCount($columns){
        foreach ($columns as $column){
            $this->buildCount +=1;//栏目数
            if($column['column_model'] == "formmodel"){//特殊的表单模型处理
                continue;
            }
            if($column["column_attr"] == 0){//内容栏目
                $this->buildCount += Db::name($column['column_model'])->where(['column_id'=>$column['id']])->count();
            }elseif ($column["column_attr"] == 2){//内联栏目
                $inner_column = $column['inner_column'];//内联栏目id
                if(!empty($inner_column)){
                    $innerColumn = \app\common\model\Column::field("column_model")->find($inner_column);
                    $this->buildCount += Db::name($innerColumn['column_model'])->where(['column_id'=>$inner_column])->count();
                }
            }
        }
    }


    // 生成静态html文件
    public function allSite(){
        //生成路由 //1:动态url,2:伪静态化,3:静态页面
        $param = $this->request->param();

        if(array_key_exists("first", $param)){//第一次请求
            //直接修改为静态化页面
            try{
                $data = xn_cfg("seo");
                $data['url_model'] = 3;
                $data['column_page'] = $param['column_page'];
                $data['document_page'] = $param['document_page'];
                $this->_set($data);
                xn_add_admin_log('seo写入--'.json_encode($data), "seo");
                Cache::clear();
            }catch (\Exception $e) {
            }

            $this->pageColumnList = [];
            $oneId = $param["oneId"];
            if($oneId == "key1" || $oneId == "key3"){//整站页面
                if(($oneId == "key1") || ($oneId == "key3" && array_key_exists("columnId", $param) && $param["columnId"] == -1)){
                    //生成网站地图
                    try{
                        (new SitemapUtil())->generateSitemap('all',$this->domain,$this->getMyLang());
                    }catch (\Exception $e){}

                    //生成首页
                    //  $htmlpath = (root_path()."/");
                    try{
                        $this->buildHtml("", "column", "index",  "", "index", null);
                    }catch (\Exception $e) {
                        $this->error("创建静态文件失败". $e->getMessage());
                    }

                    //生成tag静态
                    try{
                        $htmlpathT = root_path()."/{$this->html_save_path}/tag";

                        $this->buildTagHtml($htmlpathT);
                    }catch (\Exception $e) {
                        $this->error("创建生ag静态文件失败". $e->getMessage());
                    }

                    $columns = \app\common\model\Column::where('level', '<=' , $this->level)->where([['lang', '=', $this->getMyLang()]])->order('sort asc,id asc')->select();
                    if(sizeof($columns) > 0){
                        $this->getColumns($columns);//重新栏目数组
                        $this->getBuildCount($this->pageColumnList);//获取生成静态文件数
                        $firstColumn = $this->pageColumnList[0];//第一个栏目
                        $this->buildColumn($firstColumn);//生成静态栏目页
                        //缓存栏目
                        saveToCache(Session::getId()."_pageColumnList", json_encode($this->pageColumnList), 0);
                        saveToCache(Session::getId()."_buildCount", $this->buildCount, 0);
                        $this->success("继续创建静态文件", "", ['index'=>1, "detail_index"=>0, "curColumnIndex"=>0, "columnId"=>$firstColumn["id"], "buildCount"=>$this->buildCount,"flag"=>"column"]);
                    }else{
                        $this->error("没有栏目数据");
                    }
                }elseif($oneId == "key3" && array_key_exists("columnId", $param) && $param["columnId"] != -1){
                    $columnsUp = get_column_up(intval($param["columnId"]), "", $this->getMyLang());
                    $columnsDown = get_column_down(intval($param["columnId"]), "", $this->getMyLang());
                    $columns = array_merge($columnsUp, $columnsDown);
                    $columns = my_array_unique($columns);
                    if(sizeof($columns) > 0){
                        $this->getColumns($columns);//重新栏目数组
                        $this->getBuildCount($this->pageColumnList);//获取生成静态文件数
                        $firstColumn = $this->pageColumnList[0];//第一个栏目
                        $this->buildColumn($firstColumn);
                        //缓存栏目
                        saveToCache(Session::getId()."_pageColumnList", json_encode($this->pageColumnList), 0);
                        saveToCache(Session::getId()."_buildCount", $this->buildCount, 0);
                        $this->success("继续创建静态文件", "", ['index'=>1, "detail_index"=>0, "curColumnIndex"=>0, "columnId"=>$firstColumn["id"], "buildCount"=>$this->buildCount,"flag"=>"column"]);

                    }else{
                        $this->error("没有栏目数据");
                    }
                }

            }elseif ($oneId == "key2"){//首页
                //生成网站地图
                try{
                    (new SitemapUtil())->generateSitemap('all',$this->domain, $this->getMyLang());
                }catch (\Exception $e){}

                // $htmlpath = (root_path()."/");
                try{
                    $r =  $this->buildHtml("", "column", "index",  "", "index", null);
                }catch (\Exception $e) {
                    $this->error("创建静态文件失败". $e->getMessage());
                }
                $this->success("创建静态文件成功");
            }
        }else{//继续
            $curColumnIndex = $param["curColumnIndex"];
            $index = $param["index"];
            if(empty($curColumnIndex) && $curColumnIndex != 0){
                //清除缓存栏目
                saveToCache(Session::getId()."_pageColumnList", null);
                saveToCache(Session::getId()."_buildCount", null);
                $this->error("继续生产静态文件,参数失败！");
            }
            $curColumnIndex = intval($curColumnIndex);
            $index = intval($index);
            //获取静态文件总数
            $buildCountStr = saveToCache(Session::getId()."_buildCount");
            $buildCount = intval($buildCountStr);
            //清除缓存栏目
            $pageColumnListStr = saveToCache(Session::getId()."_pageColumnList");
            $this->pageColumnList = json_decode($pageColumnListStr);
            if($curColumnIndex >= sizeof($this->pageColumnList)){
                //清除缓存栏目
                saveToCache(Session::getId()."_buildCount", null);
                saveToCache(Session::getId()."_pageColumnList", null);
                $this->success("创建静态完成");
            }
            $detail_index = intval($param["detail_index"]);

            if($detail_index > -1){
                $curColumn = $this->pageColumnList[$curColumnIndex];//当前栏目
                $curColumn = get_object_vars($curColumn);//对象转数组
                $detail_index = $this->buildDetail($curColumn, $detail_index);
                if($detail_index > 0){
                    $index += $detail_index;
                }
                $this->success("继续生成静态文档文件", "", ['index'=>$index, "detail_index"=>$detail_index, "curColumnIndex"=>$curColumnIndex, "columnId"=>$curColumn["id"], "buildCount"=>$buildCount,"flag"=>"detail"]);
            }else{
                $curColumnIndex = $curColumnIndex+1;
                if($curColumnIndex >= sizeof($this->pageColumnList)){
                    //清除缓存栏目
                    saveToCache(Session::getId()."_pageColumnList", null);
                    saveToCache(Session::getId()."_buildCount", null);
                    $this->success("创建静态完成");
                }
                $curColumn = $this->pageColumnList[intval($curColumnIndex)];//当前栏目
                $curColumn = get_object_vars($curColumn);//对象转数组
                $this->buildColumn($curColumn);
                $this->success("继续生成静态栏目文件", "", ['index'=>($index+1), "detail_index"=>0, "curColumnIndex"=>$curColumnIndex, "columnId"=>$curColumn["id"], "buildCount"=>$buildCount,"flag"=>"column"] );
            }
        }
    }

    // 找到父类路径
    private function getParent($pid){
        foreach ($this->pageColumnList as $pageC){
            if($pid == $pageC->id){
                return $pageC;
            }
        }
        return [];
    }

    // 获取生产静态文件路径
    private function getBuildPath($column, $flag=1){
        // $htmlpathC = root_path()."/{$column['lang']}/";
        $htmlpathC = root_path()."/{$this->html_save_path}/";
        $pid = $column["pid"];
        if($pid == 0){//说明是子栏目
            $htmlpathC = $htmlpathC.$column["dir_path"]."/";
            return $htmlpathC;
        }
        if($flag == 0){//不全路径
            //子栏目存放路径
            while ($pid != 0){
                $pColumn = $this->getParent($pid);
                if($pColumn->pid != 0){
                    $pid = $pColumn->pid;
                }else{
                    $htmlpathC = $htmlpathC.$pColumn->dir_path."/";
                    $pid = 0;
                }
            }
        }elseif ($flag == 1){//全路径
            $parentPathArr = [];
            while ($pid != 0){
                $pColumn = $this->getParent($pid);
                array_push($parentPathArr, $pColumn->dir_path);
                if($pColumn->pid != 0){
                    $pid = $pColumn->pid;
                }else{
                    $pid = 0;
                }
            }
            $parentPathArr = array_reverse($parentPathArr);
            $htmlpathC = $htmlpathC.implode("", $parentPathArr)."/".$column["dir_path"]."/";
        }
        return $htmlpathC;
    }

    // 检查生成静态文件栏目是否规范
    private function checkBuildColumn($column){
        if(empty($column['column_template'])){
            $this->error("栏目id为{$column['id']}的模板数据为空","",  ['flag'=>"column", "column"=>$column]);
        }
        $tempHtml = "list_images.{$this->view_suffix}";
        if(!empty($column['column_template'])){
            $tempHtml = $column['column_template'];
        }
        $template = $this->templateHtml . $tempHtml;
        if(!file_exists($template)){
            $this->error("栏目id为{$column['id']}的模板文件未找到","",  ['flag'=>"column", "column"=>$column]);
        }
    }

    // 创建栏目及详情静态
    private function buildColumn($column){
        if($column["column_attr"] == 0){//内容栏目
            $this->checkBuildColumn($column);
        }elseif ($column["column_attr"] == 2){//内联栏目
            $inner_column = $column['inner_column'];//内联栏目id
            $innerColumn = \app\common\model\Column::find($inner_column);
            $this->checkBuildColumn($innerColumn);
        }
        $pid = $column["pid"];//父id
        $column_model = $column['column_model'];//模型
        $columnId = $column["id"];
        //创建栏目模板
        //栏目
        $column_page = xn_cfg("seo.column_page");//栏目生成方式
        $htmlfile = "index";
        if($column_page == 1 && $pid != 0){
            $htmlfile = "list_".$column['id'];//子栏目名称
            $htmlpathC = $this->getBuildPath($column, 0);
        }else{
            $htmlpathC = $this->getBuildPath($column);
        }
        $this->buildHtml($columnId, $column_model, $htmlfile, $htmlpathC, "column", $column);
    }

    // 生成详情
    private function buildDetail($column, $detail_index){
        $column_model = $column['column_model'];//模型
        if($column_model == "formmodel"){//特殊的表单模型处理
            return -1;
        }
        $pageDetailListStrList = [];
        if($column["column_attr"] == 0){//内容栏目
            $pageDetailListStrList = Db::name($column_model)->where("column_id", intval($column['id']))->select();
        }elseif ($column["column_attr"] == 1){//外链栏目
            return -1;
        }elseif ($column["column_attr"] == 2){//内联栏目
            $inner_column = $column['inner_column'];//内联栏目id
            $innerColumn = \app\common\model\Column::find($inner_column);
            $column_model = $innerColumn['column_model'];
            $pageDetailListStrList = Db::name($column_model)->where("column_id", intval($column['id']))->select();
            $column = $innerColumn;
        }
        $rm = \app\common\model\ModelRecord::where(["nid"=>$column_model, "reference_model"=>0])->find();
        if(!$rm){
            return -1;
        }
        if($detail_index >= sizeof($pageDetailListStrList)){
            return -1;
        }else{
            //创建模型即详情
            $document_page = xn_cfg("seo.document_page");//文档生成方式
            $htmlpathM = $this->getBuildPath($column);
            $jIndex = 1;
            for ($detail_index; $detail_index < sizeof($pageDetailListStrList); $detail_index++){
                if($jIndex <= 5){
                    $modelData = $pageDetailListStrList[$detail_index];
                    if($document_page == 1){
                        $htmlpathD = $htmlpathM."/".foxDate("Ymd", $modelData["create_time"])."/";
                    }else{
                        $htmlpathD = $htmlpathM;
                    }
                    $this->buildHtml($modelData["id"], $column_model, $modelData["id"], $htmlpathD, "detail", $column);
                    $jIndex++;
                }else{
                    break;
                }
            }
            return $detail_index;
        }
    }

    // 生成html静态文件
    private function buildHtml($id, $column_model="", $htmlfile = '', $htmlpath = '', $flag="", $column=null){
        $lang_flag = "";
        if($column != null){
            $home_lang = xn_cfg("base.home_lang");//默认语言
            $lang_flag = $column['lang'];
            if($lang_flag == $home_lang){
                $lang_flag = "";
            }
        }
        $htmlpath = !empty($htmlpath) ? $htmlpath:(root_path()."/{$this->html_save_path}/");
        $htmlpath = replaceSymbol($htmlpath);
        if(!tp_mkdir($htmlpath)){
            return "创建文件夹失败";
        }
        if($flag=="column"||$flag=="detail"){
            if($flag == "column"){//栏目
                $relationUrl = replaceSymbol("/{$lang_flag}/{$column_model}/index/{$id}");
                $url = $this->rootUrl.url("{$relationUrl}");
                $relationUrl = url($relationUrl);
                if($column != null){
                    $limit_column = $column["limit_column"];
                    $data_limit = $column["data_limit"];
                    $isSelf = true;
                    if($column["column_attr"] == 1){//外部链接
                        $url = $column["out_link_head"].$column["out_link"];
                        $isSelf = false;
                    }elseif ($column["column_attr"] == 2){//内链栏目
                        $innerColumn = \app\common\model\Column::find($column["inner_column"]);
                        $cur_url = replaceSymbol("/{$lang_flag}/{$innerColumn['column_model']}/index/{$innerColumn['id']}");
                        $url = $this->rootUrl.url("{$cur_url}");
                        $column_model = $innerColumn['column_model'];
                        $id = $innerColumn['id'];
                        $limit_column = $innerColumn["limit_column"];
                        $data_limit = $innerColumn["data_limit"];
                    }
                    if($isSelf){//可能存在分页
                        $tempHtml = "list_{$column["column_model"]}.{$this->view_suffix}";
                        if(!empty($column["column_template"])){
                            $tempHtml = $column['column_template'];
                        }
                        $template = $this->templateHtml . $tempHtml;
                        $fileContent = file_get_contents($template);
                        $param = getPageList($fileContent);//获取页面中分页的分页标签配置
                        if(sizeof($param) > 0){
                            $typeid = $param['typeid'];
                            if(empty($typeid)){
                                if($data_limit == 1){//仅本栏目
                                    $typeid = (string)$id;
                                }elseif ($data_limit == 2){//本栏目及下级栏目
                                    $typeid = $limit_column;
                                }elseif ($data_limit == 3){//本栏目及指定子栏目
                                    $typeid = $limit_column;
                                }
                            }
                            $columnModel = $column_model;

                            $flag = $param["flag"];
                            $orderby = $param['orderby']??'create_time';//默认发布时间排序
                            $orderway = $param['orderway']??'desc';//默认排序方式 倒序
                            $ob = $orderby.' '.$orderway;//排序
                            $page= 1;
                            $pagesize= $param["pagesize"]??99999;
                            $notypeid= $param["notypeid"];
                            $param['typeid'] = $typeid;
                            $param['columnModel'] = $columnModel;
                            $rdata= (new TagList())->getList($param, $flag, $ob, $page, $pagesize, $notypeid);
                            if(sizeof($rdata) > 0){
                                $last_page = $rdata["last_page"];//最后一页
                                if($last_page >= 2){//第二页开始生成分页
                                    for ($i=2; $i <= $last_page; $i++){
                                        $cur_url = replaceSymbol("/{$lang_flag}/{$column_model}/index/{$id}");
                                        $url_page = replaceSymbol($this->rootUrl.url("{$cur_url}"))."?page={$i}";
                                        $relationurl_page = url("{$cur_url}")."?page={$i}";
                                        $url_page = replaceSymbol($url_page);
                                        $content_page = get_url_content($url_page);
                                        $content_page = $this->pc_to_mobile_js($content_page, $relationurl_page);//生成静态模式下且PC和移动端模板分离，就自动给PC端加上跳转移动端的JS代码
                                        $htmlfile_page = $htmlpath ."{$id}_{$i}". '.'.$this->view_suffix;
                                        @file_put_contents($htmlfile_page, $content_page);
                                    }
                                }
                            }
                        }
                    }
                }
            }else{//详情
                $cur_url = replaceSymbol("/{$lang_flag}/{$column_model}/detail/{$id}");
                $url = $this->rootUrl.url($cur_url);
                $relationUrl = url($cur_url);
            }
            $htmlfile = $htmlpath . $htmlfile . '.'.$this->view_suffix;
            $url = replaceSymbol($url);
            $content = get_url_content($url);
            $content = $this->pc_to_mobile_js($content, $relationUrl);//生成静态模式下且PC和移动端模板分离，就自动给PC端加上跳转移动端的JS代码
            @file_put_contents($htmlfile, $content);
        }elseif($flag=="index"){//首页
            $this->buildIndexHtml($htmlpath, $this->view_suffix);
        }
    }

// 生成首页html
private function buildIndexHtml($htmlpath) {
    // 初始化语言列表数组
    $langList = [];
    try {
        // 从数据库中获取启用的状态为1且匹配当前语言的记录，使用缓存
        $langList = Db::name('lang')->where('status', 1)->where('lang', $this->getMyLang())->cache(true)->select()->toArray();
    } catch (\Exception $e) {
        // 异常处理，此处未做具体处理
    }
    // 获取默认语言设置
    $home_lang = xn_cfg("base.home_lang");
    // 如果语言列表为空，则添加默认语言到列表中
    if (sizeof($langList) <= 0) {
        array_push($langList, ['lang' => $home_lang]);
    }

    // 遍历语言列表，为每种语言生成首页html
    foreach ($langList as $key => $item) {
        // 根据当前语言和默认语言确定语言后缀
        $curLang = "_" . $item['lang'];
        if ($home_lang == $item['lang']) {
            $curLang = "";
        }

        // 使用模板生成首页内容
        $templateUrl = $this->rootUrl . "/index";
        if (!empty($curLang)) {
            $templateUrl .= "?lang=" . $item['lang'];
        }

        // 获取模板内容并转换为移动端格式
        $content = get_url_content($templateUrl);
        $content = $this->pc_to_mobile_js($content, "/index");

        // 构建html文件路径并写入内容
        $htmlfile = $htmlpath . "index" . '.' . $this->view_suffix;
        $htmlfile = replaceSymbol($htmlfile);
        @file_put_contents($htmlfile, $content);

        // 如果是默认语言，则复制生成的html到根目录下
        if (empty($curLang)) {
            $targetFile = root_path() . "/index" . '.' . $this->view_suffix;
            copyFile($htmlfile, $targetFile);
        }
    }
}

    // 生成静态模式下且PC和移动端模板分离
    private function pc_to_mobile_js($html = '', $url)
    {
        if (preg_match('/(http:\/\/)|(https:\/\/)/i', $url)) {//判断是否存在
            $url = $url;
        }else{
            $domain = $this->rootUrl;//域名
            if(!empty($this->mobileUrl)){
                $domain = $this->mobileUrl;
            }
            $url = $domain.$url;
        }
        $url = replaceSymbol($url);
        if($this->templateType == 3){//模板类型 1、只有电脑端； 2、只有移动端； 3、有电脑端和移动端； 4、自适应
            $jsStr = <<<EOF
            <script>
                let os = function() {
                    let ua = navigator.userAgent,
                        isWindowsPhone = /(?:Windows Phone)/.test(ua),
                        isSymbian = /(?:SymbianOS)/.test(ua) || isWindowsPhone,
                        isAndroid = /(?:Android)/.test(ua),
                        isFireFox = /(?:Firefox)/.test(ua),
                        isChrome = /(?:Chrome|CriOS)/.test(ua),
                        isTablet = /(?:iPad|PlayBook)/.test(ua) || (isAndroid && !/(?:Mobile)/.test(ua)) || (isFireFox &&
                            /(?:Tablet)/.test(ua)),
                        isPhone = /(?:iPhone)/.test(ua) && !isTablet,
                        isPc = !isPhone && !isAndroid && !isSymbian;
                    return {
                        isTablet: isTablet,
                        isPhone: isPhone,
                        isAndroid: isAndroid,
                        isPc: isPc
                    };
                }();
                if (os.isAndroid || os.isPhone || os.isTablet) {
                    // 手机或者平板
                    window.location.href = "{$url}";
                }
		</script>
EOF;
        $html  = str_ireplace('</head>', $jsStr . "\n</head>", $html);
        return $html;
        } 
		else {
			 // 模板类型为 1、2 或 4，直接返回 $html，不做任何修改
			return $html;
		}
    }

    public function save(){
        $param = $this->request->param();
    
        // 仅保留允许的配置项
        $allowedKeys = ['url_model', 'column_page', 'document_page', 'pseudo_status'];
        $filteredParam = array_intersect_key($param, array_flip($allowedKeys));
    
        // 验证每个值是否为数字
        foreach ($filteredParam as $key => $val) {
            if (!ctype_digit((string)$val)) {
                unset($filteredParam[$key]); // 或设置默认值
            }
        }
        
        $this->_set($filteredParam);
        $this->_set($param);
        xn_add_admin_log('SEO保存配置', "seo");
        Cache::clear();
        $this->success("成功");
    }

    // 写入配置文件
    protected function _set($param, $filename = "seo") {
        // 允许的配置项白名单
        $allowedKeys = ['url_model', 'column_page', 'document_page', 'pseudo_status'];
        
        if (is_array($param) && !empty($param)) {
            $file = config_path() . $this->folder . "/" . $filename . '.php';
            $str = "<?php\r\nreturn [\r\n";
            
            foreach ($param as $key => $val) {
                // 仅处理白名单内的键
                if (!in_array($key, $allowedKeys)) {
                    continue;
                }
                
                // 验证值是否为纯数字字符串
                if (ctype_digit((string)$val)) {
                    $str .= "\t'$key' => '$val',\r\n";
                } else {
                    // 非法值设置为默认值0
                    $str .= "\t'$key' => '0',\r\n";
                    // 可选：记录日志（需引入日志类）
                    // log::error("非法配置值: $key => $val");
                }
            }
            $str .= '];';
            file_put_contents($file, $str);
        }
    }

    // 生成静态标签
    private function buildTagHtml($htmlpath = ''){
        $htmlpath = !empty($htmlpath) ? $htmlpath:(root_path()."/{$this->html_save_path}/");
        if(!tp_mkdir($htmlpath)){
            return "创建文件夹失败";
        }
        $view_suffix = config('view.view_suffix');
        //标签首页
        $template = $this->templateHtml . "index_tag.{$view_suffix}";
        if(file_exists($template)) {//存在的话额外生成
            $url = "{$this->rootUrl}/Tag/index";
            $relationUrl = "/Tag/index";
            $htmlfile = $htmlpath . 'index.' . $view_suffix;
            $url = replaceSymbol($url);
            $content = get_url_content($url);
            $content = $this->pc_to_mobile_js($content, $relationUrl);//生成静态模式下且PC和移动端模板分离，就自动给PC端加上跳转移动端的JS代码
            @file_put_contents($htmlfile, $content);
        }
        //标签列表
        $templateL = $this->templateHtml . "list_tag.{$view_suffix}";
        if(file_exists($templateL)) {//存在的话额外生成
            $tagList = \app\common\model\Tag::select();
            foreach ($tagList as $tag){
                $urlL = "{$this->rootUrl}/Tag/list/{$tag['id']}".".".$view_suffix;
                $relationUrlL = "/Tag/list/{$tag['id']}".".".$view_suffix;
                $htmlfileL = $htmlpath . $tag['id'] . '.'.$view_suffix;
                $urlL = replaceSymbol($urlL);
                $contentL = get_url_content($urlL);
                $contentL = $this->pc_to_mobile_js($contentL, $relationUrlL);//生成静态模式下且PC和移动端模板分离，就自动给PC端加上跳转移动端的JS代码
                @file_put_contents($htmlfileL, $contentL);
            }
        }
    }

    // 单独生成导航栏目
    public function singleAllSite(){
        //生成路由 //1:动态url,2:伪静态化,3:静态页面
        $param = $this->request->param();
        if(array_key_exists("first", $param)){//第一次请求
            //生成首页
            // $htmlpath = (root_path()."/");
            try{
                $this->buildHtml("", "column", "index",  "", "index");
            }catch (\Exception $e) {
                $this->error("创建静态首页文件失败". $e->getMessage());
            }

            $this->pageColumnList = [];
            $columnId = intval($param["columnId"]);
            $columnsUp = get_column_up($columnId, "", $this->getMyLang());
            $columnsDown = get_column_down($columnId, "", $this->getMyLang());
            $columns = array_merge($columnsUp, $columnsDown);
            $columns = my_array_unique($columns);
            if(sizeof($columns) > 0){
                $this->getColumns($columns);//重新组织数组
                $firstColumn = $this->pageColumnList[0];//第一个栏目
                $this->buildColumn($firstColumn);
                //缓存栏目
                saveToCache(Session::getId()."_pageColumnList", json_encode($this->pageColumnList));
                $this->success("继续创建静态文件", "", ['index'=>1, "detail_index"=>0, "curColumnIndex"=>0]);

            }else{
                $this->error("没有栏目数据");
            }
        }else{//继续
            $curColumnIndex = $param["curColumnIndex"];
            $index = $param["index"];
            if(empty($curColumnIndex) && $curColumnIndex != 0){
                //清除缓存栏目
                saveToCache(Session::getId()."_pageColumnList", null);
                $this->error("继续生产静态文件,参数失败！");
            }
            $curColumnIndex = intval($curColumnIndex);
            $index = intval($index);
            //清除缓存栏目
            $pageColumnListStr = saveToCache(Session::getId()."_pageColumnList");
            $this->pageColumnList = json_decode($pageColumnListStr);
            if($curColumnIndex >= sizeof($this->pageColumnList)){
                //清除缓存栏目
                saveToCache(Session::getId()."_pageColumnList", null);
                $this->success("创建静态完成");
            }
            if($index >= sizeof($this->pageColumnList)){
                //清除缓存栏目
                saveToCache(Session::getId()."_pageColumnList", null);
                $this->success("创建静态完成");
            }
            $curColumn = $this->pageColumnList[intval($index)];//当前栏目
            $curColumn = get_object_vars($curColumn);//对象转数组
            $this->buildColumn($curColumn);
            $this->success("继续生产静态栏目文件", "", ['index'=>($index+1), 'detail_index'=> 0, "curColumnIndex"=> $index ] );

        }
    }

    // 添加文章等数据后更新静态文件
    public function addDataBuildDetail(){
        $param = $this->request->param();
        if(empty($param["id"]) || empty($param["column_model"])){
            $this->error("缺少参数，生成详情失败");
        }
        $id = $param["id"];
        $column_model = $param['column_model'];//模型
        $modelData = Db::name($column_model)->find(intval($id));
        if(!$modelData){
            $this->error("没有查到数据");
        }
        $columnId = $modelData["column_id"];
        $column = \app\common\model\Column::find($columnId);
        if(!$column){
            $this->error("没有查到栏目数据");
        }
        $seoArr = xn_cfg("seo");
        //创建模型即详情
        $document_page = $seoArr["document_page"];//文档生成方式
        $columnList = get_column_up($columnId, "", $this->getMyLang());
        $htmlpathM = "";
        foreach ($columnList as $column2){
            $htmlpathM .= "/".$column2["dir_path"];
        }
        if($document_page == 1){
            $htmlpathD = $htmlpathM."/".foxDate("Ymd", $modelData["create_time"])."/";
        }else{
            $htmlpathD = $htmlpathM;
        }
        $htmlpathD = root_path()."/{$this->html_save_path}/".$htmlpathD."/";
        $htmlpathD = replaceSymbol(replaceSymbol($htmlpathD));
        $rInfo = $this->buildHtml($modelData["id"], $column_model, $modelData["id"], $htmlpathD, "detail");
        $this->success("详情页面生成成功: " . $rInfo);
    }
} 