<?php

namespace app\taglib\fox;
use app\common\model\Column;
use think\facade\Db;

/**
 * 单页
 */
class TagSingle extends TagBase
{
    /**
     * 查询数据
     */
    public function getList($param)
    {

        $filed = $param['filed'];
        $typeid = $param['typeid'];
        $typeidP = $param['typeidP'];
        $calltype = $param["calltype"];//标签调用方式
        $filter = $param["filter"];//过滤函数
        $model = $param["model"];//模型
        $visit_lang = $this->getLang();//语言
        if(($calltype != "self") && empty($typeid) && !empty($typeidP)){
            $typeid = $typeidP;
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
                echo "single标签栏目标识不存在";
                die();
            }
        }

        if(empty($model)){
            $model = strtolower(request()->controller());
        }

        if(empty($typeid)){
            $id = \request()->param("id");
            $action = \request()->action();
            if($action == "detail"){//详情
                $article = Db::name($model)->field("column_id")->find($id);
                if(!$article){
                    return [];
                }
                $id = $article['column_id'];
            }
            $typeid = (String)$id;
        }

        if(empty($typeid)){
            echo "<div>标签没有栏目id</div>";
            return false;
        }

        $column = Column::field("column_model, v_path, id")->find($typeid);
        if(empty($column)){
            echo "<div>栏目不存在</div>";
            return false;
        }
        $single = Db::name($column['column_model'])->where("column_id", $typeid)->where("lang", $visit_lang)->limit(1)->find();
        if($single){
            if($filed == "link"){
                if($column){
                    echo resetUrl($column['v_path'], $column['id'], $visit_lang);
                    return false;
                }else{
                    echo "";
                    return false;
                }
            }
            if(!empty($filter)){//函数动态过滤

                $filterArr = explode(",", $filter);
                $method = $filterArr[0];
                $params = [$single[$filed]];
                foreach ($filterArr as $key=>$filter){
                    if($key > 0){
                        array_push($params, $filter);
                    }
                }
               $rval = call_user_func_array($method, $params);
                echo $rval;
                return false;
            }
            echo $single[$filed];
            return false;
        }else{
            echo '';
            return false;
        }

    }

}