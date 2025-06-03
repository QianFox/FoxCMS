<?php

namespace app\taglib\fox;
use app\common\model\UploadFiles;
use think\facade\Db;

/**
 * 图片集
 */
class TagImageslist
{

    /**
     * 查询文章数据
     */
    public function getList($param)
    {
        $typeid = $param["typeid"];//有图片集的数据id
        $calltype = $param["calltype"];//标签使用处境
        $typeidP = $param['typeidP'];//父栏目id
        $modelC = $param["modelC"];//父图片模型
        $model = $param["model"];//类似图片模型
        if(!empty($modelC)){
            $model = $modelC;
        }else{
            if(!in_array($model, ["images","product"])){
                $model = "";
            }
        }
        if($calltype == "self"){
            if(empty($model)){
                $model = strtolower(request()->controller());
            }
        }elseif ($calltype == "parent"){
            if(!empty($typeidP)){
                $typeid = $typeidP;
            }
        }else{
            echo 'imageslist标签，calltype传入参数错误。';
            return false;
        }
        $action = request()->action();
        if($action == "detail"){//在页面详情
            if(empty($typeid)){
                $typeid = request()->param("id");
            }
            if(empty($model)){
                $model = strtolower(request()->controller());
            }
        }

        if(empty($model) || empty($typeid)){
            echo '标签imageslist报错：参数问题';
            return false;
        }

        $curModelData = Db::name($model)->find($typeid);//当前模型数据
        if(empty($curModelData['picset_ids'])){
            return [];
        }
        $uploadFiles = UploadFiles::whereIn("id", $curModelData['picset_ids'])->select();
        $rlist = [];
        $pic_idArr = explode(",", $curModelData['picset_ids']);
        foreach ($pic_idArr as $pic_id){
            if(!empty($pic_id)){
                foreach ($uploadFiles as $uf){
                    if($pic_id == $uf->id){
                        array_push($rlist, ["imgsrc"=>$uf["url"]]);
                        break;
                    }
                }
            }
        }
        return $rlist;
    }

}