<?php

namespace app\taglib\fox;
use app\common\model\Column;
use app\common\model\ModelRecord;
use app\common\model\UploadFiles;
use think\facade\Db;

/**
 * 类似文章列表
 */
class TagArclist extends TagBase
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
        $currentstyle = $param["currentstyle"];
        if(($calltype != "self") && empty($typeid) && !empty($typeidP)){
            $typeid = (String)$typeidP;
        }
        $sid = $param["sid"];//栏目标识
        if(empty($typeid) && !empty($sid)){
            $fColumns = Column::field("column_model,id")->whereIn("nid",$sid)->where(['lang'=>$visit_lang])->select();
            if(sizeof($fColumns)>0){
                $columnIds = [];
                foreach($fColumns as $fColumn){
                    array_push($columnIds,$fColumn['id']);
                }
                $typeid = implode($columnIds, ",");
//                $columnModel = $fColumn['column_model'];
            }else{
                echo "arclist标签栏目标识不存在";
                die();
            }
        }


        if(empty($typeid) && empty($columnModel)){
            $action = \request()->action();
            if($action == "detail"){
                if(empty($id)){
                    $id = \request()->param("id");
                }
                if(empty($columnModel)){
                    $columnModel = strtolower(request()->controller());
                }
                $article = Db::name($columnModel)->field('column_id')->find($id);
                if($article){
                    $typeid = $article['column_id'];
                }
            }else{
                if(empty($typeid)){
                    $typeid = \request()->param("id");
                }
            }
        }

        $type = $param["type"];
        $where = $this->getSearch(request());//增加搜索条件
        $apply = $param['apply'];//标签所应用的场景
        if($apply == "nav"){//标签应用于导航
            $where = [];//清除过滤条件
        }
        $columnModel = trim($columnModel);
        //查询所有模型 类似文章模型
        $mmList = [];
        if(empty($columnModel)){
            $mmList = ModelRecord::field("table, nid")->where(['is_delete'=>0, 'status'=>1, "reference_model"=>0])->select();
        }else{//查询指定模型
            $columnModelArr = explode(",", $columnModel);
            foreach ($columnModelArr as $cm){
                array_push($mmList, ['nid'=>$cm]);
            }
            //过滤掉不是类似文章模型的
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
            return [];
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
        $query = Db::table($sql." as a")->where($where);
        $query->where("lang", $visit_lang);

        if(empty($type)){//不传就以后台栏目设置为准
            $columnList = Column::whereIn("id", $typeid)->select();
            foreach ($columnList as $k=>$column){
                if($column["data_limit"] == 1){//仅本栏目
                    $query->where("column_id", $typeid);
                }elseif ($column["data_limit"] == 2){//本栏目及下级栏目
                    $query->whereIn("column_id", $column["limit_column"]);
                }elseif ($column["data_limit"] == 3){//本栏目及指定子栏目
                    $query->whereIn("column_id", $column["limit_column"]);
                }
            }
        }else{
            $idArr = explode(",", $typeid);
            if($type == "top"){//向上查询栏目数据和本身栏目
                $columnIds = [];
                foreach ($idArr as $id){
                    $rColumns = get_column_up($id, $columnModel);
                    foreach ($rColumns as $rc){
                        array_push($columnIds, $rc['id']);
                    }
                }
                if(sizeof($columnIds) <= 0){
                    return [];
                }
                $query->whereIn("column_id", implode(",", $columnIds));
            }elseif ($type == "self"){//查自己栏目数据
                $query->whereIn("column_id", $typeid);
            }elseif ($type == "son"){//查子栏目和自己栏目数据
                $columnIds = [];
                foreach ($idArr as $id){
                    $rColumns = get_column_down($id, $columnModel);
                    foreach ($rColumns as $rc){
                        array_push($columnIds, $rc['id']);
                    }
                }
                if(sizeof($columnIds) <= 0){
                    return [];
                }
                $query->whereIn("column_id", implode(",", $columnIds));
            }elseif($type == "all"){//查自己和子栏目
                $columnIds = [];
                foreach ($idArr as $id){
                    $rColumnsDown = get_column_down($id, $columnModel);
                    $rColumnsUp = get_column_up($id, $columnModel);
                    foreach ($rColumnsDown as $rc){
                        array_push($columnIds, $rc['id']);
                    }
                    foreach ($rColumnsUp as $rc){
                        array_push($columnIds, $rc['id']);
                    }
                }
                if(sizeof($columnIds) <= 0){
                    return [];
                }
                $query->whereIn("column_id", implode(",", $columnIds));
            }
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

        $notypeid = $param['notypeid'];
        if(!empty($notypeid)){
            if($notypeid == "self"){
                $notypeid = \request()->param("id");
            }
            $query->whereNotIn("id", $notypeid);
        }

        $addfields = trim($param["addfields"]);//增加字段
        $channel = trim($param["channel"]);//增加字段模型

        $cruId = \request()->param("id");
        $rlist = $query->order($ob)->limit($offset, $row)->select()->each(function($item, $key)use ($currentstyle, $cruId, $addfields, $channel, $visit_lang){
            if(!empty($item['breviary_pic_id'])){
                $item["img_url"] = UploadFiles::field('url')->find($item['breviary_pic_id'])['url'];
            }
            if(empty($item['img_url'])){
                $item['img_url'] = "/static/images/noimage.gif";
            }
            if(!empty($currentstyle)){
                if($item['id'] != $cruId){
                    $currentstyle = "";
                }
                $item['currentstyle'] = $currentstyle;
            }
            if(!empty($addfields) || !empty($channel)){
                $iColumn = Column::field("column_model")->find($item['column_id']);
                if($iColumn){
                    $column_model = $iColumn['column_model'];
                    if(!empty($channel)){
                       $modelChannelArr = explode(",", $channel);
                       if(!in_array($column_model,$modelChannelArr)){
                           $column_model = "";
                       }
                    }
                    if(!empty($column_model)){
                       $curItem = Db::name($column_model)->find($item['id']);
                       if(!empty($addfields)){
                           $addfieldArr = explode(",", $addfields);
                           foreach ($addfieldArr as $addfield){
                               $item[$addfield] = $curItem[$addfield]??"";
                           }
                       }else{
                           $item = array_merge($item, $curItem);
                       }
                    }else{
                        $addfieldArr = explode(",", $addfields);
                        foreach ($addfieldArr as $addfield){
                            $item[$addfield] = $curItem[$addfield]??"";
                        }
                    }
                }
            }
            $item['visit_lang'] = $visit_lang;
            return $item;
        });

        return $rlist;
    }

}