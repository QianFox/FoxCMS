<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   15:16
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   15:16
 */

namespace app\admin\controller;

use app\admin\util\Field;
use app\common\controller\AdminBase;
use think\facade\Cache;
use think\facade\Db;

// 模型字段
class ModelField extends AdminBase
{
    public function index($page = 1, $pageSize = 1000)
    {
        $page = ['page' => $page, "pageSize" => $pageSize];
        $param = $this->request->param();
        if (!array_key_exists("modelId", $param)) {
            $this->error("参数错误");
        }

        $mr = \app\common\model\ModelRecord::field("nid")->find($param["modelId"]);
        if (!$mr) {
            $this->error("没有对应模型");
        }

        $where = array();
        array_push($where, ['model', '=', $mr->nid]);
        if (array_key_exists('status', $param) && !empty($param['status'])) {
            if ($param['status'] == '禁用') {
                array_push($where, ['status', '=', 0]);
            } else if ($param['status'] == '启用') {
                array_push($where, ['status', '=', 1]);
            }
        }
        if (array_key_exists('keyword', $param) && !empty($param['keyword'])) {
            array_push($where, ['name|title', 'like', '%' . trim($param['keyword']) . '%']);
        }
        $modelField = new \app\common\model\ModelField();
        $list = $modelField->field("id, title, name as field, dtype as type, status as state, is_system as categoryId, is_system, status")->where($where)->paginate(['list_rows' => $pageSize, 'query' => $page]);
        $this->success("操作成功", null, $list);
    }

    public function add()
    {
        $param = $this->request->param();
        if (!array_key_exists("modelId", $param) || empty($param["modelId"])) {
            $this->error("参数错误");
        }
        //查询模型
        $modelId = $param["modelId"]; //模型id
        $modelRecord = \app\common\model\ModelRecord::find($modelId);
        if (!$modelRecord) {
            $this->error("模型不存在");
        }

        if (empty($param['dtype']) || empty($param['title']) || empty($param['name'])) {
            $this->error("缺少必填信息！");
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
        $table = $modelRecord->table;
        $sql = "ALTER TABLE `$table` ADD $ntabsql";

        try {
            $ex =  Db::execute($sql);
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), 'Column already exists') !== false) {
                $this->error(config("Constant.column_already_exists"));
            } else {
                $this->error("操作失败");
            }
        }
        /*保存新增字段的记录*/
        $newData = array(
            'model'      => $modelRecord->nid,
            'dfvalue'     => $dfvalue,
            'maxlength'   => $maxlength,
            'define'      => $buideType,
            'dtype'      => $param['dtype'],
            'ifsystem'    => 0,
            'name'  => $param['name'],
            'remark'  => $param['remark'],
            'title'  => $param['title'],
        );
        (new \app\common\model\ModelField())->save($newData);
        Cache::clear();
        $this->success("操作成功", null, $ex);
    }

    // 查询模型字段
    public function getModelField($id)
    {

        $modelField = \app\common\model\ModelField::find($id);
        $this->success("查询成功", null, $modelField);
    }

    public function edit()
    {
        $param = $this->request->param();
        if (!array_key_exists("op", $param)) {
            if (!array_key_exists("modelId", $param) || empty($param["modelId"])) {
                $this->error("参数错误");
            }
            //查询模型
            $modelId = $param["modelId"]; //模型id
            $modelRecord = \app\common\model\ModelRecord::find($modelId);
            if (!$modelRecord) {
                $this->error("模型不存在");
            }
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
            $old_name = $param["oldName"];
            $fieldinfos = (new Field())->GetFieldMake($param['dtype'], $param['name'], $dfvalue, $param['title']);
            $ntabsql    = $fieldinfos[0];
            $buideType  = $fieldinfos[1];
            $maxlength  = $fieldinfos[2];
            $table = $modelRecord->table;
            $sql = " ALTER TABLE `$table` CHANGE COLUMN `{$old_name}` $ntabsql ";
            try {
                $ex =  Db::execute($sql);
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'Column already exists') !== false) {
                    $this->error(config("Constant.column_already_exists"));
                } else {
                    $this->error("操作失败");
                }
            }
            /*保存更新字段的记录*/
            $newData = array(
                'id' => $param['id'],
                'dtype'      => $param['dtype'],
                'name'  => $param['name'],
                'remark'  => $param['remark'],
                'title'  => $param['title'],
                'dfvalue'     => $dfvalue,
                'maxlength'   => $maxlength,
                'define'      => $buideType,
                'update_time' => date('Y-m-d H:i:s', time())
            );
        } else {
            $newData = array(
                'id' => $param['id'],
                'status'      => $param['status'],
                'update_time' => date('Y-m-d H:i:s', time())
            );
        }
        (new \app\common\model\ModelField())->update($newData);
        Cache::clear();
        $this->success("操作成功", null, null);
    }

    public function delete()
    {
        $param = $this->request->param();
        $id = $param['id'];
        $modelField = \app\common\model\ModelField::find($id);
        if ($modelField) {
            if ($modelField->is_system == 1) { //是否系统字段
                $this->error('系统字段不能删除');
            }
            $table = "fox_" . $modelField->model;
            $sql = "ALTER TABLE $table DROP COLUMN $modelField->name";
            $ex =  Db::execute($sql);
            \app\common\model\ModelField::destroy($id); //删除记录
            Cache::clear();
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
        $modelField = new \app\common\model\ModelField();
        try {
            $modelField->whereIn("id", implode(",", $idList))->update(["status" => $status]);
        } catch (\Exception $e) {
            $this->error('操作失败,' . $e->getMessage());
        }
        $this->success('操作成功');
    }

    public function deletes()
    {
        $param = $this->request->param();
        if (array_key_exists("idList", $param)) {
            $idList = json_decode($param['idList']);
            if (sizeof($idList) <= 0) {
                $this->error("操作失败，请选择对应启用数据");
            }
            try {
                $modelField = new \app\common\model\ModelField();
                $modelFieldList = $modelField->whereIn("id", implode(",", $idList))->select();
                $table_prefix = config("database.connections.mysql.prefix");
                foreach ($modelFieldList as $key => $mf) {
                    if ($mf->is_system == 1) { //是否系统字段
                        $this->error('系统字段不能删除');
                    }
                    $table = $table_prefix . $mf->model;
                    $sql = "ALTER TABLE {$table} DROP COLUMN {$mf['name']}";
                    Db::execute($sql);
                    \app\common\model\ModelField::destroy($mf['id']);
                }
            } catch (\Exception $e) {
                $this->error('操作失败,' . $e->getMessage());
            }
        }
        $this->success("操作成功");
    }
}