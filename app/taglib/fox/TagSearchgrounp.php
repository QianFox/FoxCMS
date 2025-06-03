<?php

namespace app\taglib\fox;
use think\facade\Db;

/**
 * 搜索组所有值
 */
class TagSearchgrounp
{
    /**
     * 查询数据
     */
    public function getList($param, $ob="create_time desc")
    {
        $pid = $param['pid'];//父标签组id
        $row = $param['row'];//调用条数
        $way = $param['way'];//方式 1：独立；2：合并
        $showall = $param['showall'];//是否显示全部 1：显示；0：不显示
        $addParam = $param['param'];
        if($row == -1){
            $row = null;
        }
        $searchList = [];
        if($pid == -1){//查全部标签
            $searchList = Db::name('search')->page(1, intval($row))->order($ob)->select();
        }else{
            $searchList = Db::name('search')->where(['search_group_id'=>$pid])->order($ob)->page(1, intval($row))->select();
        }
        if(sizeof($searchList) <= 0){
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

        $view_suffix = config('view.view_suffix');
        $baseurl = request()->baseUrl();//基本路径
        if(str_ends_with($baseurl,".".$view_suffix)){
            $lastL = strripos($baseurl, ".".$view_suffix);
            $baseurl = substr($baseurl, 0, $lastL);
        }

        $isCurr = false;
        foreach ($searchList as $k=>$tag){
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
            $firstData = ['name'=>'全部', 'link'=>$firstLink];
            if(!$isCurr){
                $firstData['currentstyle'] = $currentstyle;
            }
            array_unshift($rdataList, $firstData);
        }
        return $rdataList;
    }
}