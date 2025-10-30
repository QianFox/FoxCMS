<?php

namespace app\common\model;
use think\Model;

class ProductAttr extends Model
{

    // 追加属性
    protected $append = ['param_list'];

    protected $autoWriteTimestamp = "datetime";

    public function getStatustextAttr($value,$data)
    {
        $status = [0=>'关闭',1=>'启用'];
        return $status[$data['status']];
    }


    public function getParamListAttr($value,$data){
       return ProductAttrParam::where('product_attr_id', $data['id'])->select();
    }

}