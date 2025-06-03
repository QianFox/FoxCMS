<?php

namespace app\taglib\fox;

use app\common\util\SitemapUtil;

/**
 * 网站地图
 */
class TagSitemap extends TagBase
{
    /**
     * 网站地图
     */
    public function getList($param)
    {
        $visit_lang = $this->getLang();//语言
        $type = $param['type'];
        $target = $param['target'];
        if ($type == "html"){
            $path = "/sitemap.html";
        }elseif ($type == "txt"){
            $path = "/sitemap.txt";
        }elseif ($type == "xml"){
            $path = "/sitemap.xml";
        }else{
            $path = "/sitemap.html";
            $type = "html";
        }
        $home_lang = xn_cfg("base.home_lang");//默认语言
        if($home_lang == $visit_lang){
            $path = "{$path}";
        }else{
            $path = "/{$visit_lang}{$path}";
        }

        $title = getLangContentByMark($visit_lang, "sitemap_title")['value'];
        if(!empty($param['title'])){
            $title = $param['title'];
        }
        try{
            $baseurl = request()->domain()."/";//基本路径
            if(!check_url($baseurl."plus/Access/check")){
                $baseurl = $baseurl."index.php/";
            }
            (new SitemapUtil())->generateSitemap($type, $baseurl, $visit_lang);
        }catch (\Exception $e){}
        $val = '<a href="'.$path.'" target="'.$target.'" class="sitemap">'.$title.'</a>';
        echo $val;
        return false;

    }
}