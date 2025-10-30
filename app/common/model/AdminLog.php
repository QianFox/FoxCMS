<?php

namespace app\common\model;

use think\Model;

class AdminLog extends Model
{
    protected $autoWriteTimestamp = 'datetime';
//    protected $dateFormat = 'Y-m-d H:i:s';

    // 追加属性
    protected $append = ['username'];

    public function getUsernameAttr($value, $data){
        if(empty($data['admin_id'])){
            return '';
        }
        return Admin::field("username")->find($data['admin_id'])["username"];
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class,'admin_id');
    }


    public function scopeAdminLog($query){
        $query->where('admin_id', 1)->field('id,admin_id,url')->limit(5);
    }
}