<?php

namespace app\admin\validate;

use think\Validate;

class Param extends Validate
{
    protected $rule = [
        'column_id'=>'require',
    ];

    protected $message = [
        'column_id.require'=>'栏目id不能为空',
    ];

    protected $scene = [
        'add' => ['column_id'],
        'edit' => ['column_id'],
    ];
}