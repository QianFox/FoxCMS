<?php

namespace app\common\model;

use think\facade\Db;
use think\Model;

class MemberModel extends Model
{
    protected $autoWriteTimestamp = "datetime";
    protected $append = ['systemtext', 'statustext', 'membercount'];

    public function getSystemtextAttr($value,$data)
    {
        $systems = [0=>'自定',1=>'系统'];
        return $systems[$data['is_system']];
    }

    public function getStatusTextAttr($value,$data)
    {
        $status = [0=>'禁用',1=>'启用'];
        return $status[$data['status']];
    }

    public function getMembercountAttr($value,$data)
    {
      return Db::name($data['nid'])->count();
    }

}