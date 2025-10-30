<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   15:06
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   15:06
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\model\AuthRule;
use app\common\model\DictType as DictTypeModel;
use think\App;
use think\facade\Db;
use think\facade\View;

// 会员列表
class Member extends AdminBase
{
    private $deadlineList = []; //会员期限列表
    private $mmfList = []; //会员模型字段数据
    private $noFields = ['avatar', 'nickname', 'password', 'apassword']; //排除字段
    private $noFieldVals = ['password', 'apassword']; //排除字段值

    public function __construct(App $app)
    {
        parent::__construct($app);
        $deadlines = DictTypeModel::where('dict_type', 'deadline')->with('dictDatas')->find()->dictDatas;
        $this->deadlineList = $deadlines;
        View::assign("deadlines", $deadlines);
        $memberLevel = new \app\common\model\MemberLevel();
        $where[] = ["status", '=', 1];
        $memberLevelList = $memberLevel->where($where)->order('create_time', 'desc')->select();
        View::assign("memberLevels", $memberLevelList);

        $tableItem = []; //表格项
        $tableHeads = [];
        //table列
        $this->mmfList = \app\common\model\MemberModelField::where(['member_model_id' => 0, "status" => 1])->order("sort_order", "desc")->select();
        foreach ($this->mmfList as $mmf) {
            if (!in_array($mmf['name'], $this->noFields)) {
                array_push($tableHeads, ['key' => $mmf['name'], 'title' => $mmf['title']]);
                array_push($tableItem, $mmf['name']);
            }
        }
        array_push($tableHeads, ['key' => 'memberName', 'title' => '会员模型名称']);
        array_push($tableItem, "memberName");
        View::assign("tableHeads", $tableHeads);
        View::assign("tableItemStr", implode(',', $tableItem));
    }

    public function index($page = 1, $pageSize = 100)
    {
        $param = $this->request->param();
        if (array_key_exists('bcid', $param)) {
            View::assign('bcid', $param['bcid']);
        }
        if ($this->request->isAjax()) {
            $where = [];
            if (array_key_exists('keyword', $param) && !empty($param['keyword'])) {
                array_push($where, ['a.nickname|a.memberlevel|a.phone', 'like', '%' . trim($param['keyword']) . '%']);
            }

            //获取公用字段
            $mmfArr = ["id"];
            if (sizeof($this->mmfList) > 0) {
                foreach ($this->mmfList as $mmf) {
                    if (!in_array($mmf['name'], $this->noFieldVals)) {
                        array_push($mmfArr, $mmf['name']);
                    }
                }
            }
            $dataList = []; //数据集合
            //查询所有模型 生成
            $mmList = \app\common\model\MemberModel::where(['is_delete' => 0, 'status' => 1])->select();
            if (sizeof($mmList) > 0) {
                $commonFiled = implode(",", $mmfArr);
                $sqlArr = [];
                foreach ($mmList as $key => $mmf) {
                    if ($key > 0) {
                        $commonFiledSMore = $commonFiled . ',' . "'{$mmf['nid']}' as nid" . ',' . "'{$mmf['name']}' as memberName";
                        array_push($sqlArr, "SELECT {$commonFiledSMore} FROM {$mmf['table']}");
                    }
                }
                $commonFiledFirst = $commonFiled . ',' . "'{$mmList[0]['nid']}' as nid" . ',' . "'{$mmList[0]['name']}' as memberName";
                $tableName = $mmList[0]['table'];
                $dataSql = Db::table($tableName)->field($commonFiledFirst)->union($sqlArr)->buildSql();
                $sql = "({$dataSql})";
                $dataList = Db::table($sql . " as a")->where($where)->paginate(['page' => $page, 'list_rows' => $pageSize]);
            }
            $this->success('查询成功', null, $dataList);
        }
        return view('index');
    }

    // 获取模型类型
    private function getMemberTypeText($memberType)
    {
        $memberTypeText = "";
        foreach ($this->memberTypeList as $dl) {
            if ($memberType == $dl['dict_value']) {
                $memberTypeText = $dl['dict_label'];
                break;
            }
        }
        return $memberTypeText;
    }

    public function add()
    {
        $param = $this->request->param();
        if ($this->request->isAjax()) {
            $param = $this->request->post();
            if (array_key_exists("nid", $param) && !empty($param['nid'])) {
                $nid = $param["nid"];
                unset($param['nid']);
                unset($param['apassword']);
                $param['create_time'] = date('Y-m-d H:i:s');
                $param['update_time'] = $param['create_time'];
                Db::name($nid)->save($param);
                $this->success("操作成功");
            } else {
                $this->error("会员模型不能为空");
            }
        }
        $columnId = $param['columnId']; //栏目id
        View::assign("columnId", $columnId);
        //面包屑
        $authRule = AuthRule::find($columnId);
        $bcidStr = str_replace(",", "_", $authRule->tier);
        $breadcrumb = AuthRule::getBreadcrumb($bcidStr);
        array_push($breadcrumb, ['id' => '', 'title' => '新增会员', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/Member/add', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);
        //会员模型
        $where = ['is_delete' => 0, 'status' => 1];
        $mmList = (new \app\common\model\MemberModel())->field("nid, name")->where($where)->order('create_time', 'desc')->select();
        View::assign("mmList", $mmList); //模型数据
        //会员级别

        return  view('add');
    }

    public function edit()
    {
        $param = $this->request->param();
        if ($this->request->isAjax()) {
            $param = $this->request->post();
            if (array_key_exists("id", $param) && array_key_exists("nid", $param)) {
                $nid = $param["nid"];
                unset($param['nid']);
                unset($param['apassword']);
                if (empty($param['password'])) {
                    unset($param['password']);
                }
                $param['update_time'] = date('Y-m-d H:i:s');
                Db::name($nid)->update($param);
                $this->success("操作成功");
            } else {
                $this->error("修改缺少必要条件");
            }
        }
        $columnId = $param['columnId']; //栏目id
        $id = $param['id'];
        $nid = $param['nid'];
        $authRule = AuthRule::find($columnId);
        $bcidStr = str_replace(",", "_", $authRule->tier);
        $breadcrumb = AuthRule::getBreadcrumb($bcidStr);
        array_push($breadcrumb, ['id' => '', 'title' => '编辑会员', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/Member/edit', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);
        View::assign("id", $id);
        View::assign("nid", $nid);
        View::assign("columnId", $columnId);

        //查询模型
        $mm = \app\common\model\MemberModel::field("id")->where(['nid' => $nid])->find();
        View::assign("member_model_id", $mm["id"]);
        return  view('edit');
    }

    public function detail()
    {
        $param = $this->request->param();
        $columnId = $param['columnId']; //栏目id
        $id = $param['id'];
        $nid = $param['nid'];
        //面包屑
        $authRule = AuthRule::find($columnId);
        $bcidStr = str_replace(",", "_", $authRule->tier);
        $breadcrumb = AuthRule::getBreadcrumb($bcidStr);
        array_push($breadcrumb, ['id' => '', 'title' => '会员详情', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/Member/detail', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);
        View::assign("id", $id);
        View::assign("nid", $nid);
        View::assign("columnId", $columnId);
        //查询模型
        $mm = \app\common\model\MemberModel::field("id")->where(['nid' => $nid])->find();
        View::assign("member_model_id", $mm["id"]);

        return  view('detail');
    }

    public function delete()
    {
        $param = $this->request->param();
        $id = $param['id'];
        $nid = $param['nid'];
        if (!array_key_exists('id', $param) || !array_key_exists('nid', $param)) {
            $this->error('参数错误');
        }
        Db::name($nid)->delete($id);
        $this->success('操作成功');
    }

    public function deletes()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            if (array_key_exists("idList", $param) && array_key_exists("nidList", $param)) {
                $idList = json_decode($param['idList']);
                $nidList = json_decode($param['nidList']);
                foreach ($nidList as $k => $nid) {
                    Db::name($nid)->delete($idList[$k]);
                }
                $this->success('操作成功', '', $param);
            }
        }
    }

    // 获取栏目自定义属性
    public function getField()
    {
        $member_model_id = $this->request->param("member_model_id");
        $where = ['status' => 1];
        $mmfList = \app\common\model\MemberModelField::field('dtype,name')->where($where)->whereOr(["member_model_id" => $member_model_id, "member_model_id" => 0])->order(["sort_order" => "desc", "create_time" => "asc"])->select();
        $this->success("查询成功", '', $mmfList);
    }
}