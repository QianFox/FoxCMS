<?php
// +----------------------------------------------------------------------
// | 模板设置
// +----------------------------------------------------------------------

return [
    // 模板引擎类型使用Think
    'type'          => 'Think',
    // 默认模板渲染规则 1 解析为小写+下划线 2 全部转换小写 3 保持操作方法
    'auto_rule'     => 1,
    // 模板目录名
    'view_dir_name' => 'view',
    // 模板后缀
    'view_suffix'   => 'html',
    // 模板文件名分隔符
    'view_depr'     => DIRECTORY_SEPARATOR,
    // 模板引擎普通标签开始标记
    'tpl_begin'     => '{',
    // 模板引擎普通标签结束标记
    'tpl_end'       => '}',
    // 标签库标签开始标记
    'taglib_begin'  => '{',
    // 标签库标签结束标记
    'taglib_end'    => '}',
    //预先加载
//    'taglib_pre_load'=>'app\taglib\Fox',
//内置标签库名称(标签使用不必指定标签库名称),以逗号分隔 注意解析顺序
//    'taglib_build_in'=>'Cx,app\taglib\Fox',
    //是否开启模板编译缓存,设为false则每次都会重新编译
    'tpl_cache'=>false,
    //默认模板缓存后缀
    'cache_suffix'=>'php'

];
