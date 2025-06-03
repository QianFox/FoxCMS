<?php
/*
 * @Descripttion : 
 * @Author       : QianFox Team
 * @Date         : 2022-09-21 23:52:00
 * @LastEditors  : QianFox Team
 * @LastEditTime : 2022-09-28 09:36:28
 */


namespace app\taglib\fox;


class TagBase
{
    /**
     * 搜索条件
     * @param $request
     * @param $type 0:表示拿key-val;1:表示拿val
     */
    public function getSearch($request, $type=0){

        $param = $request->param();
        $fieldStr = $param['fields'];
        $kwtype = $param['kwtype']??0;//区分查询方式 0：字段一一对应。1：混合查询
        $fieldArr = explode(",", $fieldStr);

        $where = [];
        $val = [];
        if(sizeof($fieldArr) > 0){
            if($kwtype == 0){//字段一一对应
                foreach ($fieldArr as $field){
                    if(!empty($field)){
                        $fieldVal = $param[$field];
                        if(empty($fieldVal)){
                            continue;
                        }
                        $where[] = ["{$field}", 'like', '%'.$fieldVal."%"];
                        $val[] = $fieldVal;
                    }
                }
            }elseif($kwtype == 1){
                $keyword = $param['keyword'];
                $fields = implode("|", $fieldArr);
                $where[] = ["{$fields}", 'like', '%'.$keyword."%"];
                $val[] = $keyword;
            }
        }

        if($type == 0){
            return $where;
        }elseif($type == 1){
            return $val;
        }
        return [];
    }

    public function getLang(){
        $lang = request()->param("lang");
        if(empty($lang)){
            $lang = xn_cfg("base.home_lang");
        }
        return $lang;
    }

}