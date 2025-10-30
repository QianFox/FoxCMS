<?php

/**
 * 友情链接
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2024/5/9   7:56
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2024/5/9   7:56
 */

namespace app\taglib\fox;

class TagLink extends TagBase
{

    public function getList($param, $ob)
    {
        $visit_lang = $this->getLang(); //语言
        $where = [];
        $where[] = ['status', '=', 1];
        $where[] = ['lang', '=', $visit_lang];
        $list = \app\common\model\Link::where($where)->order($ob)->select();
        return $list;
    }
}