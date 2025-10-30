<?php

namespace app\common\model;

use think\Model;

class ModelRecord extends Model
{
    protected $autoWriteTimestamp = "datetime";
    protected $append = ['statustext', 'systemtext'];

    public function getSystemtextAttr($value,$data)
    {
        $systems = [0=>'自定',1=>'系统'];
        return $systems[$data['is_system']];
    }

    public function getStatusTextAttr($value,$data)
    {
        $status = [0=>'禁用',1=>'启用'];
        return $status[$data['status']];
    }
}