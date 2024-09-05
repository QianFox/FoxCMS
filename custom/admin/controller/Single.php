<?php
//zsl
namespace app\admin\controller;

use app\common\controller\AdminContentBase;
use app\common\model\Column;
use think\facade\View;

class Custom extends AdminContentBase
{
    public function index()
    {
        $param = $this->request->param();
        View::assign('bcid',$param['bcid']);
        $columnId = $param['columnId'];
        $custom =  \app\common\model\Custom::where('column_id', $columnId)->find();

        $teamStatusArr = explode(',', $custom['team_status']);
        if(sizeof($teamStatusArr) > 0){
            if(in_array('down', $teamStatusArr)){
                $custom["statusDown"] = "down";
            }
            if(in_array('del', $teamStatusArr)){
                $custom["statusDel"] = "del";
            }

        }

        if(empty($custom->column)){
            $single["column"]=Column::field('name')->find($columnId)['name'];
        }

        View::assign('custom', $custom);
        View::assign('columnId', $columnId);
        return view();
    }

    /**
     * 保存
     */
    public function save(){
        $param = $this->request->param();
        if(array_key_exists("content", $param)){
            $rdata = $this->replaceContent($param, 'content');//替换内容
            if($rdata["code"] == 0){
                $this->error($rdata['msg']);
            }
            $param['content'] = $rdata['content'];//替换内容
        }
        $r = false;
        $param['lang'] = $this->getMyLang();
        if (empty($param['id'])) {
           $r = \app\common\model\Custom::create($param);
        }else{
            $r = \app\common\model\Custom::update($param);
        }
        if($r){
            $this->success("保存成功");
        }else{
            $this->error("保存失败");
        }
    }

}
