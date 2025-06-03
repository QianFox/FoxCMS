<?php
/*
 * @Descripttion : QianFox让数字化营销更简单
 * @Author       : QianFox Team
 * @Date         : 2024-07-05 14:29:45
 * @Version      : V1.24
 * @Copyright    : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditors  : QianFox Team
 * @LastEditTime : 2024-11-18 22:12:08
 */

namespace app\admin\controller;

use app\common\controller\AdminContentBase;
use app\common\model\Column;
use app\common\model\FormList as FormListModal;
use think\facade\Db;
use think\facade\View;

class Formmodel extends AdminContentBase
{

    public function initialize()
    {
        parent::initialize();
    }

    public function index()
    {
        $param = $this->request->param();
        $columnId = $param['columnId'];
        View::assign('bcid', $param['bcid']);

        View::assign('columnId', $columnId);
        $column = Column::field("form_list_id")->find($columnId);
        $bind_form = 0; //绑定表单标识
        if ($column) {
            if (!empty($column['form_list_id'])) {
                $bind_form = 1;
            }
        }
        if ($bind_form == 0) { //没有绑定过表单
            //表单地址
            $apply = \app\common\model\Apply::where(['status' => 1, 'name' => '自定义表单'])->find();
            $formUrl = url("Apply/index") . "?columnId=42&type=1";
            if ($apply) {
                if (!empty($apply["path"])) {
                    $url = url("/" . $apply['mark'] . $apply["path"]) . "?columnId=" . $apply['column_id'];
                    if (!empty($apply['auth_rule_ids'])) {
                        $url = $url . "&ruleIds=" . $apply['auth_rule_ids'];
                    }
                    $formUrl = $url . "&type=1";
                }
            }
            View::assign("formUrl", $formUrl);
            //应用表单
            $formList = \app\common\model\FormList::field("id,name")->select();
            View::assign("formList", $formList);
        } else { //绑定过表单
            $tableItem = []; //表格项
            $tableHeads = []; //表头
            //table列
            $ffList = \app\common\model\FormField::where(['form_list_id' => $column['form_list_id']])->order(["sort_order" => "asc", "create_time" => "asc"])->select();
            foreach ($ffList as $ff) {
                array_push($tableHeads, ['key' => $ff['name'], 'title' => $ff['title']]);
                array_push($tableItem, $ff['name']);
            }

            array_push($tableHeads, ['key' => 'create_time', 'title' => '创建时间']);
            array_push($tableItem, 'create_time');

            if ($this->request->isAjax()) {
                if (empty($param["currentPage"])) {
                    $param["currentPage"] = 1;
                }
                if (empty($param["pageSize"])) {
                    $param["pageSize"] = 100;
                }
                $where = [];
                if (sizeof($tableHeads) > 2 && array_key_exists('keyword', $param) && !empty($param['keyword'])) {
                    array_push($where, ["{$tableHeads[0]['key']}|{$tableHeads[1]['key']}", 'like', '%' . trim($param['keyword']) . '%']);
                }
                $formList = FormListModal::find($param["formListId"]); //表单
                $list = Db::table($formList["table_name"])->where($where)->paginate(['page' => $param['currentPage'], 'list_rows' => $param['pageSize']]);
                $this->success('查询成功', null, $list);
            }

            View::assign("tableHeads", $tableHeads);
            View::assign("formListId", $column["form_list_id"]);
            View::assign("tableItemStr", implode(',', $tableItem));
        }
        View::assign('bind_form', $bind_form); //绑定表单标识
        return view();
    }

    public function save()
    {
        $param = $this->request->param();
        if (empty($param['form_list_id']) || empty($param['columnId'])) {
            $this->error("保存失败，缺少参数");
        }
        $r = Column::update(['id' => $param['columnId'], 'form_list_id' => $param['form_list_id']]);
        if ($r) {
            $this->success("操作成功");
        }
        $this->error("操作失败");
    }
}
