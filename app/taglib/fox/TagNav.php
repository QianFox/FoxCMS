<?php

namespace app\taglib\fox;
use app\common\model\Column;
use app\common\model\ColumnLevel;

/**
 * 栏目导航列表
 */
class TagNav extends TagBase
{

    /**
     * 查询导航栏目数据
     */
    public function getList($param, $type, $ob="create_time desc", $offset=0,  $row=999)
    {
        $visit_lang = $this->getLang();//语言
        $query = Column::where([['status', "=", 1]]);
        $query->where("lang", "=", $visit_lang);
        $typeidP = $param["typeidP"];//栏目id
        $notypeid = $param["notypeid"];//是否包含自己
        $typeid = $param["typeid"];//栏目id
        $calltype = $param["calltype"];//标签调用方式
        $orderbyid = $param["orderbyid"];//默认有排序
        $orderby = $param["orderby"];//默认排序
        if ($type == "son") {
            $calltype = "parent";
        }
        if (($calltype != "self") && empty($typeid) && !empty($typeidP)) {
            $typeid = (String)$typeidP;
        }
        $sid = $param["sid"];//栏目标识
        if(empty($typeid) && !empty($sid)){
            $fColumn = Column::field("id")->where(['nid'=>$sid, 'lang'=>$visit_lang])->find();
            if($fColumn){
                $typeid = $fColumn['id'];
            }else{
                echo "nav标签栏目标识不存在";
                die();
            }
        }
        if($type == "ceils"){//查询所有顶层栏目
            $columns = Column::field("id")->where(["pid"=>0, "status"=>1, "lang"=>$visit_lang])->select();
            $typeidArr = [];
            if(!empty($notypeid)){
                if($notypeid == "self"){
                    if(!empty($typeid)){
                        $query->whereNotIn('id', $typeid);//排除自身栏目
                    }
                }else{
                    $query->whereNotIn('id', $notypeid);//排除自身栏目
                }
            }
            foreach ($columns as $column){
                array_push($typeidArr, $column["id"]);
            }
            $typeid = implode(",", $typeidArr);
            $query->where("id", 'in', $typeid);
        }else{
            if (!empty($typeid)) {
                if ($type == "top") {//查自身及父栏目

                    $curColumns = Column::whereIn("id", $typeid)->select();
                    $topIdArr = [];//父栏目id
                    foreach ($curColumns as $curColumn) {
                        array_push($topIdArr, $curColumn->pid);
                    }
                    $topIdArr = array_unique($topIdArr);//去重
                    $query->whereIn('id', implode(",", $topIdArr));
                    if ($notypeid != "self") {
                        $query->whereOr('id', "in", $typeid);//查自身栏目
                    }
                } elseif ($type == "ceil") {//最顶层及自身

                    $curColumns = Column::whereIn("id", $typeid)->select();//最顶层
                    $ceilIdArr = [];//最顶层id
                    foreach ($curColumns as $curColumn) {
                        $tierArr = explode(",", $curColumn->tier);
                        array_push($ceilIdArr, $tierArr[0]);
                    }
                    $ceilIdArr = array_unique($ceilIdArr);//去重
                    $query->whereIn('id', implode(",", $ceilIdArr));
                    if ($notypeid != "self") {
                        $query->whereOr('id', 'in', $typeid);//查自身栏目
                    }
                } elseif ($type == "self") {//查自己栏目
                    $query->whereIn('id', $typeid);
                } elseif ($type == "son") {//查子栏目及自身
                    $query->whereIn('pid', $typeid);
                    if ($notypeid != "self") {
                        $query->whereOr('id', 'in', $typeid);//查自身栏目
                    }
                } elseif ($type == "sibling") {//查兄弟及自身

                    $curColumns = Column::whereIn("id", $typeid)->select();
                    $topIdArr = [];//父栏目id
                    foreach ($curColumns as $curColumn) {
                        array_push($topIdArr, $curColumn->pid);
                    }
                    $topIdArr = array_unique($topIdArr);//去重
                    $query->whereIn('pid', implode(",", $topIdArr));//查兄弟
                    if ($notypeid == "self") {
                        $query->whereNotIn('id', $typeid);//排除自身栏目
                    }
                } elseif ($type == "all") {//查子栏目、父栏目、兄弟栏目及自身
                    $curColumns = Column::whereIn("id", $typeid)->select();//当前栏目
                    $topIdArr = [];//父栏目id
                    foreach ($curColumns as $curColumn) {
                        array_push($topIdArr, $curColumn->pid);
                    }
                    $topIdArr = array_unique($topIdArr);//去重
                    $query->whereIn('id', implode(",", $topIdArr));//查父
                    $query->whereOr('pid', $typeid);//查子
                    $query->whereOr('pid', implode(",", $topIdArr));//查兄弟
                } elseif ($type == "sonsib") {//查子没得子查兄弟
                    $query->whereIn('pid', $typeid);
                    if ($notypeid != "self") {
                        $query->whereOr('id', 'in', $typeid);//查自身栏目
                    }
                }
            }
        }

        $limit = $param["limit"];//数据限制
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

        //排除id
        if(!empty($notypeid)){
            if($notypeid == "self"){
                $notypeid = $typeid;
            }
        }
        //查询栏目限制
        $columnLevels = ColumnLevel::select();
        $level = 3;
        if(sizeof($columnLevels) > 0){
            $level = $columnLevels[0]['level'];
        }
        $query->where("level", "<=", $level);
        if($orderbyid == "nosort" && !empty($typeid)){//不排序
            $rlist = $query->orderRaw("find_in_set(id,'".$typeid."')")->limit($offset, $row)->select();
        }else{
            if($orderby == "admin"){//默认后台排序
                $sortorder = $param['sortorder'];
                $rlist = $query->order("level {$sortorder}")->order("sort {$sortorder}")->limit($offset, $row)->select();
            }else{
                $rlist = $query->order($ob)->limit($offset, $row)->select();
            }
        }


        if($type == "sonsib"){
            if(sizeof($rlist) <= 0){
                if(!empty($typeid)){
                    $pColumns = Column::field("pid")->where("lang", "=", $visit_lang)->whereIn("id", $typeid)->where("level", "<=", $level)->select();
                }else{
                    $pColumns = Column::field("id")->where("lang", "=", $visit_lang)->where("pid", "=", 0)->where("level", "<=", $level)->select();
                }
                $pids = [];
                foreach ($pColumns as $pColumn){
                    if($pColumn["pid"] != 0){
                        array_push($pids, $pColumn["pid"]);
                    }
                }
                if(sizeof($pids) > 0){
                    if($orderby == "admin"){//默认后台排序
                        $sortorder = $param['sortorder'];
                        $rlist = Column::whereIn("pid", implode(",", $pids))->where("lang", "=", $visit_lang)->order("level {$sortorder}")->order("sort {$sortorder}")->limit($offset, $row)->select();
                    }else{
                        $rlist = Column::whereIn("pid", implode(",", $pids))->where("lang", "=", $visit_lang)->order($ob)->limit($offset, $row)->select();
                    }
                }
            }
        }elseif ($type == "all"){
            if($notypeid == "self"){
                if(!empty($notypeid)){
                    $exIds = explode(",", $notypeid);
                    foreach ($rlist as $key=>$rdata){
                        if(in_array($rdata["id"], $exIds)){
                            unset($rlist[$key]);
                        }
                    }
                }
            }
        }
        foreach ($rlist as $K=>$item){
            $item['visit_lang'] = $visit_lang;
        }
        return $rlist;
    }

}