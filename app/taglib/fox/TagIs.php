<?php

namespace app\taglib\fox;

/**
 * 判断地址栏参数是否存在
 */
class TagIs
{
    /**
     * 查询数据
     */
    public function getList($param)
    {
        $params = request()->param();//参数
        $currParamVal = $params['param']??"";
        $paramItem = ['param'=>$currParamVal];
        $rdataList = [];
        array_push($rdataList, $paramItem);
        return $rdataList;
    }
}