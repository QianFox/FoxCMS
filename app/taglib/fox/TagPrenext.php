<?php
/*
 * @Descripttion : QianFox让数字化营销更简单
 * @Author       : QianFox Team
 * @Date         : 2024-05-07 17:14:36
 * @Version      : V1.24
 * @Copyright    : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditors  : QianFox Team
 * @LastEditTime : 2024-05-11 10:50:37
 */

namespace app\taglib\fox;
use app\common\model\Column;
use think\facade\Db;

/**
 * 上一页下一页
 */
class TagPrenext extends TagBase
{

    /**
     * 查询栏目数据
     */
    public function getList($param, $ob="create_time desc")
    {
        $visit_lang = $this->getLang();
        $id = $param["id"];
        $action = \request()->action();
        if(empty($id) || $action != "detail"){//详情
            echo '标签prenext报错：只适用在详情之后。';
            return false;
        }
        $columnModel = strtolower(request()->controller());
        $cm =  Db::name($columnModel)->field("column_id")->find($id);//内容数据
        if(!$cm){
            return ["id"=>-1];
        }
        $column_id = $cm['column_id'];//栏目id
        $datalist = Db::name($columnModel)->where("column_id", $column_id)->where('lang', $visit_lang)->order($ob)->select()->each(function ($item) use ($visit_lang){
            $item['visit_lang'] = $visit_lang;
            return $item;
        });
        $get = $param["get"];
        $disstyle = $param["disstyle"];
        
        $title = $index_name = getLangContentByMark($visit_lang, "no_more")['value'];
        $rdata = ["id"=>-1, "disstyle"=>$disstyle, "title"=> $title];
        $size = sizeof($datalist);
        foreach ($datalist as $key=>$data){
            if($id == $data["id"]){
                if($get == "pre"){//上一页
                    if($key > 0){
                        $rdata = $datalist[($key-1)];
                    }
                }elseif ($get == "next"){//下一页
                    if(($key + 1) < $size){
                        $rdata = $datalist[($key + 1)];
                    }
                }
                break;
            }
        }
        $resultList = [];
        array_push($resultList, $rdata);
        return $resultList;
    }

}