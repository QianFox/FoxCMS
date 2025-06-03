<?php


namespace app\taglib\fox;

use app\common\model\Column;
use think\facade\Db;

/**
 * 搜索
 * Class TagSearchform
 * @package app\taglib\fox
 */
class TagSearchform extends TagBase
{

    public function getList($param){

        $typeid = $param['typeid'];
        $model = $param['model'];
        if(empty($typeid)){
            $action = request()->action();
            $id = request()->param("id");
            if($action == "detail"){//详情
                if(empty($model)){
                    $model = strtolower(request()->controller());
                }
                $columnData = Db::name($model)->find($id);
                if(!$columnData){
                    return [];
                }
                $id = $columnData['column_id'];
            }
            $typeid = (String)$id;
        }

        $params = request()->param();//参数
        $urlParam = request()->param();

        $home_lang = xn_cfg("base.home_lang");//默认语言
        $cur_lang = $this->getLang();
        if($home_lang != $cur_lang){
            $params['lang'] = $cur_lang;
            $urlParam['lang'] = $cur_lang;
        }
        if(!empty($model)){
            $params['model'] = $model;
        }

        $baseurl = request()->domain();
        if(!check_url($baseurl."/plus/Access/check")){
            $basepath = $baseurl."/index.php/Search";
        }else{
            $basepath = $baseurl."/Search";
        }

        if(empty($typeid) || empty($model)){
            $paramStr = func_param_pack($params, "&");//参数串
            $path = $basepath."?".$paramStr;
            return [["link"=>$path, "model"=> "", "lang"=>$cur_lang, 'param'=>$urlParam, 'baselink'=>$basepath]];
        }
        $column = Column::find($typeid);
        $params["model"] = $model;
        $lang = "";
        if(!empty($column["lang"])){
            $lang = $column["lang"];
            $urlParam['lang'] = $lang;
        }
        return [["link"=>$basepath, "model"=> $model,"lang"=>$lang, 'param'=>$urlParam, 'baselink'=>$basepath]];
    }
}