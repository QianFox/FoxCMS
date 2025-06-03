<?php

namespace app\taglib\fox;
use app\common\model\Column;
use think\facade\Db;

/**
 * 栏目列表
 */
class TagUpward extends TagBase
{

    /**
     * 查询栏目数据
     */
    public function getList($param, $type, $ob="create_time desc", $offset=0,  $row=10)
    {
        $visit_lang = $this->getLang();//语言
        $query = Column::where(['lang'=>$visit_lang]);
        $id = $param["typeid"];//栏目id
        $limit = $param["limit"];//栏目id
        $sid = $param["sid"];//栏目标识
        $curColumn = null;
        if(empty($id) && !empty($sid)){
            $fColumn = Column::where(['nid'=>$sid, 'lang'=>$visit_lang])->find();
            if($fColumn){
                $id = $fColumn['id'];
                $curColumn =$fColumn;
            }else{
                echo "upward标签栏目标识不存在";
                die();
            }
        }

        if(empty($id)){
            $id = \request()->param("id");
            $action = \request()->action();
            if($action == "detail"){//详情
                $columnModel = strtolower(request()->controller());
                $cm =  Db::name($columnModel)->field("column_id")->find($id);
                if(!$cm){
                    return [];
                }
                $id = $cm['column_id'];
            }
        }
        if($curColumn == null){
            $curColumn = Column::find($id);
        }
        if(!$curColumn){
            return [];
        }
        $pid = $curColumn["pid"];
        if($type == "top"){//查自身栏目
            $pColumn = Column::find($pid);
            $resultList = [];
            array_push($resultList, $pColumn);
            return $resultList;
        }elseif ($type == "son"){//查子兄弟栏目
            $query->whereOr("id", $id)->whereOr('pid', $pid);
        }elseif($type == "all"){//查兄弟和父类
            $query->whereOr("pid", $pid)->whereOr('id', $pid)->whereOr("id", $id);
        }elseif ($type == "self"){//自己
            $resultList = [];
            array_push($resultList, $curColumn);
            return $resultList;
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
        return $query->order($ob)->limit($offset, $row)->select()->each(function ($item) use ($visit_lang){
            $item['visit_lang'] = $visit_lang;
            return $item;
        });
    }

}