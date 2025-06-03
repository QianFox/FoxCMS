<?php

namespace app\taglib\fox;
use app\common\model\Column;
use think\facade\Db;

/**
 * 栏目集合
 */
class TagChannelartlist extends TagBase
{

    /**
     * 查询数据
     */
    public function getList($param, $ob="create_time desc", $offset=0,  $row=10)
    {
        $visit_lang = $this->getLang();//语言
        $query = Column::where([]);
        $typeid = $param["typeid"];//栏目id top
        $limit = $param["limit"];
        $notypeid = $param["notypeid"];//栏目id
        $type = $param["type"];//栏目id
        $orderbyid = $param["orderbyid"];//默认排序

        $sid = $param["sid"];//栏目标识
        if(empty($typeid) && !empty($sid)){
            $fColumns = Column::field("column_model,id")->whereIn("nid",$sid)->where(['lang'=>$visit_lang])->select();
            if(sizeof($fColumns)>0){
                $columnIds = [];
                foreach($fColumns as $fColumn){
                    array_push($columnIds,$fColumn['id']);
                }
                $typeid = implode($columnIds, ",");
            }else{
                echo "channelartlist标签栏目标识不存在";
                die();
            }
        }

        if(empty($typeid)){
            $id = request()->param("id");
            $action = request()->action();
            if($action == "detail"){//详情
                $columnModel = strtolower(request()->controller());
                $data = Db::name($columnModel)->find($id);
                if(!$data){
                    return [];
                }
                $id = $data['column_id'];
            }
            $typeid = (String)$id;
        }

        if($type == "top"){//查自身及父栏目
            $curColumns = Column::whereIn("id", $typeid)->select();
            $topIdArr = [];//父栏目id
            foreach ($curColumns as $curColumn){
                array_push($topIdArr, $curColumn->pid);
            }
            $topIdArr = array_unique($topIdArr);//去重
            $query->whereIn('id', implode(",", $topIdArr));
            if($notypeid != "self"){
                $query->whereOr('id', "id", $typeid);//查自身栏目
            }
        }elseif($type == "self"){//查自身栏目
            $query->whereIn('id', $typeid);
        }elseif ($type == "son"){//查子栏目
            $query->whereIn('pid', $typeid);//查子栏目
            if($notypeid != "self") {//查自己
                $query->whereOr('id','in', $typeid);
            }
        }elseif ($type == "sibling"){//查兄弟及自身
            $curColumns = Column::whereIn("id", $typeid)->select();
            $topIdArr = [];//父栏目id
            foreach ($curColumns as $curColumn){
                array_push($topIdArr, $curColumn->pid);
            }
            $topIdArr = array_unique($topIdArr);//去重
            $query->whereIn('pid', implode(",", $topIdArr));//查兄弟
            if($notypeid == "self"){
                $query->whereNotIn('id', $typeid);//排除自身栏目
            }
        }elseif($type == "all"){//查子栏目、父栏目、兄弟栏目及自身

            $curColumns = Column::whereIn("id", $typeid)->select();//当前栏目
            $topIdArr = [];//父栏目id
            foreach ($curColumns as $curColumn){
                array_push($topIdArr, $curColumn->pid);
            }
            $topIdArr = array_unique($topIdArr);//去重
            $query->whereIn('id', implode(",", $topIdArr));//查父
            $query->whereOr('pid', $typeid);//查子
            $query->whereOr('pid', implode(",", $topIdArr));//查兄弟

        }elseif($type == "ceils"){//查询所有顶层栏目
            $columns = Column::field("id")->where(["pid"=>0, "status"=>1])->select();
            $typeidArr = [];
            if(!empty($notypeid)){
                if($notypeid == "self"){
                    $query->whereNotIn('id', $typeid);//排除自身栏目
                }else{
                    $query->whereNotIn('id', $notypeid);//排除自身栏目
                }
            }
            foreach ($columns as $column){
                array_push($typeidArr, $column["id"]);
            }
            $typeid = implode(",", $typeidArr);
            $query->where("id", 'in', $typeid);
        }elseif ($type == "ceil"){//最顶层及自身
            $curColumns = Column::whereIn("id", $typeid)->select();//最顶层
            $ceilIdArr = [];//最顶层id
            foreach ($curColumns as $curColumn){
                $tierArr = explode(",", $curColumn->tier);
                array_push($ceilIdArr, $tierArr[0]);
            }
            $ceilIdArr = array_unique($ceilIdArr);//去重
            $query->whereIn('id', implode(",", $ceilIdArr));
            if($notypeid != "self"){
                $query->whereOr('id',"in",$typeid);//查自身栏目
            }
        }

        //排除id
        if(!empty($notypeid)){
            if($notypeid == "self"){
                $notypeid = $typeid;
            }
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
        if($orderbyid == "nosort"){//不排序
            $rlist = $query->orderRaw("find_in_set(id,'".$typeid."')")->limit($offset, $row)->select();
        }else{
            $rlist = $query->order($ob)->limit($offset, $row)->select();
        }

        if ($type == "all"){
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