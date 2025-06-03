<?php

namespace app\taglib\fox;
use app\common\model\Article;
use app\common\model\Column;

/**
 * 文章列表
 */
class TagArticle extends TagBase
{


    /**
     * 查询文章数据
     */
    public function getList($param, $flag="", $ob="create_time desc", $offset=0,  $row=10)
    {

        $visit_lang = $this->getLang();//语言
        $where = $this->getSearch(request());//增加搜索条件
        $query = Article::where($where);
        $query->where("lang", $visit_lang);

        $id = $param["typeid"];//栏目id
        $typeidP = $param["typeidP"];//父栏目id
        $calltype = $param["calltype"];//标签调用方式

        if(($calltype != "self") && empty($id) && !empty($typeidP)){
            $id = (String)$typeidP;
        }
        $sid = $param["sid"];//栏目标识
        if(empty($id) && !empty($sid)){
            $fColumn = Column::field("id")->where(['nid'=>$sid, 'lang'=>$visit_lang])->find();
            if($fColumn){
                $id = $fColumn['id'];
            }else{
                echo "article标签栏目标识不存在";
                die();
            }
        }

        $limit = $param["limit"];
        $type = $param["type"];
        if(empty($id)){
            $id = \request()->param("id");
            $action = \request()->action();
            if($action == "detail"){//详情
                $article = Article::field("column_id")->find($id);
                if(!$article){
                    return [];
                }
                $id = (String)$article['column_id'];
            }
            $column = Column::field("limit_column,data_limit,column_model")->find($id);//查询当前栏目内容展示
            if(!$column){
                return [];
            }
            if($column["data_limit"] == 1){//仅本栏目
                $query->where("column_id", $id);
            }elseif ($column["data_limit"] == 2){//本栏目及下级栏目
                $query->whereIn("column_id", $column["limit_column"]);
            }elseif ($column["data_limit"] == 3){//本栏目及指定子栏目
                $query->whereIn("column_id", $column["limit_column"]);
            }
        }else{
            $column_model = "article";
            $idArr = explode(",", $id);
            if($type == "top"){//向上查询栏目数据和本身栏目
                $columnIds = [];
                foreach ($idArr as $id){
                    $rColumns = get_column_up($id, $column_model, 0);
                    foreach ($rColumns as $rc){
                        array_push($columnIds, $rc['id']);
                    }
                }
                if(sizeof($columnIds) <= 0){
                    return [];
                }
                $query->whereIn("column_id", implode(",", $columnIds));
            }elseif ($type == "self"){//查自己栏目数据
                $query->whereIn("column_id", $id);
            }elseif ($type == "son"){//查子栏目和自己栏目数据
                $columnIds = [];
                foreach ($idArr as $id){
                    $rColumns = get_column_down($id, $column_model);
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
                    $rColumnsDown = get_column_down($id, $column_model);
                    $rColumnsUp = get_column_up($id, $column_model);
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

        $notypeid = $param["notypeid"];
        if(!empty($notypeid)){
            if($notypeid == "self"){
                $notypeid = \request()->param("id");
            }
            $query->whereNotIn("id", $notypeid);
        }
        return $query->order($ob)->limit($offset, $row)->select()->each(function ($item) use ($visit_lang){
            $item['visit_lang'] = $visit_lang;
            return $item;
        });
    }

}