<?php


namespace app\email\controller;


use app\common\controller\AdminApplyBase;
use think\facade\Db;
use think\facade\View;

class PluginMailTemplate extends AdminApplyBase
{

    public function index($page=1, $pageSize=100)
    {
        $param = $this->request->param();
        if($this->request->isAjax()){
            $where = array();
            $list = Db::name('plugin_mail_template')->where($where)->paginate(['page'=> $page, 'list_rows'=>$pageSize])->each(function($item, $key){
                $status = [0=>'否',1=>'是'];
                $item['statustext'] = $status[$item['status']];
                return $item;
            });
            $this->success('查询成功', '',$list);
        }
        return view('index');
    }

    /**修改状态
     * @throws \Exception
     */
    public function updateStatus()
    {
        $param = $this->request->param();
        $idList = json_decode($param['idList']);
        if(sizeof($idList) <= 0){
            $this->error("操作失败，请选择对应启用数据");
        }
        $status = intval($param['status']);
        $r = Db::name('plugin_mail_template')->whereIn("id", implode(",", $idList))->update(["status"=>$status]);
        if($r){
            $this->success("操作成功");
        }else{
            $this->error('操作失败');
        }
    }

    /**
     * 修改标题
     * @throws \think\db\exception\DbException
     */
    public function updateTitle()
    {
        $param = $this->request->param();
        $id = $param['id'];
        $title = $param['title'];
        if(empty($id)){
            $this->error("操作失败，没有参数");
        }
        $r = Db::name('plugin_mail_template')->where("id", $id)->update(["title"=>$title]);
        if($r){
            $this->success("操作成功");
        }else{
            $this->error('操作失败');
        }
    }

}