<?php

namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\facade\Db;
use think\facade\View;

class Watermark extends AdminBase
{

    public function index()
    {
        $param = $this->request->param();
        if(array_key_exists('bcid', $param)) {
            View::assign('bcid', $param['bcid']);
        }
        $watermarkArr = Db::name("watermark")->select()->toArray();
        if(sizeof($watermarkArr) > 0){
            $watermark = $watermarkArr[0];
        }else{
            $watermark = [
                'status'=>0,
                'type'=>2
            ];
        }
        View::assign('watermark', $watermark);
        //查询当前模板的类型
        return view('index');
    }

    public function save()
    {
        $param = $this->request->post();
        $id = $param["id"];
        if($id == "" || empty($id)){
            unset($param['id']);
            $param['create_time'] = date("Y-m-d H:i:s");
            $r = Db::name('watermark')->save($param);
        }else{
            $r = Db::name('watermark')->update($param);
        }
        if($r !== false){
            $this->success('设置成功');
        }else{
            $this->error("设置失败", "", $param);
        }
    }

}
