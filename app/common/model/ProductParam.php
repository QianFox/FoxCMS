<?php

namespace app\common\model;
use think\Model;


class ProductParam extends Model
{
    // 追加属性
    protected $append = ['selvalues',"attrparams"];

    protected $autoWriteTimestamp = "datetime";


    public function getSelvaluesAttr($value,$data){

        if(empty($data['sel_value'])){
            return '';
        }
        return explode(",", $data['sel_value']);
    }

    public function getAttrparamsAttr($value,$data){

        if(empty($data['attr_param_id'])){
            return '';
        }
        return (new ProductAttrParam())->select();

    }
}