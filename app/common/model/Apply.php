<?php

namespace app\common\model;
use think\Model;

class Apply extends Model
{
    // 追加属性
    protected $append = [];

    public function getStatusAttr($value,$data)
    {
        $status = [0=>'隐藏',1=>'启用'];
        return $status[$data['status']];
    }

}