<?php

namespace app\common\model;

use think\Model;

class MemberLevel extends Model
{
    protected $autoWriteTimestamp = "datetime";
    protected $append = ['statustext'];

    public function getStatustextAttr($value,$data)
    {
        $status = [0=>'禁用',1=>'启用'];
        return $status[$data['status']];
    }
}