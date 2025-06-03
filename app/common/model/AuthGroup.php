<?php

namespace app\common\model;
use think\Model;

class AuthGroup extends Model
{
    protected $append = ['statustext'];

    public function getStatustextAttr($value,$data)
    {
        $status = [0=>'禁用',1=>'启用'];
        return $status[$data['status']];
    }
    //一对多
    public function authGroupAccess(){
        return $this->hasMany(AuthGroupAccess::class,'group_id','id');
    }

}