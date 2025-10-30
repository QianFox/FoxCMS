<?php

namespace app\common\model;
use think\Model;

class Tag extends Model
{

    // 追加属性
    protected $append = ["usedtext", 'taggroupname'];

    public function getUsedtextAttr($value,$data)
    {
        $status = [0=>'否',1=>'是'];
        return $status[$data['is_used']];
    }
    public function getTaggroupnameAttr($value,$data)
    {
        if(empty($data['tag_group_id'])){
            return "";
        }
       return TagGroup::field("name")->find($data['tag_group_id'])['name'];
    }
}