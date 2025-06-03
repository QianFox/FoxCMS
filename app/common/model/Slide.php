<?php

namespace app\common\model;
use think\Model;

class Slide extends Model
{

    // 追加属性
    protected $append = ["statustext"];

    public function getImgUrlAttr($value)
    {
        if(empty($value)){
            return "";
        }
        if (preg_match('/(http:\/\/)|(https:\/\/)/i', $value)) {//判断是否存在
            return $value;
        }else{
            if(!str_starts_with($value, "/")){
                return "/".$value;
            }
            return $value;
        }
    }

    public function getStatustextAttr($value,$data)
    {
        $status = [0=>'禁用',1=>'启用'];
        return $status[$data['status']];
    }
}