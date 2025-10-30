<?php

namespace app\taglib\fox;
use think\facade\Db;

/**
 * 枚举字段所有值
 */
class TagDiyform
{

    /**
     * 查询数据
     */
    public function getList($param)
    {
        $tablename = $param["tablename"];//表名
        $field = $param["field"];//字段
        $way = $param['way'];//方式 1：独立；2：合并
        $showall = $param['showall'];//是否显示全部 1：显示；0：不显示
        $TABLE_SCHEMA = config("database.connections.mysql.database");
        $sql=<<<php
        SELECT
            column_type
        FROM
            information_schema.COLUMNS
        WHERE
            TABLE_SCHEMA = "{$TABLE_SCHEMA}"
            AND DATA_TYPE = 'enum'
            AND table_name="{$tablename}"
            AND column_name="{$field}";
php;
        $rdataArr = Db::query($sql);
        $valArr = [];
        if(sizeof($rdataArr) > 0){
            $rdata = $rdataArr[0];
            $column_type = $rdata['column_type'];
            $column_type = str_replace("enum(", "", $column_type);
            $column_type = str_replace(")", "", $column_type);
            $column_type = str_replace("'", "", $column_type);
            $valArr = explode(",", $column_type);
        }
        $view_suffix = config('view.view_suffix');
        $baseurl = request()->baseUrl();//基本路径
        if(str_ends_with($baseurl,".".$view_suffix)){
            $lastL = strripos($baseurl, ".".$view_suffix);
            $baseurl = substr($baseurl, 0, $lastL);
        }
        $params = request()->param();//参数
        $rdataList = [];
        $disstyle = $param['disstyle'];//当前选择样式
        $currFieldVal = $params[$field];
        $isCurr = false;
        foreach ($valArr as $val){
            $link = "javascript:void(0)";
            if($way == 2){//合并
                $params["{$field}"] = $val;
                $params["fields"] = $field;
                $link = tagSetUrl($baseurl, $params);
            }elseif ($way == 1){//独立
                $link = tagSetUrl($baseurl, ["id"=>$params["id"], "{$field}"=>$val, "fields"=>$field]);
            }
            $rdata = ['name'=>$val, 'link'=>$link];
            if($currFieldVal == $val){
                $rdata['disstyle'] = $disstyle;
                $isCurr = true;
            }else{
                $rdata['disstyle'] = "";
            }
            array_push($rdataList, $rdata);
        }

        if($showall == 1) {//显示
            $firstLink = "javascript:void(0)";
            if ($way == 2) {//合并
                $newParams = [];
                $params = request()->param();//参数
                $pvArr = array_values($params);
                $pkArr = array_keys($params);
                foreach ($pvArr as $k => $pv) {
                    $isDel = false;
                    foreach ($rdataList as $rd) {
                        if ($rd['name'] == $pv) {
                            $isDel = true;
                            break;
                        }
                    }
                    if (!$isDel) {
                        $pk = $pkArr[$k];
                        $newParams["{$pk}"] = $params[$pk];
                    }
                }
                $firstLink = tagSetUrl($baseurl, $newParams);
            } elseif ($way == 1) {//独立
                $firstLink = tagSetUrl($baseurl, ["id" => $params['id']]);
            }
            $firstData = ['name' => '全部', 'link' => $firstLink];
            if (!$isCurr) {
                $firstData['disstyle'] = $disstyle;
            }
            array_unshift($rdataList, $firstData);
        }
        return $rdataList;
    }

}