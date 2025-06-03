<?php

namespace app\taglib\fox;
use app\common\model\Column;
use app\common\model\Product;
use app\common\model\ProductParam;

/**
 * 产品属性列表
 */
class TagProductparam extends TagBase
{
    /**
     * 查询产品数据
     */
    public function getList($param, $ob="create_time desc", $offset=0,  $row=10)
    {
        $where = [];//增加搜索条件
        $query = ProductParam::field('name,type,sel_value,type_id,type_desc,dfvalue,create_time')->where($where);
        $id = $param["typeid"];
        $calltype = $param["calltype"];//标签调用方式
        $typeidP = $param["typeidP"];//父栏目id
        if(($calltype != "self") && empty($id) && !empty($typeidP)){
            $id = (String)$typeidP;
        }
        if(empty($id)){
            $id = \request()->param("id");
        }
        $query->where(['product_id'=>$id]);
        return $query->order($ob)->limit($offset, $row)->select();
    }

}