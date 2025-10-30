<?php

namespace app\common\model;

use think\Model;

/**
 * 栏目自定义字段表
 * Class ColumnField
 * @package app\common\model
 */
class ColumnField extends Model
{
    protected $autoWriteTimestamp = "datetime";
    protected $append = ['statustext'];

    public function getStatustextAttr($value,$data)
    {
        $status = [0=>'禁用',1=>'启用'];
        return $status[$data['status']];
    }


}