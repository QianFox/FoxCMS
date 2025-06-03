<?php

namespace app\taglib\fox;
use think\facade\Db;

/**
 * 拆分字段标签
 */
class TagSplit
{

    /**
     * 查询数据
     */
    public function getList($param)
    {
        $type = $param['type'];//字段类型 默认varchar  enum
        $tablename = $param["tablename"];//表名
        $field = $param["field"];//字段
        $TABLE_SCHEMA = config("database.connections.mysql.database");
        $rdataList = [];
        if($type == 'enum'){//枚举
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
            $rdataList = [];
            if(sizeof($rdataArr) > 0){
                $rdata = $rdataArr[0];
                $column_type = $rdata['column_type'];
                $column_type = str_replace("enum(", "", $column_type);
                $column_type = str_replace(")", "", $column_type);
                $column_type = str_replace("'", "", $column_type);
                $dataArr = explode(",", $column_type);
                foreach ($dataArr as $val){
                    array_push($rdataList, ["name"=>$val]);
                }
            }
        }elseif ($type == 'varchar'){
            $id = $param["id"];//对应字段值
            if(empty($id)){
                $id = request()->param('id');//id值
            }
            if(empty($id)){
                $id = $_REQUEST['id'];
            }
            if(empty($id)){
                echo '标签split报错：id值为空';
                return false;
            }
            $item = Db::table($tablename)->field("${field}")->find($id);
            if($item){
                $dataArr = explode(",", $item["${field}"]);
                foreach ($dataArr as $val){
                    array_push($rdataList, ["name"=>$val]);
                }
            }
        }
        return $rdataList;
    }

}