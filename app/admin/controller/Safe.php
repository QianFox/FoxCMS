<?php

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\model\Blacklist;
use think\facade\View;

class Safe extends AdminBase
{
    public function index()
    {
        $param = $this->request->param();
        if(array_key_exists('bcid', $param)){
            View::assign('bcid',$param['bcid']);
        }
        $list = \app\common\model\Safe::select();
        $rlist = [];
        foreach ($list as $item){
            array_push($rlist, [
                "id" => $item['id'],
                "dict_label" =>  $item['dict_label'],
                "dict_value" =>  $item['dict_value'],
                "status" =>  $item['status'],
                "description" =>  $item['description'],
                "type" =>  $item['type'],
                "create_time" =>  $item['create_time'],
                "update_time" =>  $item['update_time']
            ]);
        }
        View::assign('saveList', $rlist);
        return view('index');
    }

    public function list(){
        //查询ip黑名单
        $blacklist = Blacklist::select();
        $this->success('查询成功', "", $blacklist);
    }

    public function saveBlack(){
        //查询ip黑名单
        $param = $this->request->post();
        if(!array_key_exists("blacklists", $param)){
            $this->error("保存黑名数据不存在");
            if(empty(trim($param['blacklists']))){
                $this->error("保存黑名数据不能为空");
            }
        }
        $blacklist = explode("\n", trim($param['blacklists']));
        $blacklistList = Blacklist::select();
        $saveBlacklist = [];
        foreach ($blacklist as $key=>$ip){
            $isExist = false;
            foreach ($blacklistList as $black){
                if($ip == $black['ip']){
                    $isExist = true;
                }
            }
            if(!$isExist){
                array_push($saveBlacklist, ['ip'=>$ip, "create_time"=>date("Y-m-d H:i:s", time()), 'update_time'=>date("Y-m-d H:i:s", time())]);
            }
        }
        if(sizeof($saveBlacklist) > 0){
            $rblack = (new Blacklist())->saveAll($saveBlacklist);
            if(sizeof($rblack) <= 0){
                $this->error('保存失败');
            }
            $this->success('保存成功');
        }else{
            $this->error("黑名单IP已存在");
        }
    }

    public function blackDeletes()
    {
        $param = $this->request->param();
        if(array_key_exists("idList", $param)){
            $idList = json_decode($param['idList']);
            $count = 0;
            $blackModel = new Blacklist();
            $blackModel->startTrans();
            foreach ($idList as $key => $id){
                $r = $blackModel->destroy($id);
                if($r){
                    $count++;
                }
            }
            if(sizeof($idList) == $count){
                $blackModel->commit();
                $this->success('操作成功');
            }else {
                $blackModel->rollback();
                $this->error('操作失败');
            }
        }
    }

    public function save()
    {
        $param = $this->request->post();
        if(array_key_exists("dataList", $param)){
            $dataList = $param['dataList'];
            $dataArr = [];
            foreach ($dataList as $data){
                if(empty($data['id'])){
                    unset($data['id']);
                    array_push($dataArr, $data);
                }else{
                    array_push($dataArr, $data);
                }
            }
            $isSave = false;
            if(sizeof($dataArr) > 0){
                $rdata = (new \app\common\model\Safe())->saveAll($dataArr);
                if($rdata){
                    $isSave = true;
                }
            }
            if($isSave){
                xn_add_admin_log("保存安全管理", "safe");//添加日志
                $this->success('设置成功');
            }else{
                $this->error("保存失败");
            }
        }else{
            $this->error("没有数据，保存失败");
        }
    }

}