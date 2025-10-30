<?php

namespace app\common\model;

use think\Model;

class ModelField extends Model
{
    protected $autoWriteTimestamp = "datetime";
    protected $append = ['category', 'statustext'];

    public function getCategoryAttr($value,$data)
    {
        $systemtexts = [0=>'自定义',1=>'系统'];
        return $systemtexts[$data['is_system']];
    }

    public function getStatustextAttr($value,$data)
    {
        $status = [0=>'禁用',1=>'启用'];
        return $status[$data['status']];
    }
}