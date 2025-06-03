<?php

namespace app\taglib\fox;
use app\common\model\Column;
use app\common\model\ModelRecord;
use think\facade\Db;

/**
 * 类似文章列表
 */
class TagIsdata extends TagBase
{

    /**
     * 查询数据
     */
    public function getList($param, $flag="", $ob="create_time desc", $offset=0,  $row=10)
    {
        $visit_lang = $this->getLang();//语言
        $limit = $param["limit"];
        $columnModel = $param["columnModel"];
        $typeid = $param["typeid"];//栏目id
        $typeidP = $param["typeidP"];//父栏目id
        $calltype = $param["calltype"];//标签调用方式
        $type = $param["type"];//栏目id控制类型

        if(($calltype != "self") && empty($typeid) && !empty($typeidP)){//父栏目id
            $typeid = (String)$typeidP;
        }

        $sid = $param["sid"];//栏目标识
        if(empty($typeid) && !empty($sid)){
            $fColumns = Column::field("id")->whereIn("nid",$sid)->where(['lang'=>$visit_lang])->select();
            if(sizeof($fColumns)>0){
                $columnIds = [];
                foreach($fColumns as $fColumn){
                    array_push($columnIds,$fColumn['id']);
                }
                $typeid = implode($columnIds, ",");
            }else{
                echo "download标签栏目标识不存在";
                die();
            }
        }

        $typeidList = [];//栏目id集合
        $mmList = [];//模型数组
        $curTypeid = $typeid;

        if(empty($typeid)){
            $id = \request()->param("id");
            $action = \request()->action();
            if($action == "detail") {//详情
                if(!empty($id)){
                    if(empty($columnModel)){
                        $controller = request()->controller();
                        $currColumnModel = $controller;
                    }else{
                        $currColumnModel = $columnModel;
                    }
                    $article = Db::name($currColumnModel)->field('column_id')->find($id);
                    if($article){
                        $curTypeid = $article['column_id'];
                    }
                }
            }else{
                $curTypeid = $id;
            }
        }
        if(!empty($curTypeid)){
            if(empty($type)) {//不传就以后台栏目设置为准
                $columns = Column::field("id,limit_column,data_limit,column_model")->whereIn('id', $curTypeid)->select();
                foreach ($columns as $column) {
                    if ($column["data_limit"] == 1) {//仅本栏目
                        array_push($typeidList, $column['id']);
                    } elseif ($column["data_limit"] == 2) {//本栏目及下级栏目
                        $typeidList = array_merge($typeidList, explode(",", $column["limit_column"]));
                    } elseif ($column["data_limit"] == 3) {//本栏目及指定子栏目
                        $typeidList = array_merge($typeidList, explode(",", $column["limit_column"]));
                    }
                }
            }else{//传值由传入参数控制
                $idArr = explode(",", $typeid);
                if($type == "top"){//向上查询栏目数据和本身栏目
                    foreach ($idArr as $id){
                        $rColumns = get_column_up($id, "");
                        foreach ($rColumns as $rc){
                            array_push($typeidList, $rc['id']);
                        }
                    }
                }elseif ($type == "self"){//查自己栏目数据
                    foreach ($idArr as $id){
                        array_push($typeidList, $id);
                    }
                }elseif ($type == "son"){//查子栏目和自己栏目数据
                    foreach ($idArr as $id){
                        $rColumns = get_column_down($id, "");
                        foreach ($rColumns as $rc){
                            array_push($typeidList, $rc['id']);
                        }
                    }
                }elseif($type == "all"){//查自己和子栏目和父栏目
                    foreach ($idArr as $id){
                        $rColumnsDown = get_column_down($id, "");
                        $rColumnsUp = get_column_up($id, "");
                        foreach ($rColumnsDown as $rc){
                            array_push($typeidList, $rc['id']);
                        }
                        foreach ($rColumnsUp as $rc){
                            array_push($typeidList, $rc['id']);
                        }
                    }
                }
            }
        }
        $curColumnModel = \request()->param("model");
        if(!empty($columnModel)) {//有传入模型
            $curColumnModel = $columnModel;
        }
        $isAllMr = false;//是否查的是全部模型
        if(empty($curColumnModel)){
            if(sizeof($typeidList) > 0){//有栏目id就先过滤模型
                $columnList = Column::field("column_model")->whereIn('id', implode(",", $typeidList))->select();
                foreach($columnList as $c){
                    array_push($mmList, ['nid'=>$c['column_model']]);
                }
            }else{//没有时
                //查询所有模型 类似文章模型
                $mmList = ModelRecord::field("table, nid")->where(['is_delete'=>0, 'status'=>1, "reference_model"=>0])->select();
                $isAllMr = true;
            }
        }else{
            $columnModelArr = explode(",", $curColumnModel);
            foreach ($columnModelArr as $cm){
                array_push($mmList, ['nid'=>$cm]);
            }
        }
        //过滤掉不是类似文章模型的
        if(!$isAllMr){
            $mrList = ModelRecord::field("table, nid")->where(['is_delete'=>0, 'status'=>1, "reference_model"=>0])->select();
            $curMMList = [];
            foreach ($mmList as $mm){
                foreach ($mrList as $mr){
                    if($mm['nid'] == $mr['nid']){
                        array_push($curMMList, $mr);
                        break;
                    }
                }
            }
            $mmList = $curMMList;
        }
        if(sizeof($mmList) <= 0){//没得模型列表直接返回
            return ["current_page"=>0, "last_page"=>0, "per_page"=>0, "total"=>0, "datas"=>[]];
        }
        $mmfArr = array_unique(xn_cfg("list"));
        $commonFiled = implode(",", $mmfArr);
        $sqlArr = [];
        foreach ($mmList as $key=>$mmf){
            if($key > 0){
                $commonFiledSMore = $commonFiled.','."'{$mmf['nid']}' as model";
                array_push($sqlArr, "SELECT {$commonFiledSMore} FROM {$mmf['table']}");
            }
        }
        $commonFiledFirst = $commonFiled.','."'{$mmList[0]['nid']}' as model";
        $tableName = $mmList[0]['table'];
        $dataSql = Db::table($tableName)->field($commonFiledFirst)->union($sqlArr)->buildSql();
        $sql = "({$dataSql})";
        $where = $this->getSearch(request());//增加搜索条件
        $query = Db::table($sql." as a")->where($where);
        if(sizeof($typeidList) > 0){
            $query->whereIn("column_id", implode(",", $typeidList));
        }
        if(!empty($flag) && $flag != ""){
            $query->whereFindInSet('article_field', $flag);
        }
        if(!empty($limit)){
            $limitArr = explode(",", $limit);
            if(sizeof($limitArr) == 1){
                $offset = $limitArr[0];
                $row = $query->count();
            }elseif (sizeof($limitArr) == 2){
                $offset = $limitArr[0];
                $row = $limitArr[1];
            }
        }
        $rlist = $query->where('lang', $visit_lang)->order($ob)->limit($offset, $row)->select();
        return $rlist;
    }

}