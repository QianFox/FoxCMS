<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   14:08
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   14:08
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\facade\Db;
use think\facade\View;

// 模型
class ModelRecord extends AdminBase
{
    public function index()
    {
        $param = $this->request->param();
        if (array_key_exists('bcid', $param)) {
            View::assign('bcid', $param['bcid']);
        }
        if ($this->request->isAjax()) {
            $this->success('栏目查询成功', '', null);
        }
        return view('index');
    }

    public function add()
    {
        $sql = <<<EOF
        CREATE TABLE `fox_model_t`  (
          `id` int(0) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
          `model_id` int(0) NULL DEFAULT 0 COMMENT '模型ID',
          `field_id` int(0) NULL DEFAULT 0 COMMENT '自定义字段ID',
          `create_time` datetime(0) NULL COMMENT '新增时间',
          `update_time` datetime(0) NULL COMMENT '更新时间',
          PRIMARY KEY (`id`) USING BTREE
        ) ENGINE = MyISAM AUTO_INCREMENT = 280 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '模型与自定义字段绑定表' 
EOF;
        try {

            $ex =  Db::execute($sql);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return $ex;
    }
}