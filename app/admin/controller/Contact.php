<?php

namespace app\admin\controller;
use app\common\model\Contact as ContactModel;

use app\common\controller\AdminBase;
use think\facade\Db;
use think\facade\View;

class Contact extends AdminBase
{
    public function index()
    {
        $param = $this->request->param();
        if(array_key_exists('bcid', $param)){
            View::assign('bcid',$param['bcid']);
        }
        $lang = $this->getMyLang();
        $contactArr = ContactModel::where([['lang', '=', $lang]])->select()->toArray();
        if(sizeof($contactArr) > 0){
            $contact = $contactArr[0];
            foreach ($contact as $key => $val){
                $contact[$key."_call"] = "{fox:contact name='$key'/}";
            }
            View::assign('id', $contact["id"]);
        }else{
            $contact = [];
            $contactFields = Db::name('contact')->getFields();
            foreach ($contactFields as $key=>$field){
                $contact[$key."_call"] = "{fox:contact name='$key'/}";
            }
        }
        View::assign('contact', $contact);
        return view('index');
    }

    public function save()
    {
        $file = $this->request->action();
        if( $this->request->isPost() ) {
            $param = $this->request->post();
            $id = $param["id"];
            if($id == "" || empty($id)){
                $param['lang'] = $lang = xn_cfg("base.lang");
                ContactModel::create($param);
            }else{
                ContactModel::update($param);
                $this->success('设置成功');
            }
            xn_add_admin_log("保存联系方式", "contact");//添加日志
            $this->success('设置成功');
        }
        return view($file,['data'=>$this->_load($file)]);
    }

}