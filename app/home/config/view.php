<?php
// +----------------------------------------------------------------------
// | 模板设置
// +----------------------------------------------------------------------

// 查找所有系统设置表数据
$template = \app\common\model\Template::where('run_status',1)->find();

return [
    // 模板路径
    'view_path'       => root_path() . 'templates' . DIRECTORY_SEPARATOR. $template['template'].DIRECTORY_SEPARATOR . $template['html'] . DIRECTORY_SEPARATOR,
    // 模板后缀
//    'view_path'       => public_path() . 'templates' . DIRECTORY_SEPARATOR . $template['template'] . DIRECTORY_SEPARATOR . app('http')->getName() . DIRECTORY_SEPARATOR . $template['html'] . DIRECTORY_SEPARATOR,
    // 模板文件名分隔符
//    'view_depr'       => '_',
// 模板引擎普通标签开始标记
    'tpl_begin'     => '[',
    // 模板引擎普通标签结束标记
    'tpl_end'       => ']',
    // 自定义标签库
    'taglib_pre_load' => 'app\taglib\Fox',
    'tpl_replace_string'=>[
        '_STATIC_'=>'/templates'
    ],
    'tpl_cache'=>false,

    'default_filter'     => '', // 默认过滤方法 用于普通标签输出
];


