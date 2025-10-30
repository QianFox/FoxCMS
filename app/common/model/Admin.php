<?php

namespace app\common\model;

use think\Model;

class Admin extends Model
{
    protected $autoWriteTimestamp = true;
    protected $append = ['statustext'];

    public function getStatustextAttr($value,$data)
    {
        $status = [0=>'禁用',1=>'启用'];
        return $status[$data['status']];
    }

    public function authGroupAccess()
    {
        return $this->belongsToMany(AuthGroup::class, AuthGroupAccess::class, 'group_id', 'admin_id');
    }
}