<?php

namespace app\taglib\fox;
use app\common\model\Contact;
use app\common\model\VariateField;

/**
 * 联系方式
 */
class TagContact extends TagBase
{
    /**
     * 查询数据
     */
    public function getList($param)
    {

        $visit_lang = $this->getLang();
        $field = $param['field'];
        $contacts = Contact::where('lang', $visit_lang)->limit(1)->select();
        if(sizeof($contacts) == 1){
            $contact = $contacts[0];
            $vf = VariateField::where("name","{$field}")->where("group", "contact")->find();
            if($vf){
                if($vf["dtype"] == "media"){
                    $mediaArr = json_decode($contact->$field);
                    if(sizeof($mediaArr) > 0){
                        $media = $mediaArr[0];
                        echo $media->url;
                        return false;
                    }
                }
            }
            echo $contact->$field;
            return false;
        }else{
            echo "";
            return false;
        }
    }

}