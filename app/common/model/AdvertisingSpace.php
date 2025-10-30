<?php

namespace app\common\model;
use think\Model;

/**
 * 广告位置
 * Class AdvertisingSpace
 * @package app\common\model
 */
class AdvertisingSpace extends Model
{
    protected $append = ['statustext'];

    public function getStatustextAttr($value,$data)
    {
        $status = [0=>'禁用',1=>'启用'];
        return $status[$data['status']];
    }
    //一对多
    public function slides(){
        return $this->hasMany(Slide::class,'advertising_space_id','id');
    }

}