<?php

use think\facade\Route;

$url = request()->url();
$url = str_replace("/index.php", "", $url);
$home_lang = xn_cfg("base.home_lang");
$langList = [];
try{
    $langList = \think\facade\Db::name('lang')->field("lang")->where("lang","<>", $home_lang)->where('status', 1)->cache(true)->select()->toArray();
}catch (\Exception $e){
}
if(sizeof($langList) <= 0){
    array_push($langList, ['lang'=>$home_lang]);
}
//生成路由 //1:动态url,2:伪静态化,3:静态页面
$url_model = xn_cfg("seo.url_model");

if($url_model == 2 || $url_model == 3){
    Route::get('/index_<lang>', 'Index/index', ['lang'=>"<lang>"]);
    $rlang = "";
    $visit_lang = $home_lang;
    foreach ($langList as $k=>$item){
        if(str_starts_with($url,"/{$item['lang']}/")){
            $rlang = ":lang";
            $visit_lang = $item['lang'];
            break;
        }
    }
    $columns = \app\common\model\Column::field("column_model")->where("lang","=", $visit_lang)->cache(true)->select();
    foreach ($columns as $k => $v) {
        Route::any("{$rlang}/".$v["column_model"]."/index/:id", $v["column_model"]."/index")->pattern(['id'=>'\d+']);
        if($v["column_model"] != "single"){
            Route::any("{$rlang}/".$v["column_model"]."/detail/:id", $v["column_model"]."/detail")->pattern(['id'=>'\d+']);
        }
    }
}
