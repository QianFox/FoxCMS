<?php

namespace app\controller;

use app\common\controller\Base;

class Error extends Base
{
    public function __call($method, $args)
    {
        $this->error("操作错误，请联系管理员！");
    }
}