<?php

namespace app\common\model;

use think\Model;

class DictData extends Model
{
    protected $autoWriteTimestamp = 'datetime';

    protected $key = 'dict_code';

    public function dictType()
    {
        return $this->belongsTo(DictType::class,'dict_type','dict_type');
    }
}