<?php

namespace app\common\model;

use think\Model;

class Template extends Model
{
    protected $autoWriteTimestamp = true;

    // 追加属性
    protected $append = ['thumb_url','detail_url'];

    public function getStatustextAttr($value,$data)
    {
        $status = [0=>'不运行',1=>'运行'];
        return $status[$data['status']];
    }


    public function getThumbUrlAttr($value, $data){
        if(empty($data['thumb_id'])){
            return '';
        }
        return UploadFiles::field('url')->find($data['thumb_id'])['url'];
    }


    public function getDetailUrlAttr($value, $data){
        if(empty($data['detail_id'])){
            return '';
        }
        return UploadFiles::field('url')->find($data['detail_id'])['url'];
    }

}