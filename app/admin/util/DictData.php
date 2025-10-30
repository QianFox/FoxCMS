<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   15:50
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   15:50
 */

namespace app\admin\util;

use app\common\model\DictData as DictDataModel;
use app\common\model\DictType as DictTypeModel;

// 公共字典数据查询类
class DictData
{
    // 根据字典类型获取对应字典数据 (dict_type与dict_data关联查询)
    public function getDictDatasRelation($dictType, $resultField = '')
    {
        $dictDatas = [];
        if (empty($resultField)) {
            $dictDatas = DictTypeModel::where('dict_type', $dictType)->with('dictDatas')->find()->dictDatas;
        } else {
            $dictDatas = DictTypeModel::field($resultField)->where('dict_type', $dictType)->with('dictDatas')->find()->dictDatas;
        }
        $this->success($dictDatas);
    }

    // 根据字典类型获取对应字典数据 (dict_data查询)
    public function getDictDatas($dictType, $resultField = '')
    {
        $dictDatas = [];
        if (empty($resultField)) {
            $dictDatas = DictDataModel::where('dict_type', $dictType)->select();
        } else {
            $dictDatas = DictDataModel::field($resultField)->where('dict_type', $dictType)->select();
        }
        return $dictDatas;
    }

    // 获取模型文件
    public function getModelHtml($dictType, $isView)
    {

        $column_template = "list_model.html"; //栏目模板
        $model_template = "view_model.html"; //文档模板
        if ($isView == 0) { //无
            $column_template = str_replace("model", $dictType, $column_template);
            $model_template = "";
        } else { //是
            $column_template = str_replace("model", $dictType, $column_template);
            $model_template = str_replace("model", $dictType, $model_template);
        }
        return ['column_template' => $column_template, 'model_template' => $model_template, 'is_view' => $isView];
    }
}