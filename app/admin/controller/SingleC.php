<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   15:30
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   15:30
 */

namespace app\admin\controller;

use app\common\controller\AdminContentBase;
use app\common\model\Column;
use app\common\model\Single;
use think\facade\View;

// 单页模型
class SingleC extends AdminContentBase
{
    public function index()
    {
        $param = $this->request->param();
        View::assign('bcid', $param['bcid']);
        $columnId = $param['columnId'];
        $single = Single::where('column_id', $columnId)->find();

        $teamStatusArr = explode(',', $single['team_status']);
        if (sizeof($teamStatusArr) > 0) {
            if (in_array('down', $teamStatusArr)) {
                $single["statusDown"] = "down";
            }
            if (in_array('del', $teamStatusArr)) {
                $single["statusDel"] = "del";
            }
        }

        if (empty($single->column)) {
            $single["column"] = Column::field('name')->find($columnId)['name'];
        }

        View::assign('single', $single);
        View::assign('columnId', $columnId);
        return view();
    }

    public function save()
    {
        $param = $this->request->param();
        if (array_key_exists("content", $param)) {
            $rdata = $this->replaceContent($param, 'content'); //替换内容
            if ($rdata["code"] == 0) {
                $this->error($rdata['msg']);
            }
            $param['content'] = $rdata['content']; //替换内容
        }
        $r = false;
        $param['lang'] = $this->getMyLang();
        if (empty($param['id'])) {
            $r = Single::create($param);
        } else {
            $r =  Single::update($param);
        }
        if ($r) {
            $this->success("保存成功");
        } else {
            $this->error("保存失败");
        }
    }
}