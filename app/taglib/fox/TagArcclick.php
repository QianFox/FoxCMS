<?php

namespace app\taglib\fox;

use app\common\model\Column;
use think\facade\Db;

/**
 * 在内容页模板追加显示浏览量
 */
class TagArcclick
{

    /**
     * 在内容页模板追加显示浏览量
     */
    public function getList($param)
    {
        $type = $param["type"];
        if (!in_array($type, ['look', 'view'])) {
            return '标签arcclick报错：type属性值乱传。';
        }
        $sid = $param["sid"];
        $typeid = $param["typeid"];
        $typeidP = $param["typeidP"];
        $model = $param["model"];
        $calltype = $param["calltype"];
        $column_id = $param["column_id"];

        if($calltype == "parent"){//父类继承
            if(empty($typeid) &&!empty($typeidP)){
                $typeid = $typeidP;
            }
        }else{
            if(empty($typeid)){
                $typeid = \request()->param("id");
            }
        }

        if(empty($model)){
            if($calltype == "parent"){
                if(!empty($column_id)){
                    $column = Db::name("column")->field("column_model")->find($column_id);
                    if($column){
                        $model = $column['column_model'];
                    }
                }else{
                    $model = strtolower(request()->controller());
                }
            }else{
                $model = strtolower(request()->controller());
            }
        }
        if (empty($typeid)) {
            return '标签arcclick报错：缺少属性 typeid值。';
        }
        if (empty($model)) {
            return '标签arcclick报错：缺少属性 $model。';
        }

        $basic = getBasic();
        if(empty($basic['url'])){
            $url = \request()->domain();
            $baseurl = request()->domain();//基本路径
            if(!check_url($baseurl."/plus/Access/check")){
                $url = "{$url}/index.php";
            }
        }else{
            $url = $basic['url_prefix'].$basic['url'];
        }
        $url = $url.url("/plus/Arcclick/index")."?typeid={$typeid}&model={$model}&type={$type}";
        $parseStr = "<script src='{$url}' type='text/javascript' language='javascript'></script>";
        return $parseStr;
    }
}