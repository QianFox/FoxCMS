<?php

namespace app\taglib\fox;
use app\common\model\Column;
use think\facade\Db;

/**
 * 图片组
 */
class TagImagegroup
{

    /**
     * 查询文章数据
     */
    public function getList($param)
    {
        $typeid = $param["typeid"];//有图片组的数据id
        $typeidP = $param["typeidP"];
        $field = $param["field"];//字段
        $modelC = $param["modelC"];//模型2
        $model = $param["model"];//模型
        $sortorder = $param["sortorder"];//排序字段

        if(!empty($typeidP)){
            $typeid = $typeidP;
        }

        if(empty($typeid)){
            $typeid = \request()->param("id");
        }
        if(!empty($modelC)){
            $model = $modelC;
        }

        if(empty($model) || empty($typeid)){
            echo "fox:imagegroup标签缺少模型或数据ID";
            return false;
        }

        if(empty($field)){
            echo "fox:imagegroup标签必须告知那个字段是图片组";
            return false;
        }

        $fdata = Db::name($model)->field($field)->find($typeid);
        if($fdata){
            $imgGStr  = $fdata[$field];
            $imgGArr = explode(",", $imgGStr);
            $rdata = [];
            foreach ($imgGArr as $img){
                array_push($rdata, ["imgsrc"=> $img]);
            }
            if($sortorder == "desc"){
                $rdata = array_reverse($rdata);
            }
            return $rdata;
        }else{
            return [];
        }
    }

}