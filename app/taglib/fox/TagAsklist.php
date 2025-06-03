<?php

namespace app\taglib\fox;
use think\facade\Db;

/**
 * 问答列表
 */
class TagAsklist extends TagBase
{

    /**
     * 查询问答数据
     */
    public function getList($param, $ob="sort desc", $offset=0,  $row=10)
    {
        $visit_lang = $this->getLang();//语言
        $where = $this->getSearch(request());//增加搜索条件
        $askId = $param["typeid"];//问答id
        $askIdP = $param["typeidP"];//父问答id
        $calltype = $param["calltype"];//标签调用方式

        if(($calltype != "self") && empty($askId) && !empty($askIdP)){
            $askId = (String)$askIdP;
        }
        $limit = $param["limit"];
        if(empty($askId)){
            $askId = \request()->param("id");
        }
        if(empty($askId)){
            echo '标签Asklist报错：没找到问答ID。';
            return false;
        }
        $query = Db::name('ask_problem')->where($where)->whereIn("ask_id", $askId);
        $query->where("lang", $visit_lang);

        if(!empty($limit)){
            $limitArr = explode(",", $limit);
            if(sizeof($limitArr) == 1){
                $offset = $limitArr[0];
                $row = $query->count();
            }elseif (sizeof($limitArr) == 2){
                $offset = $limitArr[0];
                $row = $limitArr[1];
            }
        }

        return $query->order($ob)->limit($offset, $row)->select()->each(function ($item) use ($visit_lang){
            $item['visit_lang'] = $visit_lang;
            return $item;
        });
    }

}