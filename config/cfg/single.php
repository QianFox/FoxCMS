<?php
return [
    "`column_id` int(11) DEFAULT NULL COMMENT '栏目id'",
    "`column` varchar(255) DEFAULT NULL COMMENT '栏目名称'",
    "`team_status` varchar(50) DEFAULT '' COMMENT '状态 down,del'",
    "`lang` varchar(50) DEFAULT '' COMMENT '语言标识'",
    "`content` text COMMENT '内容'"
];