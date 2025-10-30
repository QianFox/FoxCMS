<?php

namespace app\common\model;
use think\Model;

class Link extends Model
{

    // 追加属性
    protected $append = ["statustext"];

    public function getStatustextAttr($value,$data)
    {
        $status = [0=>'禁用',1=>'启用'];
        return $status[$data['status']];
    }
}