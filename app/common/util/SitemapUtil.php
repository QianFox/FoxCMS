<?php

namespace app\common\util;

use app\common\model\Basic as BasicModel;
use app\common\model\Contact as ContactModel;
use app\common\model\Column;
use app\common\model\ModelRecord;
use think\facade\Db;
use think\facade\View;


/**
 * sitemap地图
 */
class SitemapUtil
{
    /**
     * 生成sitemap网站地图
     */
    public function generateSitemap($type, $domain, $lang=""){

        $sitemap = xn_cfg("sitemap");
        $filters = [];
        if(!empty($sitemap['filter'])){
            $filters = explode(",",$sitemap['filter']);
        }
        //更新频率
        $frequencys = [];
        if(!empty($sitemap['frequency'])){
            $frequencys = explode(",",$sitemap['frequency']);
        }
        //优先级别
        $levels = [];
        if(!empty($sitemap['level'])){
            $levels = explode(",",$sitemap['level']);
        }
        //url模式
        $url_model = xn_cfg("seo.url_model");

        $where = [];
        if(in_array("hide_column", $filters)){//隐藏栏目
            array_push($where, ['status', '=', 1]);
        }
        if(in_array("outer_model", $filters)){//过滤外部模块
            array_push($where, ['column_attr', '<>', 1]);
        }
        //查询模型
        $mrList = ModelRecord::field("table, nid")->where(['is_delete'=>0, 'status'=>1, "reference_model"=>0])->select();
        //查询栏目
        $curlang = xn_cfg("base.lang");
        if(empty($lang)){
            $lang = $curlang;
        }
        array_push($where, ['lang', '=', $lang]);
        $columns = Column::where($where)->order('level asc')->order('sort asc')->select();
        $view_suffix = config('view.view_suffix');
        if($url_model == 3){//静态化页面
            $indexUrl = $domain;
        }else{
            $indexUrl = $domain."index.{$view_suffix}";
        }
        if($type == "xml"){
            $this->xml_sitemap($columns, $indexUrl, $view_suffix, $mrList, $frequencys, $levels, $domain, $lang);
        }elseif ($type == "txt"){
            $this->txt_sitemap($columns, $mrList, $indexUrl, $domain, $lang);
        }elseif ($type == "html"){
            return $this->html_sitemap($columns, $mrList, $view_suffix, $domain, $lang);
        }elseif($type == "all"){//生成全部网站地图
            $this->txt_sitemap($columns, $mrList, $indexUrl, $domain, $lang);
            $this->xml_sitemap($columns, $indexUrl, $view_suffix, $mrList, $frequencys, $levels, $domain, $lang);
            $this->html_sitemap($columns, $mrList, $view_suffix, $domain, $lang);
        }
    }


    /**
     * 生成xml网站地图
     */
    private function xml_sitemap($columns, $indexUrl, $view_suffix, $mrList, $frequencys, $levels, $domain, $lang){
        $sitemap = new \app\common\util\SitemapXmlUtil($domain);
        $home_lang = xn_cfg("base.home_lang");//默认语言
        if($home_lang == $lang){
            $xmlFile = "sitemap";
        }else{
            $xmlFile = $lang."/sitemap";
        }
        $sitemap->setXmlFile($xmlFile);
        $sitemap->addItem($indexUrl, $levels[0], $frequencys[0], 'Today');
        foreach ($columns as $column){
            $column['visit_lang'] = $lang;
            $columnUrl = getColumnUrl($column);
            $columnUrl = $this->addPrefix($columnUrl, $domain);
            $columnUrl = replaceSymbol($columnUrl);
            $sitemap->addItem($columnUrl, $levels[1], $frequencys[1], strtotime($column['update_time']));
            $column_model = $column['column_model'];
            $is_find = false;
            foreach ($mrList as $mr){
                if($mr['nid'] == $column_model){
                    $is_find = true;
                    break;
                }
            }
            if($is_find){
                $column_id = $column["id"];
                if ($column["column_attr"] == 2){//内链栏目
                    $column_id = $column['inner_column'];
                }
                $dataList = Db::name($column_model)->where('column_id', $column_id)->select();//数据列表
                foreach ($dataList as $data){
                    $path = "/{$column_model}/detail";
                    $detailUrl = detailSetUrl($path, $data['id'], $data['column_id'], $column_model, $lang);
                    $detailUrl = $this->addPrefix($detailUrl, $domain);
                    $detailUrl = replaceSymbol($detailUrl);
                    $sitemap->addItem($detailUrl, $levels[2], $frequencys[2], strtotime($data['release_time']));
                }
            }
        }
        $sitemap->endSitemap();
    }


    /**
     * 生成txt网站地图
     */
    private function txt_sitemap($columns, $mrList, $indexUrl, $domain, $lang=""){
        $datas = [];
        array_push($datas, $indexUrl);//首页
        foreach ($columns as $column){
            $column['visit_lang'] = $lang;
            $columnUrl = getColumnUrl($column);
            $columnUrl = $this->addPrefix($columnUrl, $domain);
            $columnUrl = replaceSymbol($columnUrl);
            array_push($datas, $columnUrl);
            $column_model = $column['column_model'];
            $is_find = false;
            foreach ($mrList as $mr){
                if($mr['nid'] == $column_model){
                    $is_find = true;
                    break;
                }
            }
            if($is_find){
                $column_id = $column["id"];
                if ($column["column_attr"] == 2){//内链栏目
                    $column_id = $column['inner_column'];
                }
                $dataList = Db::name($column_model)->where('column_id', $column_id)->select();//数据列表
                foreach ($dataList as $data){
                    $path = "/{$column_model}/detail";
                    $detailUrl = detailSetUrl($path, $data['id'], $data['column_id'], $column_model, $lang);
                    $detailUrl = $this->addPrefix($detailUrl, $domain);
                    $detailUrl = replaceSymbol($detailUrl);
                    array_push($datas, $detailUrl);
                }
            }
        }
        $home_lang = xn_cfg("base.home_lang");//默认语言
        if($home_lang == $lang){
            $filename = "sitemap.txt";
        }else{
            $filename = $lang."/sitemap.txt";
        }
        $data = implode("\r\n", $datas);
        @file_put_contents($filename, $data);
    }


    /**
     * 生成html网站地图
     */
    private function html_sitemap($columns, $mrList, $view_suffix, $domain, $lang=""){
        $home_lang = xn_cfg("base.home_lang");//默认语言
        if($home_lang == $lang){
            $filename = "sitemap.{$view_suffix}";
        }else{
            $filename = $lang."/sitemap.{$view_suffix}";
        }
        $sitemapUrl = root_path()."/app/home/view/sitemap.{$view_suffix}";
        //网站基本信息
        $basic = [];
        $basicArr = BasicModel::where(['lang'=>$lang])->select()->toArray();
        $basicN = [];//基本属性返回
        if(sizeof($basicArr) > 0) {
            $basic = $basicArr[0];
            $basicN['keyword']= $basic['keyword'];
            $basicN['description']= $basic['description'];
        }
        $contactArr = ContactModel::where([['lang', '=', $lang]])->field('company_tel')->select()->toArray();
        $contact = [];
        if(sizeof($contactArr) > 0) {
            $contact = $contactArr[0];
        }

        $basicN['logo']= $basic['web_logo']??"/static/404/images/foxcms_logo.svg";
        //查询栏目数据
        $navs = [];//顶层栏目
        foreach ($columns as $column){
            $column['visit_lang'] = $lang;
            if($column['pid'] == 0){
                $columnUrl = getColumnUrl($column);
                $columnUrl = $this->addPrefix($columnUrl, $domain);
                $column['link'] = replaceSymbol($columnUrl);
                array_push($navs, $column);
            }
        }

        $columnList = $this->channelLevel($domain, $columns);//排序好栏目
        //栏目列表
        foreach ($columns as $key=>$column){
            $column_model = $column['column_model'];
            $is_find = false;
            foreach ($mrList as $mr){
                if($mr['nid'] == $column_model){
                    $is_find = true;
                    break;
                }
            }
            if($is_find){
                $column_id = $column["id"];
                if ($column["column_attr"] == 2){//内链栏目
                    $column_id = $column['inner_column'];
                }
                $dataList = Db::name($column_model)->where('column_id', $column_id)->where('lang', $lang)->select();//数据列表
                foreach ($dataList as $kkey=>$data){
                    $path = "/{$column_model}/detail";
                    $detailUrl = detailSetUrl($path, $data['id'], $data['column_id'], $column_model, $lang);
                    $detailUrl = $this->addPrefix($detailUrl, $domain);
                    $detailUrl = replaceSymbol($detailUrl);
                    $data['link'] = $detailUrl;
                    $dataList[$kkey] = $data;
                }
                $column['data_list'] = $dataList;
                $columns[$key] = $column;
            }
        }
        $columnArticleList = $this->channelLevel($domain,$columns);//栏目文章数据列表
        $caList = [];//返回栏目数据列表
        foreach ($columnArticleList as $ca){
            $this->cycleColumn($ca, $caList);
        }
        $content = View::fetch($sitemapUrl);
        $content = str_replace("__basic.logo__", $basicN['logo'], $content);
        $nav_content = "";
        foreach ($navs as $k=>$vo){
            $nav_content .= '<li>
              <a class="foxui-link" href="'.$vo['link'].'">'.$vo['name'].'</a>
            </li>';
        }
        $content = str_replace("__nav.content__", $nav_content, $content);
        $column_content = "";
        foreach ($columnList as $key=>$vo){
            $column_content .= ' <div class="foxui-col-md-4 foxui-col-sm-8 foxui-col-xs-12">
                                <dl>
                                    <dt>
                                        <a class="foxui-ellipsis" href="'.$vo['link'].'">'.$vo['name'].'</a>
                                    </dt>';
            $column_content_child = "";
            if (sizeof($vo['children']) > 0){
                foreach ($vo['children'] as $key=>$voo){
                    $column_content_child .='  <dd>
                                        <a class="foxui-link foxui-link-primary foxui-ellipsis" href="'.$voo['link'].'">'.$voo['name'].'</a>
                                    </dd>';
                }
            }
            $column_content .= $column_content_child;
            $column_content .= '</dl>
                                </div>';
        }
        $content = str_replace(" __column.content__", $column_content, $content);

        $ca_contet= "";
        foreach ($caList as $key=>$vo){
            if($vo['data_list'] && sizeof($vo['data_list']) > 0){

                $ca_contet .='<h5 class="foxui-margin-bottom-16">'.$vo['name'].'</h5>
                <div class="foxui-row foxui-gutter-y-6 foxui-gutter-x-xxxl-12 foxui-gutter-x-xxl-12 foxui-gutter-x-xl-12">';
                $ca_contet_child = "";
                foreach ($vo['data_list'] as $do) {

                    $ca_contet_child .='<div class="foxui-col-md-8 foxui-col-sm-12 foxui-col-xs-12">
                            <div class="item">';
                    $ca_contet_child .='<a class="foxui-link foxui-link-primary foxui-ellipsis" href = "'.($do['link']??"javascript:void(0)").'">';
                    $ca_contet_child .= $do['title']??"";
                    $ca_contet_child .='</a>';
                    $ca_contet_child .='</div>
                        </div>';
                }
                $ca_contet .= $ca_contet_child;
                $ca_contet .='</div>';
            }
        }
        $content = str_replace(" __ca.content__", $ca_contet, $content);

        $content = str_replace("__contact.company_tel__", $contact['company_tel'], $content);

        @file_put_contents($filename, $content);
    }


    /**
     * 添加前缀
     */
    protected function addPrefix($value, $domain = ""){
        if(empty($value)){
            return "";
        }
        if (preg_match('/(http:\/\/)|(https:\/\/)/i', $value)) {//判断是否存在
            return $value;
        }else{
            if(str_starts_with($value, "/")){
                $value = substr($value, 1, strlen($value));
            }
            $value = $domain.$value;
            return $value;
        }
    }


    /**
     * 解析子栏目
     */
    private function cycleColumn($ca, &$caList){
        array_push($caList, $ca);
        if(sizeof($ca['children']) > 0){
            foreach ($ca['children'] as $k=>$cca){
                $this->cycleColumn($cca, $caList);
            }
        }
        return $caList;
    }


    /**
     * 返回多层栏目
     * @param $dataArr 操作的数组
     * @param int $pid 一级PID的值
     * @param string $fieldPri 唯一键名，如果是表则是表的主键
     * @param string $fieldPid 父ID键名
     * @param int $level 不需要传参数（执行时调用）
     */
    private function channelLevel($domain, $dataArr, $pid = 0, $fieldPri = 'id', $fieldPid = 'pid', $level = 1)
    {
        if (empty($dataArr)) {
            return array();
        }
        $rArr = [];
        foreach ($dataArr as $v) {
            if ($v[$fieldPid] == $pid) {
                $arr = array();
                $arr['id'] = $v->id;
                $arr['pid'] = $v->pid;
                $arr['name'] = $v->name;
                $arr['status'] = $v->status;
                $arr['sort'] = $v->sort;
                $arr['data_list'] = $v->data_list;
                $arr['column_model'] = $v->column_model;
                $arr['level'] = $level;
                $columnUrl = getColumnUrl($v);
                $columnUrl = $this->addPrefix($columnUrl, $domain);
                $columnUrl = replaceSymbol($columnUrl);
                $arr['link'] = $columnUrl;
                $arr['children'] = self::channelLevel($domain, $dataArr, $v[$fieldPri], $fieldPri, $fieldPid, $level + 1);
                array_push($rArr, $arr);
            }
        }
        return $rArr;
    }
}