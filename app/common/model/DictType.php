<?php

namespace app\common\model;

use think\Model;

/**
 * 字典类型表
 * Class DictType
 * @package app\common\model
 */
class DictType extends Model
{
    protected $autoWriteTimestamp = true;

    public function getStatustextAttr($value,$data)
    {
        $status = [0=>'正常',1=>'停用'];
        return $status[$data['status']];
    }

    public function dictDatas()
    {
        return $this->hasMany(DictData::class, 'dict_type', 'dict_type');
    }
}