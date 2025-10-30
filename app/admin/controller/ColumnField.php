<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   14:38
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   14:38
 */

namespace app\admin\controller;

use app\admin\util\Field;
use app\common\controller\AdminBase;
use app\common\model\AuthRule;
use app\common\model\FieldType;
use think\facade\Cache;
use think\facade\Db;
use think\facade\View;

// 栏目字段
class ColumnField extends AdminBase
{
    public function initialize()
    {
        parent::initialize();

        $columnList = \app\common\model\Column::field("id,name")->where(["status" => 1, "lang" => $this->getMyLang()])->select();
        View::assign("columnList", $columnList);
    }

    public function index($page = 1, $pageSize = 10)
    {
        $param = $this->request->param();
        if (array_key_exists('bcid', $param)) {
            View::assign('bcid', $param['bcid']);
        }
        if ($this->request->isAjax()) {
            $where = array();
            if (array_key_exists('status', $param) && !empty($param['status'])) {
                if ($param['status'] == '禁用') {
                    array_push($where, ['status', '=', 0]);
                } else if ($param['status'] == '启用') {
                    array_push($where, ['status', '=', 1]);
                }
            }
            if (array_key_exists('keyword', $param) && !empty($param['keyword'])) {
                array_push($where, ['title|name', 'like', '%' . $param['keyword'] . '%']);
            }
            $columnField = new \app\common\model\ColumnField();
            $list = $columnField->where($where)->order('create_time', 'desc')->paginate(['page' => $page, 'list_rows' => $pageSize]);
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
        array_push($breadcrumb, ['id' => '', 'title' => '添加栏目字段', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/ColumnField/add', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);
        View::assign("columnId", $columnId);

        if ($this->request->isAjax()) {
            $param = $this->request->post();
            if (empty($param['dtype']) || empty($param['title']) || empty($param['name'])) {
                $this->error("缺少必填信息！");
            }
            if (empty($param['column_ids'])) {
                $this->error("请选择所属栏目！");
            }
            $fColumns = \app\common\model\Column::field("nid")->whereIn("id", $param['column_ids'])->select()->toArray();
            if (sizeof($fColumns) <= 0) {
                $this->error("操作失败没找到栏目信息！");
            }
            $nids = [];
            foreach ($fColumns as $key => $col) {
                array_push($nids, $col['nid']);
            }
            $columnList = \app\common\model\Column::field("id")->whereIn('nid', implode(",", $nids))->select()->toArray();
            $column_ids = [];
            foreach ($columnList as $column) {
                array_push($column_ids, $column['id']);
            }

            /*去除中文逗号，过滤左右空格与空值*/
            $dfvalue    = str_replace('，', ',', $param['dfvalue']);
            if (in_array($param['dtype'], ['radio', 'checkbox', 'select', 'region'])) {
                $pattern    = ['"', '\'', ';', '&', '?', '='];
                $dfvalue    = func_preg_replace($pattern, '', $dfvalue);
            }
            $dfvalueArr = explode(',', $dfvalue);
            foreach ($dfvalueArr as $key => $val) {
                $tmp_val = trim($val);
                if (empty($tmp_val)) {
                    unset($dfvalueArr[$key]);
                    continue;
                }
                $dfvalueArr[$key] = $tmp_val;
            }
            $dfvalueArr = array_unique($dfvalueArr);
            $dfvalue = implode(',', $dfvalueArr);
            /*--end*/

            $fieldinfos = (new Field())->GetFieldMake($param['dtype'], $param['name'], $dfvalue, $param['title']);
            $ntabsql    = $fieldinfos[0];
            $buideType  = $fieldinfos[1];
            $maxlength  = $fieldinfos[2];
            $table = 'fox_column';
            $sql = "ALTER TABLE `$table` ADD $ntabsql";
            try {
                $ex =  Db::execute($sql);
            } catch (\Exception $e) {
                if ($e->getCode() == 10501) {
                    $this->error("栏目字段已经存在");
                } else {
                    $this->error("添加栏目字段失败" . $e->getMessage());
                }
            }

            /*保存新增字段的记录*/
            $newData = array(
                'dfvalue'     => $dfvalue,
                'maxlength'   => $maxlength,
                'define'      => $buideType,
                'dtype'       => $param['dtype'],
                'ifsystem'    => 0,
                'name'        => $param['name'],
                'remark'      => $param['remark'],
                'title'       => $param['title'],
                'column_ids'  => implode(",", $column_ids),
            );
            (new \app\common\model\ColumnField())->save($newData);
            Cache::clear();
            $this->success("操作成功", null, $ex);
        }

        $fieldTypeList = FieldType::field("name,title,status")->where("status", 1)->select();
        View::assign("fieldTypeList", $fieldTypeList); //字段类型数据

        return  view('add');
    }

    public function edit()
    {
        $param = $this->request->param();
        $columnId = $param['columnId']; //栏目id
        $id = $param['id'];
        $authRule = AuthRule::find($columnId);
        $bcidStr = str_replace(",", "_", $authRule->tier);
        $breadcrumb = AuthRule::getBreadcrumb($bcidStr);
        array_push($breadcrumb, ['id' => '', 'title' => '编辑栏目字段', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/ColumnField/edit', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);
        View::assign("columnId", $columnId);
        $columnField = \app\common\model\ColumnField::find($id);
        if ($this->request->isAjax()) {
            $column_ids = explode(",", $columnField['column_ids']); //栏目ids
            if (empty($param['dtype']) || empty($param['title']) || empty($param['name'])) {
                $this->error("缺少必填信息！");
            }
            if (empty($param['column_ids'])) {
                $this->error("请选择所属栏目！");
            }
            /*去除中文逗号，过滤左右空格与空值*/
            $dfvalue    = str_replace('，', ',', $param['dfvalue']);
            if (in_array($param['dtype'], ['radio', 'checkbox', 'select', 'region'])) {
                $pattern    = ['"', '\'', ';', '&', '?', '='];
                $dfvalue    = str_replace($pattern, '', $dfvalue);
            }
            $dfvalueArr = explode(',', $dfvalue);
            foreach ($dfvalueArr as $key => $val) {
                $tmp_val = trim($val);
                if (empty($tmp_val)) {
                    unset($dfvalueArr[$key]);
                    continue;
                }
                $dfvalueArr[$key] = $tmp_val;
            }
            $dfvalueArr = array_unique($dfvalueArr);
            $dfvalue = implode(',', $dfvalueArr);
            /*--end*/
            $old_name = $param["old_name"];
            $fieldinfos = (new Field())->GetFieldMake($param['dtype'], $param['name'], $dfvalue, $param['title']);
            $ntabsql    = $fieldinfos[0];
            $buideType  = $fieldinfos[1];
            $maxlength  = $fieldinfos[2];
            $table = 'fox_column';
            $sql = " ALTER TABLE `$table` CHANGE COLUMN `{$old_name}` $ntabsql ";
            try {
                $ex =  Db::execute($sql);
            } catch (\Exception $e) {
                $this->error("保存栏目字段失败");
            }
            $new_column_ids = explode(",", $param['column_ids']); //新栏目ids
            $column_idsArr = array_unique(array_merge($column_ids, $new_column_ids));
            $column_ids = implode(",", $column_idsArr);
            /*保存更新字段的记录*/
            $newData = array(
                'id'          => $param['id'],
                'dtype'       => $param['dtype'],
                'name'        => $param['name'],
                'remark'      => $param['remark'],
                'title'       => $param['title'],
                'dfvalue'     => $dfvalue,
                'maxlength'   => $maxlength,
                'define'      => $buideType,
                'column_ids'  => $column_ids,
                'update_time' => date('Y-m-d H:i:s', time())
            );

            (new \app\common\model\ColumnField())->update($newData);
            Cache::clear();
            $this->success("操作成功", null, $sql);
        }
        $columnIds = [];
        if ($columnField) {
            if (!empty($columnField["column_ids"])) {
                $columnIds = explode(",", $columnField["column_ids"]);
            }
        }
        View::assign("columnIds", $columnIds);

        View::assign("columnField", $columnField);
        $dtype = $columnField->dtype;
        $disableDtypes = (new Field())->convertField($dtype); //不允许转换字段类型
        $fieldTypeList = FieldType::field("name,title,status")->where("status", 1)->select();
        foreach ($fieldTypeList as $key => $fieldType) {
            $fieldType['isDisable'] = in_array($fieldType['name'], $disableDtypes); //存在就禁用
        }
        View::assign("fieldTypeList", $fieldTypeList); //字段类型数据
        return  view('edit');
    }

    public function delete()
    {
        $param = $this->request->param();
        $id = $param['id'];
        $columnField = \app\common\model\ColumnField::find($id);
        if ($columnField) {
            if ($columnField->is_system == 1) { //是否系统字段
                $this->error('系统字段不能删除');
            }
            $table = "fox_column";
            $sql = "ALTER TABLE $table DROP COLUMN $columnField->name";
            $ex =  Db::execute($sql);
            \app\common\model\ColumnField::destroy($id); //删除记录
            $this->success('删除成功', null, $ex);
        } else {
            $this->error('参数错误');
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
        $columnField = new \app\common\model\ColumnField();
        try {
            $columnField->whereIn("id", implode(",", $idList))->update(["status" => $status]);
        } catch (\Exception $e) {
            $this->error('操作失败,' . $e->getMessage());
        }
        $this->success('操作成功');
    }

    public function deletes()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            if (array_key_exists("idList", $param)) {
                $idList = json_decode($param['idList']);
                $count = 0;
                $columnField = new \app\common\model\ColumnField();
                $columnField->startTrans();
                $table = "fox_column";
                foreach ($idList as $key => $id) {
                    $columnField = $columnField->find($id);
                    if ($columnField) {
                        if ($columnField->is_system == 1) { //是否系统字段
                            continue;
                        }
                        $sql = "ALTER TABLE $table DROP COLUMN $columnField->name";
                        $ex =  Db::execute($sql);
                        $r = $columnField->destroy($id);
                        if ($r) {
                            $count++;
                        }
                    }
                }
                if (sizeof($idList) == $count) {
                    $columnField->commit();
                    $this->success('操作成功');
                } else {
                    $columnField->rollback();
                    $this->error('操作失败');
                }
            }
        }
    }
}