<?php

namespace app\common\model;

use app\common\model\DictType as DictTypeModel;
use think\Model;


class VariateField extends Model
{
    protected $autoWriteTimestamp = "datetime";
    protected $append = ['statustext',"Category","grouptext"];

    protected static function init()
    {
        parent::init();
    }

    public function getStatustextAttr($value,$data)
    {
        $status = [0=>'禁用',1=>'启用'];
        return $status[$data['status']];
    }

    public function getGrouptextAttr($value,$data)
    {
        $variateList = DictTypeModel::where('dict_type', 'variate')->with('dictDatas')->find()->dictDatas;//变量所属组
        $groupText = "";
        foreach ($variateList as $dl){
            if($data['group'] == $dl['dict_value']){
                $groupText = $dl['dict_label'];
                break;
            }
        }
        return $groupText;
    }

    public function getCategoryAttr($value,$data)
    {
        $systemtexts = [0=>'自定义',1=>'系统'];
        return $systemtexts[$data['is_system']];
    }

}