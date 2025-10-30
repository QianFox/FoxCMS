<?php

namespace app\taglib\fox;

/**
 * 首页地址
 */
class TagIndex extends TagBase
{
    /**
     * 查询数据
     */
    public function getList($param)
    {
        $path = $param['path'];
        $visit_lang = $this->getLang();//语言
        $baseurl = request()->domain();//基本路径
        $name = $param['name'];
        if(empty($name)){
            echo "缺少地址";
            return false;
        }
        $url = "/{$name}";
        $url = replaceSymbol($url);
        if(!empty($path)){
            $url = $path;
        }else{
            if(!check_url($baseurl."/plus/Access/check")){
                $url = "/index.php/{$url}";
            }
        }
        $url = resetIndexUrl($url, $visit_lang);
        echo $url;
        return false;

    }
}