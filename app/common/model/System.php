<?php

namespace app\common\model;

use think\Model;

class System extends Model
{
    protected $autoWriteTimestamp = true;

    public function getStatustextAttr($value,$data)
    {
        $status = [0=>'不运行',1=>'运行'];
        return $status[$data['status']];
    }

}