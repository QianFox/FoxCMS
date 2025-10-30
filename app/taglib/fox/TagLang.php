<?php

namespace app\taglib\fox;

/**
 * 语言翻译
 */
class TagLang extends TagBase
{
    public function getList($param){
        $name = trim($param['name']);
        $visit_lang = $this->getLang();
        $valArr = getLangContentByMark($visit_lang, $name);
        $content = "";
        if(sizeof($valArr) > 0){
            $content = $valArr['value'];
        }
        echo $content;
        return false;
    }

}