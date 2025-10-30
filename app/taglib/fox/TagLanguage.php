<?php

namespace app\taglib\fox;

use think\facade\Db;

/**
 * 语言
 */
class TagLanguage extends TagBase
{
    /**
     * 查询数据
     */
    public function getList($param)
    {
        $path = $param['path'];
        $currentstyle = $param['currentstyle'];
        $visit_lang = $this->getLang();//语言
        $baseurl = request()->domain();//基本路径
        $langList = Db::name('lang')->field("lang,name,pic_flag")->whereNotIn('lang', $visit_lang)->where('status', 1)->select()->toArray();
        $toggle = $param['toggle'];
        if ($toggle == "on"){//开启特殊两门语言切换
            if(sizeof($langList) != 1){
                echo "抱歉该状态必须为两门语言的时候，才能使用";
                return false;
            }
        }
        $home_lang = xn_cfg("base.home_lang");
        $url = "/index";
        if(!empty($path)){
            $url = $path;
        }else{
            if(!check_url($baseurl."/plus/Access/check")){
                $url = "/index.php/{$url}";
            }
        }
        $rlist = [];
        foreach ($langList as $key=>$item){
            if(!($item['lang'] == $visit_lang)){
                $currentstyle = "";
            }
            $item['currentstyle'] = $currentstyle;
            if(!($home_lang == $item['lang'])){

            }
            $url = resetIndexUrl($url, $item['lang']);
            $item['url'] = $url;
            $rlist[] = $item;
        }
        return $rlist;
    }
}