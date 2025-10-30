<?php

namespace app\admin\validate;

use think\Validate;

class Admin extends Validate
{
    protected $rule = [
        'username' => 'require',
        'password' => 'require',
        'nickname'=>'require',
        'group_id'=>'require'
    ];

    protected $message = [
        'username.require' => '用户名不能为空！',
        'password.require' => '密码不能为空！',
        'nickname.require' => '操作员姓名不能为空！',
        'group_id.require' => '用户角色不能为空！',
    ];

    protected $scene=[
        'save'=>['username','password','nickname','group_id'],
        'delete'=>['id'],
        'update'=>['id','username','nickname','group_id'],
        'read'=>['id'],
    ];
}
