<?php

namespace app\common\model;
use think\Model;

class Single extends Model
{

    // 追加属性
    protected $append = ['column'];

    public function getColumnAttr($value,$data){
        if(empty($data['column_id'])){
            return '';
        }
        return Column::field('name')->find($data['column_id'])['name'];
    }

}