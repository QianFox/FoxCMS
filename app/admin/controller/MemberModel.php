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

use app\admin\util\TableUtil;
use app\common\controller\AdminBase;
use app\common\model\AuthRule;
use app\common\model\DictType as DictTypeModel;
use app\common\model\MemberModelField;
use think\App;
use think\facade\Cache;
use think\facade\Db;
use think\facade\View;

// 会员模型管理
class MemberModel extends AdminBase
{
    private $memberTypeList = []; //会员类型列表
    private $noFields = ['apassword']; //排除字段

    public function __construct(App $app)
    {
        parent::__construct($app);
        $memberTypes = DictTypeModel::where('dict_type', 'member_type')->with('dictDatas')->find()->dictDatas;
        $this->memberTypeList = $memberTypes;
        View::assign("memberTypes", $memberTypes);
    }

    public function index($page = 1, $pageSize = 10)
    {
        $param = $this->request->param();
        if (array_key_exists('bcid', $param)) {
            View::assign('bcid', $param['bcid']);
        }
        if ($this->request->isAjax()) {

            $where[] = ['is_delete', '=', 0];
            if (!empty($param['status'])) {
                $where[] = ['status', '=', $param['status']];
            }
            if (array_key_exists('keyword', $param) && !empty($param['keyword'])) {
                $where[] = ['name', 'like', '%' . $param['keyword'] . '%'];
            }
            $memberModel = new \app\common\model\MemberModel();
            $list = $memberModel->where($where)->order('create_time', 'desc')->paginate(['page' => $page, 'list_rows' => $pageSize]);
            $this->success('查询成功', null, $list);
        }
        return view('index');
    }

    public function add()
    {
        $prefix = "fox";
        $tablePrefix = "member";
        if ($this->request->isAjax()) {
            $param = $this->request->post();
            if (empty($param['nid']) || empty($param['name'])) {
                $this->error("缺少必填信息！");
            }
            $nid = $param['nid'];
            $mr = \app\common\model\MemberModel::where('nid', $tablePrefix . "_" . $nid)->find();
            if ($mr || is_exist_table($prefix . "_" . $tablePrefix . "_" . $nid)) {
                $this->error("数据表名已存在");
            }

            $fieldList = []; //表栏列表
            $mmfList = \app\common\model\MemberModelField::where(['member_model_id' => 0, "status" => 1])->select(); //查询会员模型公用字段
            foreach ($mmfList as $mmf) {
                if (!in_array($mmf['name'], $this->noFields)) {
                    $default = "''";
                    if (!empty($mmf['dfvalue'])) {
                        $default = "'{$mmf['dfvalue']}'";
                    } else {
                        $num = substr_count($mmf['define'], 'int');
                        if ($mmf['define'] == 'datetime') {
                            $default = null;
                        } elseif ($num > 0) {
                            $default = 0;
                        }
                    }
                    array_push($fieldList, "`{$mmf['name']}` {$mmf['define']} DEFAULT {$default} COMMENT '{$mmf["remark"]}'");
                }
            }
            /*保存新增字段的记录*/
            array_push($fieldList, "`create_time` datetime DEFAULT NULL COMMENT '创建时间'");
            array_push($fieldList, "`update_time` datetime DEFAULT NULL COMMENT '更新时间'");

            $remark = $param['name'];
            $tableName = $prefix . "_" . $tablePrefix . "_" . $nid;
            $r = TableUtil::createTable($tableName, $fieldList, $remark);
            if ($r) {
                $memberModel = [
                    "nid" => $tablePrefix . "_" . $nid,
                    "name" => $param["name"],
                    "table" => $tableName,
                    "status" => $param["status"],
                    "member_type" => $param["member_type"],
                    "member_type_desc" => $param["member_type_desc"],
                ];
                (new \app\common\model\MemberModel())->save($memberModel);
                Cache::clear(); //清空缓存
                $this->success("操作成功");
            } else {
                $this->error("操作失败", '', $r);
            }
        }
        //功能面包屑
        $param = $this->request->param();
        $bcid = $param['bcid'];
        View::assign('bcid', $bcid);
        $columnId = $param['columnId']; //栏目id
        $authRule = AuthRule::find($columnId);
        $bcidStr = str_replace(",", "_", $authRule->tier);
        $breadcrumb = AuthRule::getBreadcrumb($bcidStr);
        array_push($breadcrumb, ['id' => '', 'title' => '添加会员模型', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/MemberModel/add', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);
        View::assign("columnId", $columnId);
        View::assign("tableHead", $prefix . "_" . $tablePrefix . "_");
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
        array_push($breadcrumb, ['id' => '', 'title' => '编辑模型', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/MemberModel/edit', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);
        View::assign("columnId", $columnId);

        $memberModel = \app\common\model\MemberModel::find($id);

        $memberTypeText = "";
        foreach ($this->memberTypeList as $dl) {
            if ($memberModel['member_type'] == $dl['dict_value']) {
                $memberTypeText = $dl['dict_label'];
                break;
            }
        }
        $memberModel['memberTypeText'] = $memberTypeText;
        View::assign("memberModel", $memberModel);
        return  view('edit');
    }

    public function delete()
    {
        $param = $this->request->param();
        $id = $param['id'];
        if (!array_key_exists('id', $param)) {
            $this->error('参数错误');
        }
        $mr = \app\common\model\MemberModel::find($id);
        if ($mr['is_system'] == 1) {
            $this->error("系统模型不允许删除");
        }
        if ($mr) {
            \app\common\model\MemberModel::destroy($id); //删除
            $table = $mr->table;
            $sql = "DROP TABLE $table";
            Db::execute($sql);
            MemberModelField::where('member_model_id', $id)->delete();
            $this->success('操作成功');
        } else {
            $this->error('未找到模型');
        }
    }

    public function deletes()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            if (array_key_exists("idList", $param)) {
                $idList = json_decode($param['idList']);
                $r = \app\common\model\MemberModel::destroy($idList); //批量删除
                MemberModelField::whereIn('member_model_id', $idList)->delete();
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
        $memberModel = new \app\common\model\MemberModel();
        try {
            $memberModel->whereIn("id", implode(",", $idList))->update(["status" => $status]);
        } catch (\Exception $e) {
            $this->error('操作失败,' . $e->getMessage());
        }
        $this->success('操作成功');
    }
}