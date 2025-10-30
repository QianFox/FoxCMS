<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   15:37
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   15:37
 */

namespace app\admin\controller;

use app\common\model\AuthRule;
use app\common\model\ColumnLevel;

use app\common\controller\AdminBase;
use think\facade\View;

// 层级设置
class TierSet extends AdminBase
{
    public function index()
    {
        $param = $this->request->param();
        $columnId = $param["columnId"];
        $authRule = AuthRule::find($columnId);
        $bcidStr = str_replace(",", "_", $authRule->tier);
        $breadcrumb = AuthRule::getBreadcrumb($bcidStr);
        array_push($breadcrumb, ['id' => '', 'title' => '设置栏目层级', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/TierSet/index', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);

        $columnLevels = ColumnLevel::select();
        $columnLevel = ['level' => 3, 'is_thumb' => 1];
        if (sizeof($columnLevels) > 0) {
            $columnLevel = $columnLevels[0];
        }
        View::assign("columnLevel", $columnLevel);
        View::assign("ts", $param['ts']);
        return view('index');
    }

    // 查询栏目层级
    public function getTierSet()
    {
        $columnLevels = ColumnLevel::select();
        $columnLevel = ['level' => 3, 'is_thumb' => 1];
        if (sizeof($columnLevels) > 0) {
            $columnLevel = $columnLevels[0];
        }
        $this->success("查询成功", null, $columnLevel);
    }

    public function save()
    {
        $param = $this->request->post();
        if (empty($param["id"])) {
            $r = (new ColumnLevel())->save($param);
            if (!$r) {
                $this->success("操作失败");
            }
        } else {
            $r = (new ColumnLevel())->update($param);
            if (!$r) {
                $this->success("操作失败");
            }
        }
        $this->success('操作成功');
    }
}