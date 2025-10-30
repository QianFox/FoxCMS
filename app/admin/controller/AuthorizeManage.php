<?php

namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\facade\View;

class AuthorizeManage extends AdminBase
{
    function index(){
        $keyword = $this->request->param("keyword");//访问内容
        if(empty($keyword)){
            $keyword = $this->domain;//访问内容
        }
//        $keyword = "https://www.foxcms.cn/";
        $foxcmsDomain = config("adminconfig.foxcms_domain");//foxcms官网地址
        $foxcmsPathUrl = $foxcmsDomain.url("api/Home/authorizeInfo")."?keyword=".$keyword;
        $resJson = get_url_content($foxcmsPathUrl);
        $res = json_decode($resJson);
        $authorize = $res->data;//授权信息
        $auth = get_object_vars($authorize);//对象转数组
        View::assign("auth", $auth);//授权信息
        return view("authorize");
    }

}