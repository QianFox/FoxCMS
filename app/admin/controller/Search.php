<?php

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\model\AuthRule;
use \app\common\model\Search as SearchModel;
use think\facade\View;

class Search extends AdminBase
{

    public function initialize()
    {
        parent::initialize();
        $action = request()->action();
        if($action == "add" || $action == "edit"){
            $searchGroupList = \app\common\model\SearchGroup::where(['status'=>1])->order(['sort'=>'desc'])->select();
            View::assign("searchGroupList", $searchGroupList);
        }
    }

    public function index($page=1, $pageSize=100)
    {
        $param = $this->request->param();


        if($this->request->isAjax()){
            $where = array();
            if(array_key_exists('keyword', $param) && !empty($param['keyword'])){
                if($param['keyword'] == '否'){
                    array_push($where, ['is_used', '=', 0]);
                }else if($param['keyword'] == '是'){
                    array_push($where, ['is_used', '=', 1]);
                }else{
                    array_push($where, ['name', 'like', '%'.$param['keyword'].'%']);
                }
            }
            //查询所有模型 生成
            $list = (new SearchModel())->where($where)->order(['search_group_id'=>'desc', 'sort'=>'desc', 'create_time'=>'desc'])->paginate(['page'=> $page, 'list_rows'=>$pageSize]);

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
        $is_used = intval($param['is_used']);
        $searchModel = new SearchModel();
        try{
            $searchModel->whereIn("id", implode(",", $idList))->update(["is_used"=>$is_used]);
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
        array_push($breadcrumb, ['id'=>'', 'title'=>'批量新增', 'name'=>DIRECTORY_SEPARATOR. config('adminconfig.admin_path').'/Search/add','url'=>'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);

        if ($this->request->isPost()) {
            $param = $this->request->param();
            $searchs = $param['searchs'];
            if(empty($searchs)){
                $this->error("Search标签列表为空");
            }

            $findSearchs = SearchModel::select();
            $is_used = $param['is_used'];
            $searchList = explode("\n", $searchs);
            $searchArr = [];
            foreach ($searchList as $search){
                $isExist = false;
                foreach ($findSearchs as $findSearch){
                    if($findSearch['name'] == $search){
                        $isExist = true;
                        break;
                    }
                }
                if(!$isExist && !empty($search)){
                    array_push($searchArr, ["name"=>$search, "is_used"=>$is_used, 'search_group_id'=>$param['search_group_id']]);
                }
            }
            (new SearchModel())->saveAll($searchArr);
            $this->success("操作成功", "", $searchList);
        }
        return view('add');
    }

    public function edit()
    {
        $columnId = $this->request->param("columnId");
        $authRule = AuthRule::find($columnId);
        $bcidStr = str_replace(",","_", $authRule->tier);
        $breadcrumb = AuthRule::getBreadcrumb($bcidStr);
        array_push($breadcrumb, ['id'=>'', 'title'=>'编辑', 'name'=>DIRECTORY_SEPARATOR. config('adminconfig.admin_path').'/Search/edit','url'=>'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);

        if ($this->request->isPost()) {
            $param = $this->request->param();
            SearchModel::update($param);
            $this->success("操作成功");
        }
        $id = $this->request->get('id');
        $search = SearchModel::find($id);
        return view('edit',['search'=>$search]);
    }

    public function delete()
    {
        $id = intval($this->request->get('id'));
        !($id > 0) && $this->error('参数错误');
        $search = \app\common\model\Search::find($id);
        if (!$search) {
            $this->error("操作失败");
        }
        SearchModel::destroy($id);
        $this->success('删除成功');
    }

    public function deletes()
    {
        if( $this->request->isPost() ) {
            $param = $this->request->param();
            if(array_key_exists("idList", $param)){
                $idList = json_decode($param['idList']);
                $r = \app\common\model\Search::destroy($idList);//批量删除
                if($r){
                    $this->success('操作成功');
                }else {
                    $this->error('操作失败');
                }
            }
        }
    }

    public function updateData()
    {
        $param = $this->request->param();
        $id = $param['id'];
        $sort = $param['sort']??1;
        if(empty($id)){
            $this->error("修改条件不能空");
        }
        $r = \app\common\model\Search::update(['id'=>$id, 'sort'=>$sort]);
        if($r !== false){
            $this->success("操作成功");
        }
        $this->error("操作失败");
    }

}