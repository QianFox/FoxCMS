<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   19:23
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   19:23
 */

namespace app\plus\controller;

use app\common\controller\ApiBase;
use think\facade\Db;

class Arcclick extends ApiBase
{
    // 修改点击/预览数
    function index()
    {
        $param = $this->request->param();
        $typeid  = $param["typeid"];
        $model  = $param["model"];
        $type  = $param["type"];
        if (!empty($typeid) && !empty($model) && !empty($type)) {
            if ($type == "view") { //新增并且返回
                Db::name($model)->where("id", $typeid)->update(['click' => Db::raw('click + 1')]);
            }
            $view_num = Db::name($model)->where("id", $typeid)->value("click");
            echo "document.write('" . $view_num . "');";
            exit;
        } else {
            echo "document.write(0);";
            exit;
        }
    }
}