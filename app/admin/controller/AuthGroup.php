<?php

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\model\AuthGroup as AuthGroupModel;
use app\common\model\AuthGroupAccess;
use app\common\model\AuthRule;
use think\facade\Cache;
use think\facade\View;
use utils\Data;

class AuthGroup extends AdminBase
{
    public function index($page=1, $pageSize=100)
    {
        $param = $this->request->param();
        if(array_key_exists('bcid', $param)){
            View::assign('bcid',$param['bcid']);
        }
        if($this->request->isAjax()){
            $where = array();
            if(array_key_exists('status', $param) && !empty($param['status'])){
                if($param['status'] == '禁用'){
                    $where['status'] = 0;
                }else if($param['status'] == '启用'){
                    $where['status'] = 1;
                }
            }
            if(array_key_exists('keyword', $param) && !empty($param['keyword'])){
                $where['title'] = trim($param['keyword']);
            }
            $list = AuthGroupModel::withCount(['authGroupAccess'])->where($where)->paginate(['page'=> $page, 'list_rows'=>$pageSize]);
            $this->success('查询成功', '',$list);
        }
        return view('index');

    }

    public function edit()
    {
        $columnId = $this->request->param("columnId");
        $authRule = AuthRule::find($columnId);
        $bcidStr = str_replace(",","_", $authRule->tier);
        $breadcrumb = AuthRule::getBreadcrumb($bcidStr);
        array_push($breadcrumb, ['id'=>'', 'title'=>'编辑角色', 'name'=> DIRECTORY_SEPARATOR. config('adminconfig.admin_path').'/AuthGroup/edit','url'=>'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);

        if ($this->request->isPost()) {
            $param = $this->request->param();
            if(!array_key_exists('id',$param)){
                $this->error('角色修改失败,缺少必要参数');
            }
            //增加父节点id
            if(array_key_exists('rules', $param)){
                $ids = explode(',', $param['rules']);
                $authRules = AuthRule::whereIn('id', $ids)->select()->toArray();
                $rules = [];
                foreach ($authRules as $key=> $authRule){
                    $tierArr = explode(',', $authRule['tier']);
                    $rules = array_merge($rules, $tierArr);
                }
                $rules = array_unique($rules);
                $param['rules'] = implode(',', $rules);
            }else{
                $this->error('请分配权限存在');
            }
            $result = AuthGroupModel::update(['title' => $param['title'],'rules'=>$param['rules'], 'status'=>$param['status'], 'id'=> $param['id']]);
            if ($result) {
                xn_add_admin_log('修改角色');
                Cache::clear();
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }
        }

        $id = $this->request->get('id');
        $data = AuthGroupModel::find($id);
        $rules = explode(',', $data['rules']);

        $auth_data = AuthRule::where(['status'=>1])->select()->toArray();
        $rule_data = Data::channelLevel($auth_data, 0, '&nbsp;', 'id');
        $rule_data = $this->channelMenu($rule_data);

        return view('edit',['data'=>$data, 'rule_data' => $rule_data, "rules"=>$rules]);
    }

    public function updateStatus()
    {
        $param = $this->request->param();
        $idList = json_decode($param['idList']);
        if(sizeof($idList) <= 0){
            $this->error("操作失败，请选择对应启用数据");
        }
        $status = intval($param['status']);
        $authGroupModel = new AuthGroupModel();
        try{
            $authGroupModel->whereIn("id", implode(",", $idList))->update(["status"=>$status]);
        }catch (\Exception $e){
            $this->error('操作失败,'.$e->getMessage());
        }
        $this->success('操作成功');

    }

    public function add()
    {
        $columnId = $this->request->param("columnId");
        $authRule = AuthRule::find($columnId);
        $bcidStr = str_replace(",","_", $authRule->tier);
        $breadcrumb = AuthRule::getBreadcrumb($bcidStr);
        array_push($breadcrumb, ['id'=>'', 'title'=>'添加角色', 'name'=>DIRECTORY_SEPARATOR. config('adminconfig.admin_path').'/AuthGroup/add','url'=>'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);

        if ($this->request->isPost()) {
            $param = $this->request->param();
            $ajm  = AuthGroupModel::getByTitle($param['title']);
            if($ajm){
                $this->error('角色名称已经存在');
            }
            //增加父节点id
            if(array_key_exists('rules', $param)){
                $ids = explode(',', $param['rules']);
                $authRules = AuthRule::whereIn('id', $ids)->select()->toArray();
                $rules = [];
                foreach ($authRules as $key=> $authRule){
                    $tierArr = explode(',', $authRule['tier']);
                    $rules = array_merge($rules, $tierArr);
                }
                $rules = array_unique($rules);
                $param['rules'] = implode(',', $rules);
            }else{
                $this->error('请分配权限存在');
            }
            $result = AuthGroupModel::create(['title' => $param['title'],'rules'=>$param['rules'], 'status'=>$param['status']]);
            if ($result) {
                xn_add_admin_log('添加角色');
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }
        }
        // 获取规则数据
        $auth_data = AuthRule::where(['status'=>1])->select()->toArray();
        $rule_data = Data::channelLevel($auth_data, 0, '&nbsp;', 'id');
        $rule_data = $this->channelMenu($rule_data);
        return view('add', ['rule_data' => $rule_data]);
    }

    public function channelMenu($rule_data){
        foreach ($rule_data as $k=> $v){
            if(sizeof($v['_data']) > 0){
                foreach ($v['_data'] as  $vv){
                    if((sizeof($vv['_data']) > 0) && $vv['type'] =='M'){
                        $cDatas = array_merge( $vv['_data'], $v['_data']);
                        $i = 0;
                        foreach ($cDatas as $key=> $vvv){
                            if(sizeof($vvv['_data']) > 0 && $vvv['type'] =='M'){
                                unset($cDatas[$key]);
                                $i++;
                            }
                        }
                        $cDatas = array_values($cDatas);
                        $v['_data'] = $cDatas;
                        $rule_data[$k] = $v;
                    }
                }

            }
        }
        return $rule_data;
    }

    public function delete()
    {
        $id = intval($this->request->get('id'));
        !($id>0) && $this->error('参数错误');
        $suthGroupAccess = AuthGroupAccess::where("group_id", $id)->find();
        if($suthGroupAccess){
            $this->error('角色下有用户,操作失败');
        }
        AuthGroupModel::destroy($id);
        xn_add_admin_log('删除角色');
        $this->success('删除成功');
    }

    public function deletes()
    {
        if( $this->request->isPost() ) {
            $param = $this->request->param();
            if(array_key_exists("idList", $param)){
                $idList = json_decode($param['idList']);
                $count = 0;
                $authGroupModel = new AuthGroupModel();
                $authGroupModel->startTrans();
                foreach ($idList as $key => $id){
                    $suthGroupAccess = AuthGroupAccess::where("group_id", $id)->find();
                    if($suthGroupAccess){
                        $authGroupModel->rollback();
                        $this->error('角色下有用户,操作失败');
                    }
                    $r = $authGroupModel->destroy($id);
                    if($r){
                        $count++;
                    }
                }
                if(sizeof($idList) == $count){
                    $authGroupModel->commit();
                    xn_add_admin_log('删除角色');
                    $this->success('操作成功');
                }else {
                    $authGroupModel->rollback();
                    $this->error('操作失败');
                }
            }
        }
    }

    public function group_rule()
    {
        if($this->request->isPost()){
            $param = $this->request->param();
            $data = [
                'id' => $param['id'],
                'rules' => implode(',', $param['rule_ids'])
            ];
            AuthGroupModel::update($data);
            $this->success('操作成功');
        }

        $id = $this->request->get('id');
        // 获取用户组数据
        $group_data = AuthGroupModel::find($id);
        $group_data['rules'] = explode(',', $group_data['rules']);
        // 获取规则数据
        $auth_data = AuthRule::select()->toArray();
        $rule_data = Data::channelLevel($auth_data,0,'&nbsp;','id');
        return view('',['group_data'=>$group_data,'rule_data'=>$rule_data]);
    }

}
