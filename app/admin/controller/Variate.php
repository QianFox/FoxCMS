<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   15:39
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   15:39
 */

namespace app\admin\controller;

use app\admin\util\Field;
use app\common\controller\AdminBase;
use app\common\model\AuthRule;
use app\common\model\DictType as DictTypeModel;
use app\common\model\FieldType;
use app\common\model\VariateField;
use think\facade\Cache;
use think\facade\Db;
use think\facade\View;

// 变量
class Variate extends AdminBase
{
    private $variateList = [];

    public function initialize()
    {
        parent::initialize();
        $this->variateList = DictTypeModel::where('dict_type', 'variate')->with('dictDatas')->find()->dictDatas; //变量所属组
        View::assign('variates', $this->variateList);
    }

    public function index($page = 1, $pageSize = 100)
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
            if (array_key_exists('keyword', $param) && !empty(trim($param['keyword']))) {
                array_push($where, ['title|name', 'like', '%' . trim($param['keyword']) . '%']);
            }
            $list = (new VariateField())->where($where)->order('create_time', 'desc')->paginate(['page' => $page, 'list_rows' => $pageSize]);
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
        array_push($breadcrumb, ['id' => '', 'title' => '添加变量', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/VariateField/add', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);
        View::assign("columnId", $columnId);

        if ($this->request->isAjax()) {
            $param = $this->request->post();
            if (empty($param['dtype']) || empty($param['title']) || empty($param['name'])) {
                $this->error("缺少必填信息！");
            }
            /*去除中文逗号，过滤左右空格与空值*/
            $dfvalue = str_replace('，', ',', $param['dfvalue']);
            if (in_array($param['dtype'], ['radio', 'checkbox', 'select', 'region'])) {
                $pattern = ['"', '\'', ';', '&', '?', '='];
                $dfvalue = func_preg_replace($pattern, '', $dfvalue);
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
            $table = 'fox_' . $param['group'];
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
            //调用标签
            $call_tag = "{fox:{$param['group']} name='{$param['name']}'/}";

            /*保存新增字段的记录*/
            $newData = array(
                'call_tag'    => $call_tag,
                'group'       => $param['group'],
                'dfvalue'     => $dfvalue,
                'maxlength'   => $maxlength,
                'define'      => $buideType,
                'dtype'       => $param['dtype'],
                'ifsystem'    => 0,
                'name'    => $param['name'],
                'remark'  => $param['remark'],
                'title'   => $param['title'],
            );
            (new \app\common\model\VariateField())->save($newData);
            Cache::clear();
            $this->success("操作成功", null, $ex);
        }

        $fieldTypeList = FieldType::field("name,title,status")->where("status", 1)->where([["name", "<>", "pics"]])->select(); //不查图片集字段
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
        array_push($breadcrumb, ['id' => '', 'title' => '编辑变量', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/VariateField/edit', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);
        View::assign("columnId", $columnId);

        if ($this->request->isAjax()) {
            if (empty($param['dtype']) || empty($param['title']) || empty($param['name'])) {
                $this->error("缺少必填信息！");
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
            $table = 'fox_' . $param['group'];
            $sql = " ALTER TABLE `$table` CHANGE COLUMN `{$old_name}` $ntabsql ";
            try {
                $ex =  Db::execute($sql);
            } catch (\Exception $e) {
                $this->error("保存栏目字段失败");
            }
            /*保存更新字段的记录*/
            $newData = array(
                'id' => $param['id'],
                'group'       => $param['group'],
                'dtype'       => $param['dtype'],
                'name'        => $param['name'],
                'remark'      => $param['remark'],
                'title'       => $param['title'],
                'dfvalue'     => $dfvalue,
                'maxlength'   => $maxlength,
                'define'      => $buideType,
                'update_time' => date('Y-m-d H:i:s', time())
            );

            (new \app\common\model\VariateField())->update($newData);
            Cache::clear();
            $this->success("操作成功", null, $sql);
        }
        $variateField = \app\common\model\VariateField::find($id);
        $dtype = $variateField->dtype;
        $disableDtypes = (new Field())->convertField($dtype); //不允许转换字段类型
        $fieldTypeList = FieldType::field("name,title,status")->where("status", 1)->where([["name", "<>", "pics"]])->select(); //不查图片集字段
        foreach ($fieldTypeList as $key => $fieldType) {
            $fieldType['isDisable'] = in_array($fieldType['name'], $disableDtypes); //存在就禁用
        }
        View::assign("fieldTypeList", $fieldTypeList); //字段类型数据

        $groupText = "";
        foreach ($this->variateList as $dl) {
            if ($variateField['group'] == $dl['dict_value']) {
                $groupText = $dl['dict_label'];
                break;
            }
        }
        $variateField["groupText"] = $groupText;

        View::assign("variateField", $variateField);
        return  view('edit');
    }

    // 编辑状态
    public function statusChange()
    {
        $param = $this->request->param();
        /*保存更新字段的记录*/
        $newData = array(
            'id' => $param['id'],
            'status'      => $param['status'],
            'update_time' => date('Y-m-d H:i:s', time())
        );
        (new \app\common\model\VariateField())->update($newData);
        $this->success("操作成功");
    }

    // 查询变量
    public function getVariate($id)
    {
        $variate =  VariateField::find($id);
        $this->success("操作成功", "", $variate);
    }

    public function delete()
    {
        $param = $this->request->param();
        $id = $param['id'];
        $variateField = \app\common\model\VariateField::find($id);
        if ($variateField) {
            if ($variateField->is_system == 1) { //是否系统字段
                $this->error('系统字段不能删除');
            }
            $table = "fox_" . $variateField['group'];
            $sql = "ALTER TABLE $table DROP COLUMN $variateField->name";
            $ex =  Db::execute($sql);
            \app\common\model\VariateField::destroy($id); //删除记录
            $this->success('删除成功', null, $ex);
        } else {
            $this->error('参数错误');
        }
    }

    // 修改状态
    public function updateStatus()
    {
        $param = $this->request->param();
        $idList = json_decode($param['idList']);
        if (sizeof($idList) <= 0) {
            $this->error("操作失败，请选择对应启用数据");
        }
        $status = intval($param['status']);
        $variateField = new VariateField();
        try {
            $variateField->whereIn("id", implode(",", $idList))->update(["status" => $status]);
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
                $variateFieldM = new \app\common\model\VariateField();
                $variateFieldM->startTrans();
                foreach ($idList as $key => $id) {
                    $variateField = $variateFieldM->find($id);
                    if ($variateField) {
                        if ($variateField->is_system == 1) { //是否系统字段
                            continue;
                        }
                        $table = "fox_" . $variateField['group'];
                        $sql = "ALTER TABLE $table DROP COLUMN $variateField->name";
                        $ex =  Db::execute($sql);
                        $r = $variateField->destroy($id);
                        if ($r) {
                            $count++;
                        }
                    }
                }
                if (sizeof($idList) == $count) {
                    $variateFieldM->commit();
                    $this->success('操作成功');
                } else {
                    $variateFieldM->rollback();
                    $this->error('操作失败');
                }
            }
        }
    }

    // 查询自定义变量
    public function getField()
    {
        $param = $this->request->param();
        $where = ['status' => 1, "is_system" => 0, "group" => $param['group']];
        $variateFieldList = \app\common\model\VariateField::field('dtype,name')->where($where)->order(["sort_order" => "desc", "create_time" => "asc"])->select();
        $this->success("查询成功", '', $variateFieldList);
    }
}