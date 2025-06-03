<?php

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\model\AdminLog as AdminLogModel;
use think\facade\Db;

class FormNotice extends AdminBase
{
    public function index()
    {
        $param = $this->request->param();
        if($this->request->isAjax()) {
            $model = new AdminLogModel();
            if ($param['start_date'] != '' && $param['end_date'] != '') {
                $model = $model->whereBetweenTime('create_time', $param['start_date'], $param['end_date']);
            }
            if(empty($param["currentPage"])){
                $param["currentPage"] = 1;
            }
            if(empty($param["pageSize"])){
                $param["pageSize"] = 10;
            }
            $list = $model->where([])->order('id desc')->paginate(['page'=> $param['currentPage'], 'list_rows'=>$param['pageSize']]);;
            $this->success("查询成功", "", $list);
        }
        return view('fox_index');
    }

    public function clear()
    {
        $model = Db::name('admin_log');
        $day = $this->request->param('day/d');
        if( $day > 0 ) {
            $model = $model->whereTime('create_time', '<=', strtotime(date('Y-m-d',time()-$day*86400)) );
            $result = $model->delete();
        } else {
            $result = $model->delete(true);
        }
        if( $result ) {
            xn_add_admin_log('清除日志');
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    public function deletes()
    {
        if( $this->request->isPost() ) {
            $param = $this->request->param();
            if(array_key_exists("idList", $param)){
                $idList = json_decode($param['idList']);
                $count = 0;
                $model = new AdminLogModel();
                $model->startTrans();
                foreach ($idList as $key => $id){
                    $r = $model->destroy($id);
                    if($r){
                        $count++;
                    }
                }
                if(sizeof($idList) == $count){
                    $model->commit();
                    $this->success('操作成功');
                }else {
                    $model->rollback();
                    $this->error('操作失败');
                }
            }
        }
    }

}
