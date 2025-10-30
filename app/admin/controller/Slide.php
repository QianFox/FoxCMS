<?php

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\model\AuthRule;
use \app\common\model\Slide as SlideModel;
use think\facade\View;

class Slide extends AdminBase
{
    public function index($page=1, $pageSize=100)
    {
        $param = $this->request->param();
        if($this->request->isAjax()){
            $where = array();
            if(array_key_exists('keyword', $param) && !empty($param['keyword'])){
                if($param['keyword'] == '禁用'){
                    array_push($where, ['status', '=', 0]);
                }else if($param['keyword'] == '启用'){
                    array_push($where, ['status', '=', 1]);
                }else{
                    array_push($where, ['title', 'like', '%'.$param['keyword'].'%']);
                }
            }
            $list = (new SlideModel())->where($where)->paginate(['page'=> $page, 'list_rows'=>$pageSize]);
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
        $status = intval($param['status']);
        $slideModel = new SlideModel();
        try{
            $slideModel->whereIn("id", implode(",", $idList))->update(["status"=>$status]);
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
        array_push($breadcrumb, ['id'=>'', 'title'=>'添加广告位', 'name'=>DIRECTORY_SEPARATOR. config('adminconfig.admin_path').'/Slide/add','url'=>'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);

        if ($this->request->isPost()) {
            $param = $this->request->param();
            SlideModel::create($param);
            $this->success("操作成功");
        }
        return view('add');
    }

    public function edit()
    {
        $columnId = $this->request->param("columnId");
        $authRule = AuthRule::find($columnId);
        $bcidStr = str_replace(",","_", $authRule->tier);
        $breadcrumb = AuthRule::getBreadcrumb($bcidStr);
        array_push($breadcrumb, ['id'=>'', 'title'=>'编辑广告位', 'name'=>DIRECTORY_SEPARATOR. config('adminconfig.admin_path').'/Slide/edit','url'=>'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);

        if ($this->request->isPost()) {
            $param = $this->request->param();
            SlideModel::update($param);
            $this->success("操作成功");
        }
        $id = $this->request->get('id');
        $slide = SlideModel::find($id);
        return view('edit',['slide'=>$slide]);
    }

    public function delete()
    {
        $id = intval($this->request->get('id'));
        !($id>0) && $this->error('参数错误');
        SlideModel::destroy($id);
        $this->success('删除成功');
    }

    public function deletes()
    {
        $param = $this->request->param();
        if(array_key_exists("idList", $param)){
            $idList = json_decode($param['idList']);
            $count = 0;
            $slideModel = new SlideModel();
            $slideModel->startTrans();
            foreach ($idList as $key => $id){
                $r = $slideModel->destroy($id);
                if($r){
                    $count++;
                }
            }
            if(sizeof($idList) == $count){
                $slideModel->commit();
                $this->success('操作成功');
            }else {
                $slideModel->rollback();
                $this->error('操作失败');
            }
        }
    }

}