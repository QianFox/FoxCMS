<?php
return [
    "`column_id` int(11) DEFAULT NULL COMMENT '栏目id'",
    "`column` varchar(255) DEFAULT NULL COMMENT '栏目名称'",
    "`tags` varchar(255) DEFAULT NULL COMMENT '文档标签'",
    "`title` tinytext COMMENT '标题'",
    "`brief_title` varchar(255) DEFAULT NULL COMMENT '简略标题'",
    "`pic_ids` varchar(255) DEFAULT NULL COMMENT '文章ids'",
    "`breviary_pic_id` int(11) DEFAULT NULL COMMENT '文章缩略图id'",
    "`click` int(11) DEFAULT '0' COMMENT '浏览量'",
    "`article_field` varchar(20) DEFAULT NULL COMMENT '文章属性(t:头条;c:推荐;h:热门;b:加粗;s:幻灯)'",
    "`keywords` varchar(255) DEFAULT NULL COMMENT 'SEO关键词'",
    "`description` varchar(255) DEFAULT NULL COMMENT 'SEO描述'",
    "`article_source` varchar(255) DEFAULT NULL COMMENT '文章来源'",
    "`author` varchar(255) DEFAULT NULL COMMENT '作者'",
    "`content` text COMMENT '内容'",
    "`team_status` varchar(50) DEFAULT '' COMMENT '状态 down,del'",
    "`lang` varchar(50) DEFAULT '' COMMENT '语言标识'",
    "`release_time` datetime DEFAULT NULL COMMENT '发布时间'"
];