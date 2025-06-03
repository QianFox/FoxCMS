<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   15:08
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   15:08
 */

namespace app\admin\controller;

use app\admin\util\Field;
use app\common\controller\AdminBase;
use app\common\model\AuthRule;
use think\App;
use think\Exception;
use think\facade\View;

// 会员级别
class MemberLevel extends AdminBase
{
    public function index($page = 1, $pageSize = 100)
    {
        $param = $this->request->param();
        if (array_key_exists('bcid', $param)) {
            View::assign('bcid', $param['bcid']);
        }
        if ($this->request->isAjax()) {
            $where = [];
            if (array_key_exists('keyword', $param) && !empty($param['keyword'])) {
                if ($param['keyword'] == '禁用') {
                    array_push($where, ['status', '=', 0]);
                } else if ($param['keyword'] == '启用') {
                    array_push($where, ['status', '=', 1]);
                } else {
                    array_push($where, ['name', 'like', '%' . trim($param['keyword']) . '%']);
                }
            }
            $memberLevel = new \app\common\model\MemberLevel();
            $list = $memberLevel->where($where)->order('create_time', 'desc')->paginate(['page' => $page, 'list_rows' => $pageSize]);
            $this->success('查询成功', null, $list);
        }

        return view('index');
    }

    public function add()
    {
        $param = $this->request->param();
        //功能面包屑
        $bcid = $param['bcid'];
        View::assign('bcid', $bcid);
        $columnId = $param['columnId']; //栏目id
        $authRule = AuthRule::find($columnId);
        $bcidStr = str_replace(",", "_", $authRule->tier);
        $breadcrumb = AuthRule::getBreadcrumb($bcidStr);
        array_push($breadcrumb, ['id' => '', 'title' => '添加会员等级', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/MemberLevel/add', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);
        View::assign("columnId", $columnId);

        if ($this->request->isAjax()) {
            $param = $this->request->post();
            if (empty($param['name'])) {
                $this->error("缺少必填信息！");
            }
            $param['name'] = trim($param['name']);
            $mr = \app\common\model\MemberLevel::where('name', $param['name'])->find();
            if ($mr) {
                $this->error("会员等级已存在");
            }
            try {
                (new \app\common\model\MemberLevel())->save($param);
            } catch (\Exception $e) {
                $this->error('保存失败会员等级已经存在,' . $e->getMessage());
            }

            //查询所有会员级别
            $this->updateMmf();
            $this->success("操作成功");
        }
        return  view('add');
    }

    // 修改会员模型字段类型
    private function updateMmf()
    {
        try {
            $mmf = \app\common\model\MemberModelField::where(['name' => 'memberlevel'])->find();
            if ($mmf) {
                $where = ['status' => 1];
                $mlList = (new \app\common\model\MemberLevel())->where($where)->field("name")->select();
                $dfvalueArr = [];
                foreach ($mlList as $ml) {
                    array_push($dfvalueArr, trim($ml['name']));
                }
                $dfvalue = implode(',', $dfvalueArr);
                $fieldinfos = (new Field())->GetFieldMake('select', 'memberlevel', $dfvalue, '会员级别');
                $mmf->define = $fieldinfos[1];
                $mmf->dfvalue = !empty($dfvalueArr[0]) ? $dfvalueArr[0] : '';
                $mmf->save();
            }
        } catch (\Exception $e) {
        }
    }

    public function edit()
    {
        $param = $this->request->param();
        $columnId = $param['columnId']; //栏目id
        $id = $param['id'];
        $authRule = AuthRule::find($columnId);
        $bcidStr = str_replace(",", "_", $authRule->tier);
        $breadcrumb = AuthRule::getBreadcrumb($bcidStr);
        array_push($breadcrumb, ['id' => '', 'title' => '编辑会员等级', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/MemberLevel/edit', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);
        View::assign("columnId", $columnId);

        if ($this->request->isAjax()) {
            $param = $this->request->post();
            (new \app\common\model\MemberLevel())->update($param);

            //查询所有会员级别
            $this->updateMmf();
            $this->success("操作成功");
        }

        $memberLevel = \app\common\model\MemberLevel::find($id);
        View::assign("memberLevel", $memberLevel);
        return  view('edit');
    }

    public function delete()
    {
        $param = $this->request->param();
        $id = $param['id'];
        if (!array_key_exists('id', $param)) {
            $this->error('参数错误');
        }
        \app\common\model\MemberLevel::destroy($id); //删除
        $this->success('操作成功');
    }

    public function deletes()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            if (array_key_exists("idList", $param)) {
                $idList = json_decode($param['idList']);
                $r = \app\common\model\MemberLevel::destroy($idList); //批量删除
                if ($r) {
                    $this->success('操作成功');
                } else {
                    $this->error('操作失败');
                }
            }
        }
    }

    public function updateStatus()
    {
        $param = $this->request->param();
        $idList = json_decode($param['idList']);
        if (sizeof($idList) <= 0) {
            $this->error("操作失败，请选择对应启用数据");
        }
        $status = intval($param['status']);
        $memberLevel = new \app\common\model\MemberLevel();
        try {
            $memberLevel->whereIn("id", implode(",", $idList))->update(["status" => $status]);
        } catch (Exception $e) {
            $this->error('操作失败,' . $e->getMessage());
        }
        $this->success('操作成功');
    }
}