<?php

namespace app\common\model;
use think\Model;

class Search extends Model
{

    // 追加属性
    protected $append = ["usedtext", 'searchgroupname'];

    public function getUsedtextAttr($value,$data)
    {
        $status = [0=>'否',1=>'是'];
        return $status[$data['is_used']];
    }
    public function getSearchgroupnameAttr($value,$data)
    {
        if(empty($data['search_group_id'])){
            return "";
        }
        return SearchGroup::field("name")->find($data['search_group_id'])['name'];
    }
}