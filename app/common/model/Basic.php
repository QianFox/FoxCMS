<?php

namespace app\common\model;

use think\Model;

class Basic extends Model
{
    protected $autoWriteTimestamp = true;

    public function getStatustextAttr($value,$data)
    {
        $status = [0=>'禁用',1=>'启用'];
        return $status[$data['status']];
    }

}