<?php

return [
    // 默认磁盘
    'default' => env('filesystem.driver', 'local'),
    // 磁盘列表
    'disks'   => [
        'local'  => [
            'type' => 'local',
            'root' => app()->getRuntimePath() . 'storage',
        ],
        'public' => [
            // 磁盘类型
            'type'       => 'local',
            // 磁盘路径
            'root'       => app()->getRootPath()."uploads",
            // 磁盘路径对应的外部URL路径
            'url'        => DIRECTORY_SEPARATOR."uploads",
            // 可见性
            'visibility' => 'public',
            'template'
        ],
        //根路径文件夹
        'folder' => 'uploads',

        // 更多的磁盘配置信息
    ],
    //模板目录
    'template'=>[
        "explain"=>'explain.txt',//模板说明文件
        'thumb'=>'thumb',//缩略图名字
        'detail'=>'detail',//详细图名字
    ],
    //备份数据
    'sql'=>[
        'model'=>"data"//模型数据路径
    ]
];
