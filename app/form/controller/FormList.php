<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   19:17
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   19:17
 */

namespace app\form\controller;

use app\admin\util\Field;
use app\admin\util\TableUtil;
use app\common\controller\AdminApplyBase;
use app\common\model\AuthRule;
use app\common\model\FormField;
use app\common\model\FormList as FormListModal;
use think\facade\Cache;
use think\facade\Db;
use think\facade\View;

// 自定义表单
class FormList extends AdminApplyBase
{
    public function initialize()
    {
        parent::initialize();
        $action = $this->request->action();
        if ($action == "add" || $action == "edit") {
            $pmtList = Db::name('plugin_mail_template')->where([['status', '=', 1], ['type', '=', 2]])->select();
            if ($action == "add") {
                $pmt = [];
                if (sizeof($pmtList) > 0) {
                    $pmt = $pmtList[0];
                    foreach ($pmtList as $pm) {
                        if ($pm['is_default'] == 1) {
                            $pmt = $pm;
                            break;
                        }
                    }
                }
                View::assign("pmt", $pmt);
            }
            View::assign("pmtList", $pmtList);
            $pmc = Db::name('plugin_mail_config')->find();
            $mail_config = 1; //配置
            $mail_config_url = url('/email/PluginMailConfig/index') . "?columnId=42&apply_plugin_id=6&id=4";
            if (
                empty($pmc['smtp_url']) || empty($pmc['smtp_port'])
                || empty($pmc['send_account']) || empty($pmc['auth_code'])
            ) {
                $mail_config = 0;
                $mail_config_url = "";
            }
            View::assign("mail_config_url", $mail_config_url);
            View::assign("mail_config", $mail_config);
        }
    }

    public function index()
    {
        $param = $this->request->param();
        if ($this->request->isAjax()) {
            $model = new FormListModal();
            if (empty($param["currentPage"])) {
                $param["currentPage"] = 1;
            }
            if (empty($param["pageSize"])) {
                $param["pageSize"] = 100;
            }
            $condition = array();
            if (array_key_exists('keyword', $param) && !empty($param['keyword'])) {
                array_push($condition, ['table_name|name', 'like', '%' . $param['keyword'] . '%']);
            }
            $list = $model->where($condition)->order('id desc')->paginate(['page' => $param['currentPage'], 'list_rows' => $param['pageSize']]);

            foreach ($list as $formItem) {
                $formItem['formTotal'] = Db::table($formItem['table_name'])->count();
                $formItem['viewCount'] = Db::table($formItem['table_name'])->where("is_view", 1)->count();
            }
            $this->success("查询成功", "", $list);
        }

        return view('index');
    }

    public function deletes()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            if (array_key_exists("idList", $param)) {
                $idList = json_decode($param['idList']);
                if (sizeof($idList) <= 0) {
                    $this->error("操作失败，请选择对应删除数据");
                }
                $idStr = implode(",", $idList);
                try {
                    $formLists = FormListModal::whereIn('id', $idStr)->select();
                    foreach ($formLists as $key => $fromList) {
                        $id = $fromList["id"];
                        //删除表
                        $sql = "DROP TABLE {$fromList['table_name']}";
                        Db::execute($sql);
                        //删除字段
                        FormField::where('form_list_id', $id)->delete();
                        //删除数据
                        FormListModal::destroy($id);
                    }
                } catch (\Exception $e) {
                    $this->error("操作失败");
                }
                Cache::clear();
                $this->success('操作成功');
            }
        }
    }

    public function add()
    {
        $param = $this->request->param();
        $columnId = $param['columnId']; //栏目id
        $authRule = AuthRule::find($columnId);
        $bcidStr = str_replace(",", "_", $authRule->tier);
        $breadcrumb = AuthRule::getBreadcrumb($bcidStr);
        array_push($breadcrumb, ['id' => '', 'title' => '添加表单', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/FormList/add', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);

        if ($this->request->isPost()) {
            $param = $this->request->param();
            if (array_key_exists("columns", $param)) {
                $columns = $param["columns"];
                foreach ($columns as $k => $column) {
                    //判断参数
                    $dfVArr = explode(",", $column['dfvalue']);
                    $dfVArr2 = array_unique($dfVArr);
                    if (sizeof($dfVArr) != sizeof($dfVArr2)) {
                        $this->error("保存失败，" . $column['name'] . "的值重复");
                    }
                }
            }

            //创建表
            $tableName = $param['table_name'];
            if (empty($tableName)) {
                $this->error("数据库表名不能为空");
            }
            if (strpos($tableName, 'fox_') !== false) {
            } else {
                $tableName = 'fox_' . $tableName;
            }
            if (is_exist_table($tableName)) {
                $this->error("表名已存在");
            }

            $newData = array(
                'name' => $param['name'],
                'table_name' => $tableName,
                'commit_type' => $param['commit_type'],
                'notice_setting' => $param['notice_setting'],
                'verify' => $param['verify']
            );
            $flm = new FormListModal();
            $r = $flm->save($newData);
            $fieldList = []; //表栏列表
            $tableFieldData = []; //表栏列表数据
            if (array_key_exists("columns", $param)) {
                $field = new Field();
                $columns = $param["columns"];
                $formListId = $flm->id;
                Db::name("form_field")->where("form_list_id", $formListId)->delete(); //删除当前表多余字段
                foreach ($columns as $k => $column) {
                    $fieldinfos = $field->GetFieldMake($column['dtype'], $column['name'], $column['dfvalue'], $column['title']);
                    $ntabsql = $fieldinfos[0];
                    $buideType = $fieldinfos[1];
                    $maxlength = $fieldinfos[2];
                    $csql = substr($ntabsql, 0, strlen($ntabsql) - 1);
                    array_push($fieldList, $csql); //表栏列表
                    /*保存新增字段的记录*/
                    $FieldData = array(
                        'form_list_id' => $formListId,
                        'dfvalue' => $column['dfvalue'],
                        'val' => $column['val'],
                        'maxlength' => $maxlength,
                        'define' => $buideType,
                        'dtype' => $column['dtype'],
                        'is_require' => $column['isRequire'],
                        'name' => $column['name'],
                        'remark' => $column['remark'],
                        'title' => $column['title'],
                        'sort_order' => $k,
                        'marke_word' => $column['markeWord']
                    );
                    array_push($tableFieldData, $FieldData); //表栏列表
                }
            }
            //额外增加两个字段
            array_push($fieldList, "`is_view` tinyint(2) DEFAULT '0' COMMENT '是否查看'");
            array_push($fieldList, "`create_time` datetime DEFAULT NULL COMMENT '创建时间'");

            $remark = $param['name'];
            try {
                $e = TableUtil::createTable($tableName, $fieldList, $remark);
                if (!$e) {
                    $flm->delete(); //删除
                    $this->error("创建表失败");
                }
                if (sizeof($tableFieldData) > 0) {
                    //保存表各列的数据
                    $dr = (new FormField())->saveAll($tableFieldData);
                }
                Cache::clear();
            } catch (\Exception $e) {
                $flm->delete(); //删除
                $this->error("创建表失败" . $e->getMessage());
            }

            $this->success("操作成功");
        }

        //默认表名
        $default_table_name = "form_diy" . func_random_num(0, 4);
        View::assign("default_table_name", $default_table_name);
        return view('add');
    }

    // 查询表单项
    public function findFormFields()
    {
        $param = $this->request->param();
        $id = $param['id'];
        $formFieldList = (new FormField())->where("form_list_id", $id)->order(["sort_order" => "asc", "create_time" => "asc"])->select();
        $uf = new \app\common\model\UploadFiles();
        foreach ($formFieldList as $ff) {
            if ($ff->dtype == 'pic' && $ff->val && $ff->val != '') { //图片

                $ff->img = $uf->find(intval($ff->val));
            }
        }
        $this->success("查询成功", null, $formFieldList);
    }

    // 编辑表单字段
    public function edit()
    {
        $param = $this->request->param();
        $flm = new FormListModal();
        if ($this->request->isPost()) {
            if (!array_key_exists("id", $param)) {
                $this->error("id参数不存在，操作失败");
            }
            $rflm = $flm->find(intval($param['id']));
            if (!$rflm) {
                $this->error("没查到更新表，操作失败");
            }
            $formFieldList = (new FormField())->where("form_list_id", intval($param['id']))->select();

            if (array_key_exists("columns", $param)) {
                $columns = $param["columns"];
                foreach ($columns as $k => $column) {
                    //判断参数
                    $dfVArr = explode(",", $column['dfvalue']);
                    $dfVArr2 = array_unique($dfVArr);
                    if (sizeof($dfVArr) != sizeof($dfVArr2)) {
                        $this->error("保存失败，" . $column['title'] . "的值重复");
                    }
                }
            }

            //创建表
            $tableName = $param['table_name'];
            if (empty($tableName)) {
                $this->error("数据库表名不能为空");
            }
            if (strpos($tableName, 'fox_') !== false) {
            } else {
                $tableName = 'fox_' . $tableName;
            }
            //修改表单表数据
            $updateData = array();
            if ($tableName != $rflm['table_name']) { //表名一致
                //修改表名
                $sql = "alter table {$rflm['table_name']} rename {$tableName}";
                try {
                    Db::execute($sql);
                    $updateData['table_name'] = $tableName;
                } catch (\Exception $e) {
                    $this->error("修改表名失败");
                }
            }
            if ($rflm['name'] != $param['name']) {
                $updateData['name'] = $param['name'];
            }
            if ($rflm['commit_type'] != $param['commit_type']) {
                $updateData['commit_type'] = $param['commit_type'];
            }
            if ($rflm['notice_setting'] != $param['notice_setting']) {
                $updateData['notice_setting'] = $param['notice_setting'];
            }
            if ($rflm['email_setting'] != $param['email_setting']) {
                $updateData['email_setting'] = $param['email_setting'];
            }
            if ($rflm['template_id'] != $param['template_id']) {
                $updateData['template_id'] = $param['template_id'];
            }
            if ($rflm['verify'] != $param['verify']) {
                $updateData['verify'] = $param['verify'];
            }
            if (sizeof($updateData) > 0) {
                $updateData["id"] = $rflm['id'];
                FormListModal::update($updateData);
            }
            if (array_key_exists("columns", $param)) {
                //删除的字段
                $delFormFieldArr = array();
                foreach ($formFieldList as $formField) {
                    $isExist = false;
                    foreach ($columns as $column) {
                        if ($column["id"] && $column["id"] == $formField['id']) {
                            $isExist = true;
                            break;
                        }
                    }
                    if (!$isExist) {
                        array_push($delFormFieldArr, $formField);
                    }
                }

                //区分操作数据
                $field = new Field();
                $columns = $param["columns"];
                $addFieldList = []; //添加表栏列表属性
                $updateFieldList = []; //修改表栏列表属性
                $addTableFieldData = []; //添加表栏列表数据
                $updateTableFieldData = []; //修改表栏列表数据
                foreach ($columns as $k => $column) {
                    if ($column["id"]) { //存在
                        $fField = null;
                        foreach ($formFieldList as $formField) {
                            if ($column["id"] == $formField['id']) {
                                $fField = $formField;
                                break;
                            }
                        }
                        if ($fField) {

                            $fieldinfos = $field->GetFieldMake($column['dtype'], $fField['name'], $column['dfvalue'], $column['title'], $column['isRequire']);
                            $ntabsql    = $fieldinfos[0];
                            $buideType  = $fieldinfos[1];
                            $maxlength  = $fieldinfos[2];

                            /*保存新增字段的记录*/
                            $FieldData = array();
                            if ($column['title'] != $fField['title']) {
                                $FieldData['title'] = $column['title'];
                            }
                            if ($buideType != $fField['define']) {
                                $FieldData['define'] = $buideType;
                            }
                            if ($maxlength != $fField['maxlength']) {
                                $FieldData['maxlength'] = $maxlength;
                            }
                            if ($column['val'] != $fField['val']) {
                                $FieldData['val'] = $column['val'];
                            }
                            if ($column['dfvalue'] != $fField['dfvalue']) {
                                $FieldData['dfvalue'] = $column['dfvalue'];
                            }
                            if ($column['markeWord'] != $fField['marke_word']) {
                                $FieldData['marke_word'] = $column['markeWord'];
                            }
                            if ($column['remark'] != $fField['remark']) {
                                $FieldData['remark'] = $column['remark'];
                            }
                            if ($column['isRequire'] != $fField['is_require']) {
                                $FieldData['is_require'] = $column['isRequire'];
                            }
                            if (sizeof($FieldData) > 0) {
                                array_push($updateFieldList, $ntabsql); //修改的字段
                                $FieldData['sort_order'] = $k;
                                $FieldData['id'] = $fField['id'];
                                array_push($updateTableFieldData, $FieldData); //表栏列表
                            }
                        }
                    } else { //不存在
                        //重新生成一个名字
                        foreach ($formFieldList as $fk => $ff) {
                            if ($column['name'] == $ff['name']) {
                                $column['name'] =  $column['name'] . createNonceStr(1);
                                break;
                            }
                        }
                        $fieldinfos = $field->GetFieldMake($column['dtype'], $column['name'], $column['dfvalue'], $column['title'], $column['isRequire']);
                        $ntabsql    = $fieldinfos[0];
                        $buideType  = $fieldinfos[1];
                        $maxlength  = $fieldinfos[2];

                        array_push($addFieldList, $ntabsql); //添加的字段
                        /*保存新增字段的记录*/
                        $FieldData = array(
                            'form_list_id'      => $rflm->id,
                            'dfvalue'     => $column['dfvalue'],
                            'val'     => $column['val'],
                            'maxlength'   => $maxlength,
                            'define'      => $buideType,
                            'dtype'      => $column['dtype'],
                            'is_require'      => $column['isRequire'],
                            'name'  => $column['name'],
                            'remark'  => $column['remark'],
                            'title'  => $column['title'],
                            'sort_order'  => $k,
                            'marke_word'  => $column['markeWord']
                        );
                        array_push($addTableFieldData, $FieldData); //表栏列表
                    }
                }
                if (sizeof($addFieldList) > 0) { //添加表栏列表属性
                    foreach ($addFieldList as $addField) {
                        //增加表字段
                        $sql = "ALTER TABLE {$tableName} ADD {$addField}";
                        try {
                            Db::execute($sql);
                        } catch (\Exception $e) {
                            $this->error("增加表字段失败" . $e->getMessage());
                        }
                    }
                }
                if (sizeof($updateFieldList) > 0) { //修改表字段属性
                    foreach ($updateFieldList as $updateField) {
                        //增加表字段
                        $sql = "ALTER TABLE {$tableName} modify {$updateField}";
                        try {
                            Db::execute($sql);
                        } catch (\Exception $e) {
                            $this->error("修改表字段失败");
                        }
                    }
                }
                if (sizeof($addTableFieldData) > 0) { //添加表栏列表数据
                    $dr = (new FormField())->saveAll($addTableFieldData);
                }
                if (sizeof($updateTableFieldData) > 0) { //修改表栏列表数据
                    foreach ($updateTableFieldData as $updateTFieldData) {
                        FormField::update($updateTFieldData);
                    }
                }
                if (sizeof($delFormFieldArr) > 0) { //删除去掉的表字段
                    $delFieldIdArr = array(); //删除数据id
                    foreach ($delFormFieldArr as $delFormField) {
                        //增加表字段
                        $sql = "alter table {$tableName} drop column {$delFormField['name']} ";
                        try {
                            Db::execute($sql);
                            array_push($delFieldIdArr, $delFormField["id"]);
                        } catch (\Exception $e) {
                            $this->error("删除表字段失败");
                        }
                    }
                }
                if (sizeof($delFieldIdArr) > 0) {
                    //删除数据
                    FormField::destroy($delFieldIdArr);
                }
            }
            Cache::clear();
            $this->success();
        }

        $id = $param['id'];
        View::assign('id', $id);
        $pmt = [];
        if (array_key_exists('id', $param)) {
            $formList = $flm->find($id);
            View::assign('formList', $formList);
            $pmt = Db::name('plugin_mail_template')->find($formList['template_id']);
        }
        View::assign("pmt", $pmt);
        return view('edit');
    }

    public function look($page = 1, $pageSize = 100)
    {
        $param = $this->request->param();
        $tableItem = []; //表格项
        $tableHeads = []; //表头
        //table列
        $ffList = \app\common\model\FormField::where(['form_list_id' => $param["formListId"]])->order(["sort_order" => "asc", "create_time" => "asc"])->select();
        foreach ($ffList as $ff) {
            array_push($tableHeads, ['key' => $ff['name'], 'title' => $ff['title']]);
            array_push($tableItem, $ff['name']);
        }

        array_push($tableHeads, ['key' => 'create_time', 'title' => '创建时间']);
        array_push($tableItem, 'create_time');

        if ($this->request->isAjax()) {
            $where = [];
            if (sizeof($tableHeads) > 2 && array_key_exists('keyword', $param) && !empty($param['keyword'])) {
                array_push($where, ["{$tableHeads[0]['key']}|{$tableHeads[1]['key']}", 'like', '%' . trim($param['keyword']) . '%']);
            }

            $formList = FormListModal::find($param["formListId"]); //表单
            $list = Db::table($formList["table_name"])->where($where)->order(["create_time" => "desc", "id" => "desc"])->paginate(['page' => $page, 'list_rows' => $pageSize]);
            $this->success('查询成功', null, $list);
        }

        View::assign("tableHeads", $tableHeads);
        View::assign("formListId", $param["formListId"]);
        View::assign("tableItemStr", implode(',', $tableItem));
        return view();
    }

    // 批量删除
    public function lookDeletes()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            if (array_key_exists("idList", $param)) {
                $idList = json_decode($param['idList']);
                if (sizeof($idList) <= 0) {
                    $this->error("操作失败，请选择对应删除数据");
                }
                try {
                    $formList = FormListModal::find($param["formListId"]); //表单
                    Db::table($formList["table_name"])->delete($idList);
                } catch (\Exception $e) {
                    $this->error("操作失败");
                }
                Cache::clear();
                $this->success('操作成功');
            }
        } else {
            $this->error("请求异常，删除失败！");
        }
    }

    public function detail()
    {
        $param = $this->request->param();
        $id = $param['id'];
        $formListId = $param['formListId'];
        $formList = FormListModal::find($formListId);
        View::assign("id", $id);
        View::assign("formListId", $formListId);
        View::assign("tableName", $formList['table_name']);
        return view();
    }

    public function deleteField()
    {
        $field_id = $this->request->param("field_id");
        if (empty($field_id)) {
            $this->error("操作失败");
        }
        $formField = FormField::find($field_id);
        if ($formField) {
            $formList = \app\common\model\FormList::find($formField['form_list_id']);
            //增加表字段
            $sql = "alter table {$formList['table_name']} drop column {$formField['name']} ";
            Db::execute($sql);
            FormField::destroy($field_id);
            $this->success("操作成功");
        } else {
            $this->success("操作成功", "", "ts");
        }
        $this->error("操作失败");
    }
}
