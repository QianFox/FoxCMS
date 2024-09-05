<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   18:22
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   18:22
 */

namespace app\common\model;

use think\Model;

// 单页模板
class Custom extends Model
{
    // 追加属性
    protected $append = ['column'];

    // 栏目
    public function getColumnAttr($value, $data)
    {
        if (empty($data['column_id'])) {
            return '';
        }
        return Column::field('name')->find($data['column_id'])['name'];
    }
}