<?php

namespace app\taglib\fox;
use think\facade\Db;

/**
 * 标签组所有值
 */
class TagTaggrounp extends TagBase
{
    /**
     * 查询数据
     */
    public function getList($param)
    {
        $visit_lang = $this->getLang();//语言
        $pid = $param['pid'];//父标签组id
        $row = $param['row'];//调用条数
        $way = $param['way'];//方式 1：独立；2：合并
        $showall = $param['showall'];//是否显示全部 1：显示；0：不显示
        $addParam = $param['param'];
        $target = $param['target'];

        if($row == -1){
            $row = null;
        }
        if($pid == -1){//查全部标签
            $tagList = Db::name('tag')->page(1, intval($row))->select();
        }else{
            $tagList = Db::name('tag')->where(['tag_group_id'=>$pid])->page(1, intval($row))->select();
        }
        if(sizeof($tagList) <= 0){
            return [];
        }
        $rdataList = [];
        $params = request()->param();//参数
        $currentstyle = $param['currentstyle'];//当前选择样式
        $action = \request()->action();
        $currFieldVal = $params['tags'];
        $fields = $params['fields'];
        $fieldsArr = [];
        if(!empty($fields)){
            $currFieldsArr = explode(",", $fields);
            $fieldsArr =  array_merge($fieldsArr, $currFieldsArr);
        }

        if($target == "blank"){//tag跳转
            $baseurl = "/Tags/index";
        }elseif ($target == "self"){//本页显示
            $view_suffix = config('view.view_suffix');
            $baseurl = request()->baseUrl();//基本路径
            if(str_ends_with($baseurl,".".$view_suffix)){
                $lastL = strripos($baseurl, ".".$view_suffix);
                $baseurl = substr($baseurl, 0, $lastL);
            }
        }else{
            $baseurl = "/Tags/index";
        }

        $isCurr = false;
        foreach ($tagList as $k=>$tag){
            $link = "javascript:void(0)";
            if($action == "detail") {
                $url = "/Tags/index";
                array_push($fieldsArr, 'tags');
                $fieldsArr = array_unique($fieldsArr);
                $tagsStr = implode(",", $fieldsArr);
                $param = ['id'=>$tag['id'], 'fields'=>$tagsStr, 'tags'=>$tag['name']];
                if(!empty($addParam)){
                    $param['param'] = $addParam;
                }
                $link = tagSetUrl($url, $param);
            }else{
                array_push($fieldsArr, 'tags');
                $fieldsArr = array_unique($fieldsArr);
                $tagsStr = implode(",", $fieldsArr);
                if($way == 2){//合并
                    $params['fields'] = $tagsStr;
                    $params['tags'] = $tag['name'];
                    if(!empty($addParam)){
                        $params['param'] = $addParam;
                    }
                    $link = tagSetUrl($baseurl, $params);
                }elseif($way == 1){//独立
                    $dparam = ["id"=>$params["id"], "tags"=>$tag['name'], "fields"=>$tagsStr];
                    if(!empty($addParam)){
                        $dparam['param'] = $addParam;
                    }
                    $link = tagSetUrl($baseurl, $dparam);
                }
            }
            $rdata = ['link'=>$link, 'name'=>$tag['name'], 'target'=>' target="_blank" '];
            if($currFieldVal == $tag['name']){
                $rdata['currentstyle'] = $currentstyle;
                $isCurr = true;
            }else{
                $rdata['currentstyle'] = "";
            }
            $isExist = false;
            foreach ($rdataList as $rd){
                if($rdata['name'] == $rd['name']){
                    $isExist = true;
                    break;
                }
            }
            if(!$isExist){
                array_push($rdataList, $rdata);
            }
        }
        array_unique($rdataList);
        if($showall == 1){//显示
            $firstLink = "javascript:void(0)";
            if($way == 2){//合并
                $params = request()->param();//参数
                $pvArr = array_values($params);
                $pkArr = array_keys($params);
                $newParams = [];
                foreach ($pvArr as $k=>$pv){
                    $isDel = false;
                    foreach ($rdataList as $rd){
                        if($rd['name']  == $pv){
                            $isDel = true;
                            break;
                        }
                    }
                    if(!$isDel){
                        $pk = $pkArr[$k];
                        $newParams["{$pk}"] = $params[$pk];
                    }
                }
                if(!empty($addParam)){
                    $newParams['param'] = $addParam;
                }
                $firstLink = tagSetUrl($baseurl, $newParams);
            }elseif ($way == 1){//独立
                $nParam = ["id"=>$params['id']];
                if(!empty($addParam)){
                    $nParam['param'] = $addParam;
                }
                $firstLink = tagSetUrl($baseurl, $nParam);
            }
            $all_text = getLangContentByMark($visit_lang, "FOX_ALL");

            if (!$all_text) {
                $all_text = "全部";
            }

            $firstData = ['name'=>$all_text, 'link'=>$firstLink];
            if(!$isCurr){
                $firstData['currentstyle'] = $currentstyle;
            }
            array_unshift($rdataList, $firstData);
        }
        return $rdataList;
    }
}