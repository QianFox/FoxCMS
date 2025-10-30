<?php

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\model\Admin as AdminModel;
use app\common\model\AuthGroup;
use app\common\model\AuthGroupAccess;
use app\common\model\AuthRule;
use think\facade\Db;
use think\facade\Session;
use think\facade\View;

class Admin extends AdminBase
{
    public function index($page=1, $pageSize=100)
    {
        $param = $this->request->param();
        if(array_key_exists('bcid', $param)){
            View::assign('bcid',$param['bcid']);
        }
        if($this->request->isAjax()){
            $where = array();
            if(array_key_exists('keyword', $param) && !empty($param['keyword'])){
                if($param['keyword'] == '禁用'){
                    array_push($where, ['status', '=', 0]);
                }else if($param['keyword'] == '启用'){
                    array_push($where, ['status', '=', 1]);
                }else{
                    array_push($where, ['username|phone', 'like', '%'.$param['keyword'].'%']);
                }
            }
            if(array_key_exists('username', $param) && !empty($param['username'])){
                $where['username'] = $param['username'];
            }
            $list = AdminModel::with(['auth_group_access'])->field('id,username,phone,status, status as status_text')->where($where)->paginate(['page'=> $page, 'list_rows'=>$pageSize]);

            $this->success('查询成功', '',$list);
        }
        return view('index');
    }


    public function updateStatus()
    {

        $param = $this->request->param();
        $idList = json_decode($param['idList']);
        if(sizeof($idList) <= 0){
            $this->error("操作失败，请选择对应启用数据");
        }
        if(in_array("1", $idList)){
            $this->error("操作失败，不能禁用/启用管理员");
        }
        $status = intval($param['status']);
        $adminModel = new AdminModel();
        try{
            $adminModel->whereIn("id", implode(",", $idList))->update(["status"=>$status]);
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
        array_push($breadcrumb, ['id'=>'', 'title'=>'添加用户', 'name'=>DIRECTORY_SEPARATOR. config('adminconfig.admin_path').'/Admin/add','url'=>'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);

        if( $this->request->isPost() ) {
            $param = $this->request->param();
            validate(\app\admin\validate\Admin::class)->scene('save')->check($param);
            $admin = AdminModel::getByUsername($param['username']);
            if($admin){
                $this->error('用户名已存在');
            }
            if (!verify_password($param['password'])){
                $this->error('密码必须包含数字、大小写字母、特殊字符中至少3种,且不少于8位');
            }
            $param['password'] = xn_encrypt($param['password']);
            $adminModel = new AdminModel();
            $adminModel->startTrans();
            $ar = $adminModel->save($param);
            if(!$ar){
                $adminModel->rollback();
                $this->error('保存用户失败');
            }
            $r = (new AuthGroupAccess())->save(["admin_id"=>$adminModel->id, "group_id"=>$param['group_id']]);
            if(!$r){
                $adminModel->rollback();
                $this->error('保存用户组失败');
            }
            $adminModel->commit();
            xn_add_admin_log("添加用户信息", "login", "{$param['nickname']}的信息添加");
            $this->success('操作成功');
        }
        $list = AuthGroup::field('id,title')->where(['status'=>1])->select()->toArray();
        return view('add',['authGroups'=>$list]);
    }


    public function edit()
    {
        $columnId = $this->request->param("columnId");
        $authRule = AuthRule::find($columnId);
        $bcidStr = str_replace(",","_", $authRule->tier);
        $breadcrumb = AuthRule::getBreadcrumb($bcidStr);
        array_push($breadcrumb, ['id'=>'', 'title'=>'编辑用户', 'name'=>DIRECTORY_SEPARATOR. config('adminconfig.admin_path').'/Admin/edit','url'=>'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);

        if( $this->request->isPost() ) {
            $param = $this->request->param();
            validate(\app\admin\validate\Admin::class)->scene('update')->check($param);

            $id = $param['id'];
            $group_id = $param['group_id'];
            //更新权限
            if( !empty($group_id) ) {
                $ags = Db::name('auth_group_access')->where("admin_id",$id)->find();
                if($ags){
                    Db::name('auth_group_access')->where("admin_id",$id)->update(['group_id'=>$group_id]);
                }else{
                    (new AuthGroupAccess())->save(["admin_id"=>$id, "group_id"=>$param['group_id']]);
                }
            }
            if($id == 1){//超级管理员
                unset($param["status"]);
            }
            if(!empty($param['password'])){
                if (!verify_password($param['password'])){
                    $this->error('密码必须包含数字、大小写字母、特殊字符中至少3种,且不少于8位');
                }
                $param['password'] = xn_encrypt($param['password']);
            }else{
                unset($param["password"]);
            }
            $result = (new AdminModel)->force()->update($param);
            if( $result ) {
                xn_add_admin_log("修改用户信息", "login", "{$param['nickname']}的信息被修改");
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }

        }
        $id = $this->request->get('id');
        $list = AuthGroup::field('id,title')->where(['status'=>1])->select()->toArray();
        $user_group_id = Db::name('auth_group_access')->where("admin_id",$id)->column('group_id');
        $group_title = "";
        foreach ($list as $key=>$value){
            if($value["id"] == $user_group_id[0]){
                $group_title = $value["title"];
                break;
            }
        }
        $assign = [
            'user_data'=> AdminModel::find($id),
            'authGroups'=>$list,
            'user_group_id'=> $user_group_id[0],
            'group_title'=>$group_title
        ];
        return view('edit', $assign);
    }

    public function delete()
    {
        $id = intval($this->request->get('id'));
        if($id == 1){
            $this->error("操作失败，不能删除管理员");
        }
        !($id>1) && $this->error('参数错误');
        AuthGroupAccess::where('admin_id', $id)->delete();
        AdminModel::destroy($id);
        xn_add_admin_log('删除用户信息');
        $this->success('删除成功');
    }

    public function deletes()
    {
        $param = $this->request->param();
        if(array_key_exists("idList", $param)){
            $idList = json_decode($param['idList']);
            if(in_array("1", $idList)){
                $this->error("操作失败，不能删除管理员");
            }

            $count = 0;
            $adminModel = new AdminModel();
            $adminModel->startTrans();
            foreach ($idList as $key => $id){
                $r = AdminModel::destroy($id);
                if($r){
                    AuthGroupAccess::where('admin_id', $id)->delete();
                    $count++;
                }
            }
            if(sizeof($idList) == $count){
                $adminModel->commit();
                xn_add_admin_log('删除用户');
                $this->success('操作成功');
            }else {
                $adminModel->rollback();
                $this->error('操作失败');
            }
        }
    }

    public function info()
    {
        if( $this->request->isPost() ) {
            $param = $this->request->param();
            $id = $this->getAdminId();
            if( $param['password']!='' ){
                $param['password'] = xn_encrypt($param['password']);
            } else {
                unset($param['password']);
            }
            $result = AdminModel::where('id',$id)->update($param);
            if( $result ) {
                xn_add_admin_log('修改个人资料',"login");
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }
        }
        $id = $this->getAdminId();
        $user_data = AdminModel::find($id);
        return view('', ['user_data'=>$user_data]);
    }

    public function updatePassword()
    {
        $param =  $this->request->param();
        if(!empty($param['password'])){
            if (!verify_password($param['password'])){
                $this->error('密码必须包含数字、大小写字母、特殊字符中至少3种,且不少于8位');
            }
            $param['password'] = xn_encrypt($param['password']);
        }
        $opassword = xn_encrypt($param['opassword']);
        $admin = (new AdminModel())->find($this->getAdminId());
        if($opassword != $admin["password"]){
            $this->error('原密码输入错误');
        }
        unset($admin["password"]);
        Session::set('admin_auth', $admin);
        $param["id"] = $this->getAdminId();
        $result = (new AdminModel())->force()->update($param);
        if( $result) {
            xn_add_admin_log('修改密码', "login", "{$admin['nickname']}密码被修改");
            $this->success('修改成功', "", $admin);
        } else {
            $this->error('操作失败');
        }
    }
}
