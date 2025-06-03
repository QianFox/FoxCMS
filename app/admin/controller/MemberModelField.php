<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   15:09
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   15:09
 */

namespace app\admin\controller;

use app\admin\util\Field;
use app\common\controller\AdminBase;
use think\facade\Cache;
use think\facade\Db;

// 会员模型字段
class MemberModelField extends AdminBase
{
    public function index($page = 1, $pageSize = 10)
    {
        $page = ['page' => $page, "pageSize" => $pageSize];
        $param = $this->request->param();
        if (!array_key_exists("modelId", $param)) {
            $this->error("参数错误");
        }
        $mm = \app\common\model\MemberModel::field("nid")->find($param["modelId"]);
        if (!$mm) {
            $this->error("没有对应模型");
        }

        $where = array();
        array_push($where, ['member_model_id', '=', $param["modelId"]]);
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
        $MemberModelField = new \app\common\model\MemberModelField();
        $list = $MemberModelField->field("id, title, name as field, title as type, status as state, is_system as categoryId, is_system, status")->where($where)->paginate(['list_rows' => $pageSize, 'query' => $page]);
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
        $memberModel = \app\common\model\MemberModel::find($modelId);
        if (!$memberModel) {
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
        $table = $memberModel->table;
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
            'member_model_id'      => $modelId,
            'dfvalue'     => $dfvalue,
            'maxlength'   => $maxlength,
            'define'      => $buideType,
            'dtype'      => $param['dtype'],
            'ifsystem'    => 0,
            'name'  => $param['name'],
            'remark'  => $param['remark'],
            'title'  => $param['title'],
        );
        (new \app\common\model\MemberModelField())->save($newData);

        Cache::clear(); //清空缓存
        $this->success("操作成功", null, $ex);
    }

    // 查询模型字段
    public function getMemberModelField($id)
    {
        $MemberModelField = \app\common\model\MemberModelField::find($id);
        $this->success("查询成功", null, $MemberModelField);
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
            $memberModel = \app\common\model\MemberModel::find($modelId);
            if (!$memberModel) {
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
            $table = $memberModel->table;
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
        (new \app\common\model\MemberModelField())->update($newData);
        Cache::clear(); //清空缓存
        $this->success("操作成功", null, null);
    }

    public function delete()
    {
        $param = $this->request->param();
        $id = $param['id'];
        $MemberModelField = \app\common\model\MemberModelField::find($id);
        if ($MemberModelField) {
            if ($MemberModelField->is_system == 1) { //是否系统字段
                $this->error('系统字段不能删除');
            }
            $memberModelId = $MemberModelField['member_model_id'];
            $mm = \app\common\model\MemberModel::find($memberModelId);
            $table = $mm->table;
            $sql = "ALTER TABLE $table DROP COLUMN $MemberModelField->name";
            $ex =  Db::execute($sql);
            \app\common\model\MemberModelField::destroy($id); //删除记录
            Cache::clear(); //清空缓存
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
        $memberModelField = new \app\common\model\MemberModelField();
        try {
            $memberModelField->whereIn("id", implode(",", $idList))->update(["status" => $status]);
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
                            $this->error('系统字段不能删除');
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
                    Cache::clear(); //清空缓存
                    $this->success('操作成功');
                } else {
                    $columnField->rollback();
                    $this->error('操作失败');
                }
            }
        }
    }
}