-- 表数据结构内容

DROP TABLE IF EXISTS `fox_access_log`;
CREATE TABLE `fox_access_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(255) DEFAULT NULL COMMENT '搜索ip',
  `browser` varchar(255) DEFAULT NULL COMMENT '访问浏览器',
  `from_page` varchar(255) DEFAULT NULL COMMENT '来源页面',
  `search_content` text COMMENT '搜索内容',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='授权访问记录';


DROP TABLE IF EXISTS `fox_access_stat`;
CREATE TABLE `fox_access_stat` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `page_title` varchar(255) DEFAULT NULL COMMENT '网页标题',
  `ip` varchar(255) DEFAULT NULL COMMENT '搜索ip',
  `browser` varchar(255) DEFAULT NULL COMMENT '访问浏览器',
  `from_page` varchar(255) DEFAULT NULL COMMENT '来源页面',
  `source_site` varchar(255) DEFAULT NULL COMMENT '来源网站',
  `browser_type` varchar(255) DEFAULT NULL COMMENT '浏览器类型',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `cookie` varchar(255) DEFAULT NULL COMMENT 'cookie访问状态值',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='访问统计';


DROP TABLE IF EXISTS `fox_admin`;
CREATE TABLE `fox_admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nickname` varchar(255) DEFAULT NULL COMMENT '昵称',
  `username` varchar(60) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(64) NOT NULL DEFAULT '' COMMENT '登录密码；mb_password加密',
  `avatar` varchar(255) DEFAULT '' COMMENT '用户头像，相对于upload/avatar目录',
  `email` varchar(100) DEFAULT '' COMMENT '登录邮箱',
  `phone` bigint(20) unsigned DEFAULT NULL COMMENT '手机号',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '用户状态 0：禁用； 1：启用',
  `register_time` datetime DEFAULT NULL COMMENT '注册时间',
  `last_login_ip` varchar(16) DEFAULT '' COMMENT '最后登录ip',
  `last_login_time` datetime DEFAULT NULL COMMENT '最后登录时间',
  `remark` varchar(100) DEFAULT NULL COMMENT '备注',
  `lock_time` datetime DEFAULT NULL COMMENT '账号锁定时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `user_login_key` (`username`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='后台用户表';


DROP TABLE IF EXISTS `fox_admin_log`;
CREATE TABLE `fox_admin_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) NOT NULL,
  `url` varchar(512) DEFAULT '',
  `remark` varchar(512) DEFAULT NULL,
  `ip` char(15) DEFAULT NULL,
  `create_time` datetime DEFAULT NULL,
  `method` varchar(10) DEFAULT NULL,
  `param` text,
  `type` varchar(255) DEFAULT 'system',
  `content` text COMMENT '内容',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='后台操作日志';


DROP TABLE IF EXISTS `fox_advertising_space`;
CREATE TABLE `fox_advertising_space` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '名称',
  `type` tinyint(4) DEFAULT '0' COMMENT '类型 默认0',
  `sort` int(11) DEFAULT NULL COMMENT '排序',
  `remark` varchar(255) DEFAULT NULL COMMENT '描述',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态1显示；0：隐藏',
  `sid` varchar(255) DEFAULT NULL COMMENT '标识id',
  `lang` varchar(50) DEFAULT NULL COMMENT '语言',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='广告位';


DROP TABLE IF EXISTS `fox_advertising_space_field`;
CREATE TABLE `fox_advertising_space_field` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `advertising_space_id` int(255) DEFAULT NULL COMMENT '广告位id',
  `name` varchar(32) DEFAULT '' COMMENT '字段名称',
  `title` varchar(250) DEFAULT '' COMMENT '字段标题',
  `dtype` varchar(32) DEFAULT '' COMMENT '字段类型',
  `define` text COMMENT '字段定义',
  `maxlength` int(11) DEFAULT '0' COMMENT '最大长度，文本数据必须填写，大于255为text类型',
  `dfvalue` text COMMENT '默认值',
  `remark` varchar(256) DEFAULT '' COMMENT '提示说明',
  `sort_order` int(11) DEFAULT '1' COMMENT '排序',
  `lang` varchar(255) DEFAULT NULL COMMENT '语言',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='广告位自定义字段表';


DROP TABLE IF EXISTS `fox_apply`;
CREATE TABLE `fox_apply` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `apply_menu_id` int(11) DEFAULT NULL COMMENT '应用菜单默认id',
  `column_id` int(11) DEFAULT NULL COMMENT '原应用id',
  `mark` varchar(255) DEFAULT NULL COMMENT '应用标志',
  `name` varchar(255) DEFAULT NULL COMMENT '名称',
  `describe` varchar(255) DEFAULT NULL COMMENT '描述',
  `icon` varchar(255) DEFAULT NULL COMMENT '图标',
  `path` varchar(255) DEFAULT NULL COMMENT '调转路径',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态1显示；0：隐藏',
  `sort` int(11) DEFAULT '0' COMMENT '排序',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `type_desc` varchar(255) DEFAULT NULL COMMENT '应用类型描述',
  `type` tinyint(255) DEFAULT '0' COMMENT '应用类型0:系统内置；1:免费应用；2：商业应用；3：扩展应用;',
  `link` varchar(255) DEFAULT NULL COMMENT '链接地址',
  `price` decimal(10,2) DEFAULT NULL COMMENT '价格',
  `version` varchar(255) DEFAULT NULL COMMENT '版本',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='插件应用表';


DROP TABLE IF EXISTS `fox_apply_menu`;
CREATE TABLE `fox_apply_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `mark` varchar(255) DEFAULT NULL COMMENT '应用标志',
  `column_id` int(11) DEFAULT NULL COMMENT '原应用id',
  `apply_plugin_id` int(11) DEFAULT NULL COMMENT '应用插件id',
  `pid` int(10) unsigned DEFAULT '0' COMMENT '父级id',
  `tier` varchar(255) DEFAULT NULL COMMENT '层级集合',
  `name` char(80) DEFAULT '' COMMENT '规则唯一标识',
  `title` char(20) DEFAULT '' COMMENT '规则中文名称',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态：为1正常，为0禁用',
  `is_menu` tinyint(3) unsigned DEFAULT '0' COMMENT '菜单显示 0:菜单显示；1：不是菜单',
  `condition` char(100) DEFAULT '' COMMENT '规则表达式，为空表示存在就验证，不为空表示按照条件验证',
  `type` varchar(10) DEFAULT 'M' COMMENT '菜单类型（M目录 C菜单 B按钮）',
  `sort` int(11) DEFAULT '1' COMMENT '排序',
  `icon` varchar(40) DEFAULT '' COMMENT '图标',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `create_by` varchar(64) DEFAULT '' COMMENT '创建者',
  `update_by` varchar(64) DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='应用菜单表';


DROP TABLE IF EXISTS `fox_apply_table`;
CREATE TABLE `fox_apply_table` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `apply_plugin_id` int(11) DEFAULT NULL COMMENT '应用插件id',
  `description` varchar(255) DEFAULT NULL COMMENT '应用标志',
  `name` varchar(255) DEFAULT NULL COMMENT '表名',
  `table` varchar(255) DEFAULT NULL COMMENT '表',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='应用表记录表';


DROP TABLE IF EXISTS `fox_article`;
CREATE TABLE `fox_article` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `column_id` int(11) DEFAULT NULL COMMENT '栏目id',
  `column` varchar(255) DEFAULT NULL COMMENT '栏目名称',
  `tags` varchar(255) DEFAULT NULL COMMENT '文档标签',
  `title` tinytext COMMENT '标题',
  `brief_title` varchar(255) DEFAULT NULL COMMENT '简略标题',
  `pic_ids` varchar(255) DEFAULT NULL COMMENT '文章ids',
  `breviary_pic_id` int(11) DEFAULT NULL COMMENT '文章缩略图id',
  `click` int(11) DEFAULT '0' COMMENT '浏览量',
  `article_field` varchar(20) DEFAULT NULL COMMENT '文章属性(t:头条;c:推荐;h:热门;b:加粗;s:幻灯)',
  `keywords` varchar(255) DEFAULT NULL COMMENT 'SEO关键词',
  `description` varchar(255) DEFAULT NULL COMMENT 'SEO描述',
  `article_source` varchar(255) DEFAULT NULL COMMENT '文章来源',
  `author` varchar(255) DEFAULT NULL COMMENT '作者',
  `content` text COMMENT '内容',
  `team_status` varchar(50) DEFAULT '' COMMENT '状态 down,del',
  `release_time` datetime DEFAULT NULL COMMENT '发布时间',
  `lang` varchar(50) DEFAULT NULL COMMENT '语言标识',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='文章表';


DROP TABLE IF EXISTS `fox_attachment`;
CREATE TABLE `fox_attachment` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_thumb` int(11) DEFAULT NULL COMMENT '是否开启缩略图 0：未；1：开启',
  `thumb_pic_quality` int(11) DEFAULT NULL COMMENT '1到100的整数,值越大,压缩比例越小',
  `i_suffixs` varchar(255) DEFAULT NULL COMMENT '图片文件后缀逗号隔开',
  `i_file_size` bigint(20) DEFAULT NULL COMMENT '图片文件大小kb',
  `i_reduce_size` int(11) DEFAULT NULL COMMENT '图片文件压缩大小 越大越清晰100为不压缩',
  `a_suffixs` varchar(255) DEFAULT NULL COMMENT '音频后缀',
  `a_file_size` bigint(20) DEFAULT NULL COMMENT '音频文件大小kb',
  `f_suffixs` varchar(255) DEFAULT NULL COMMENT '文件后缀',
  `file_size` bigint(20) DEFAULT NULL COMMENT '文件大小kb',
  `is_remote` tinyint(4) DEFAULT '0' COMMENT '是否开启远程远程附件0：不开启；1：开启',
  `access_key` varchar(255) DEFAULT NULL COMMENT '密钥key',
  `access_key_secret` varchar(255) DEFAULT NULL COMMENT '密钥secret',
  `is_intranet` tinyint(4) DEFAULT '0' COMMENT '内网上传0：否；1：是',
  `url_prefix` varchar(255) DEFAULT NULL COMMENT '定义域名访问路径前缀',
  `url` varchar(255) DEFAULT NULL COMMENT '定义域名访问路径',
  `is_customize` tinyint(2) DEFAULT '0' COMMENT '是否自定义域名0：否；1：是',
  `endpoint_prefix` varchar(255) DEFAULT 'http' COMMENT '默认oss访问地址前缀',
  `endpoint` varchar(255) DEFAULT NULL COMMENT '默认oss访问地址',
  `bucket` varchar(255) DEFAULT NULL COMMENT '存储空间名称 Bucket',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='附件设置';


DROP TABLE IF EXISTS `fox_auth_group`;
CREATE TABLE `fox_auth_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(100) NOT NULL DEFAULT '' COMMENT '角色名称',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '角色状态1启用；0：禁用',
  `rules` text COMMENT '规则id',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户组表';


DROP TABLE IF EXISTS `fox_auth_group_access`;
CREATE TABLE `fox_auth_group_access` (
  `admin_id` int(10) unsigned NOT NULL COMMENT '用户id',
  `group_id` int(10) unsigned NOT NULL COMMENT '用户组id',
  UNIQUE KEY `uid_group_id` (`admin_id`,`group_id`) USING BTREE,
  KEY `uid` (`admin_id`) USING BTREE,
  KEY `group_id` (`group_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户组明细表';


DROP TABLE IF EXISTS `fox_auth_rule`;
CREATE TABLE `fox_auth_rule` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(10) unsigned DEFAULT '0' COMMENT '父级id',
  `tier` varchar(255) DEFAULT NULL COMMENT '层级集合',
  `name` char(80) DEFAULT '' COMMENT '规则唯一标识',
  `title` char(20) DEFAULT '' COMMENT '规则中文名称',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态：为1正常，为0禁用',
  `is_menu` tinyint(3) unsigned DEFAULT '0' COMMENT '菜单显示 0:菜单显示；1：不是菜单',
  `condition` char(100) DEFAULT '' COMMENT '规则表达式，为空表示存在就验证，不为空表示按照条件验证',
  `type` varchar(10) DEFAULT 'M' COMMENT '菜单类型（M目录 C菜单 B按钮）',
  `sort` int(11) DEFAULT '1' COMMENT '排序 越小越靠前',
  `icon` varchar(40) DEFAULT '' COMMENT '图标',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `create_by` varchar(64) DEFAULT '' COMMENT '创建者',
  `update_by` varchar(64) DEFAULT '' COMMENT '更新者',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=119 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='规则表';


DROP TABLE IF EXISTS `fox_basic`;
CREATE TABLE `fox_basic` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '网站状态1启用；0：禁用',
  `status_desc` varchar(255) DEFAULT NULL COMMENT '状态描述',
  `url_prefix` varchar(255) DEFAULT NULL COMMENT '站点根地址',
  `url` varchar(255) DEFAULT NULL COMMENT '站点根网址',
  `name` varchar(255) DEFAULT NULL COMMENT '网站名称',
  `default_style` varchar(255) DEFAULT NULL COMMENT '网站默认风格',
  `homepage_url` varchar(255) DEFAULT NULL COMMENT '主页链接',
  `title` varchar(255) DEFAULT NULL COMMENT '首页标题',
  `login_logo` varchar(255) DEFAULT NULL COMMENT '登录logo',
  `web_logo` varchar(255) DEFAULT NULL COMMENT '网站logo',
  `web_icon` varchar(255) DEFAULT NULL COMMENT '浏览器地址栏Favicon图标ID',
  `html_save_path` varchar(255) DEFAULT '/html' COMMENT '文档HTML保存路径',
  `copyright` varchar(255) DEFAULT NULL COMMENT '网站版权信息',
  `keyword` varchar(255) DEFAULT NULL COMMENT '站点默认关键字',
  `description` varchar(255) DEFAULT NULL COMMENT '站点描述',
  `aq` varchar(255) DEFAULT NULL COMMENT '网站备案号',
  `mobile_domain` varchar(255) DEFAULT NULL COMMENT '手机域名',
  `police_aq` varchar(255) DEFAULT NULL COMMENT '公安备案号',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `lang` varchar(50) DEFAULT NULL COMMENT '语言',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='基本设置';


DROP TABLE IF EXISTS `fox_blacklist`;
CREATE TABLE `fox_blacklist` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(255) DEFAULT NULL COMMENT 'IP',
  `description` varchar(255) DEFAULT NULL COMMENT '描述',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='IP黑名单';


DROP TABLE IF EXISTS `fox_column`;
CREATE TABLE `fox_column` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT '0' COMMENT '父栏目id',
  `tier` varchar(255) DEFAULT NULL COMMENT '层级集合',
  `name` varchar(255) NOT NULL COMMENT '栏目名称',
  `level` tinyint(4) DEFAULT NULL COMMENT '栏目层级',
  `pic_ids` int(11) DEFAULT NULL COMMENT '栏目图片ids',
  `column_model` varchar(255) DEFAULT NULL COMMENT '栏目类型',
  `status` tinyint(4) DEFAULT '1' COMMENT '栏目状态 默认1：启用；0：不显示',
  `content` text COMMENT '内容',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `sort` tinyint(4) DEFAULT NULL COMMENT '排序',
  `en_name` varchar(200) DEFAULT '' COMMENT '英文名',
  `dir_path` varchar(255) DEFAULT NULL COMMENT '目录路径',
  `v_path` varchar(255) DEFAULT NULL COMMENT '访问路径',
  `column_attr` tinyint(4) DEFAULT '0' COMMENT ' 0 栏目属性0:内容栏目；1:外部链接; 2:内链栏目',
  `out_link_head` varchar(50) DEFAULT NULL COMMENT '外链地址头部',
  `out_link` varchar(255) DEFAULT NULL COMMENT '外链地址',
  `column_template` varchar(255) DEFAULT NULL COMMENT '栏目模板',
  `model_template` varchar(255) DEFAULT NULL COMMENT '模型模板',
  `seo_title` varchar(255) DEFAULT NULL COMMENT 'SEO标题',
  `keywords` varchar(255) DEFAULT NULL COMMENT 'SEO关键字',
  `description` varchar(255) DEFAULT NULL COMMENT 'SEO描述',
  `inherit_option` tinyint(4) DEFAULT '0' COMMENT '继承选项 0:不继承；1：同时更改下级栏目模板风格',
  `memberlevel` varchar(255) DEFAULT NULL COMMENT '阅读权限(即会员级别)',
  `data_limit` tinyint(2) DEFAULT '1' COMMENT '栏目内容展示(1:仅本栏目;2:本栏目及下级栏目;3:本栏目及指定子栏目)',
  `limit_column` varchar(255) DEFAULT NULL COMMENT '限制的指定子栏目',
  `inner_column` int(11) DEFAULT NULL COMMENT '内部链接时，栏目id',
  `form_list_id` int(11) DEFAULT NULL COMMENT '应用表单id',
  `lang` varchar(50) DEFAULT NULL COMMENT '语言标识',
  `nid` varchar(255) DEFAULT NULL COMMENT '标识',
  `column_icon` varchar(255) DEFAULT '' COMMENT '栏目图标',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='栏目表';


DROP TABLE IF EXISTS `fox_column_field`;
CREATE TABLE `fox_column_field` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `column_ids` varchar(255) DEFAULT '' COMMENT '栏目ids',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '字段名称',
  `title` varchar(250) NOT NULL DEFAULT '' COMMENT '字段标题',
  `dtype` varchar(32) NOT NULL DEFAULT '' COMMENT '字段类型',
  `define` text NOT NULL COMMENT '字段定义',
  `maxlength` int(11) DEFAULT '0' COMMENT '最大长度，文本数据必须填写，大于255为text类型',
  `dfvalue` text COMMENT '默认值',
  `remark` varchar(256) DEFAULT '' COMMENT '提示说明',
  `is_system` tinyint(1) DEFAULT '0' COMMENT '字段分类，1=系统(不可修改)，0=自定义',
  `sort_order` int(11) DEFAULT '100' COMMENT '排序',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态1:启用；0：禁用',
  `category` enum('column','model') DEFAULT 'column' COMMENT '字段类别默认栏目',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='栏目自定义字段表';


DROP TABLE IF EXISTS `fox_column_level`;
CREATE TABLE `fox_column_level` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `level` tinyint(4) DEFAULT NULL COMMENT '栏目层级 1:一级栏目 2：二级栏目 3：三级栏目',
  `is_thumb` tinyint(4) DEFAULT NULL COMMENT '是否开启缩略图 0：关闭  1：开启',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='栏目层级表';


DROP TABLE IF EXISTS `fox_contact`;
CREATE TABLE `fox_contact` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `map_ak` varchar(255) DEFAULT NULL COMMENT '地图AK',
  `company` varchar(255) DEFAULT NULL COMMENT '公司名称',
  `company_tel` varchar(255) DEFAULT NULL COMMENT '公司电话',
  `email` varchar(255) DEFAULT NULL COMMENT '电子邮箱',
  `linkman` varchar(255) DEFAULT NULL COMMENT '联系人',
  `phone` varchar(255) DEFAULT NULL COMMENT '手机号码',
  `qq` varchar(255) DEFAULT NULL COMMENT 'qq',
  `customer_url` varchar(255) DEFAULT NULL COMMENT '客服链接',
  `qr_code1` varchar(255) DEFAULT NULL COMMENT '二维码1',
  `qr_code2` varchar(255) DEFAULT NULL COMMENT '二维码2',
  `contact_address` varchar(255) DEFAULT NULL COMMENT '联系地址',
  `geolocation` varchar(255) DEFAULT NULL COMMENT '地理位置',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `lang` varchar(50) DEFAULT NULL COMMENT '语言',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='联系方式';


DROP TABLE IF EXISTS `fox_data_backup`;
CREATE TABLE `fox_data_backup` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT NULL COMMENT '父id',
  `backup_file` varchar(255) DEFAULT NULL COMMENT '备份文件',
  `table_name` varchar(255) DEFAULT NULL COMMENT '表名',
  `random_num` varchar(255) DEFAULT NULL COMMENT '随机字符串',
  `backup_file_path` varchar(255) DEFAULT NULL COMMENT '备份文件路径',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='数据备份';


DROP TABLE IF EXISTS `fox_demand`;
CREATE TABLE `fox_demand` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `text0` varchar(255) NOT NULL DEFAULT '' COMMENT '阁下姓名',
  `text1` varchar(255) NOT NULL DEFAULT '' COMMENT '手机号码',
  `is_view` tinyint(2) DEFAULT '0' COMMENT '是否查看',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `text2` varchar(255) NOT NULL DEFAULT '' COMMENT '需求',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='需求';


DROP TABLE IF EXISTS `fox_dict_data`;
CREATE TABLE `fox_dict_data` (
  `dict_code` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '字典编码',
  `dict_sort` int(11) DEFAULT '0' COMMENT '字典排序',
  `dict_label` varchar(100) DEFAULT '' COMMENT '字典标签',
  `dict_value` varchar(100) DEFAULT '' COMMENT '字典键值',
  `dict_type` varchar(100) DEFAULT '' COMMENT '字典类型',
  `css_class` varchar(100) DEFAULT NULL COMMENT '样式属性（其他样式扩展）',
  `list_class` varchar(100) DEFAULT NULL COMMENT '表格回显样式',
  `is_default` char(1) DEFAULT 'N' COMMENT '是否默认（Y是 N否）',
  `status` char(1) DEFAULT '0' COMMENT '状态（0正常 1停用）',
  `create_by` varchar(64) DEFAULT '' COMMENT '创建者',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_by` varchar(64) DEFAULT '' COMMENT '更新者',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `remark` varchar(500) DEFAULT NULL COMMENT '备注',
  `count` int(11) DEFAULT '0' COMMENT '数量',
  `type` varchar(255) DEFAULT 'system' COMMENT '类型：默认系统 system',
  PRIMARY KEY (`dict_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='字典数据表';


DROP TABLE IF EXISTS `fox_dict_type`;
CREATE TABLE `fox_dict_type` (
  `dict_id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT '字典主键',
  `dict_name` varchar(100) DEFAULT '' COMMENT '字典名称',
  `dict_type` varchar(100) DEFAULT '' COMMENT '字典类型',
  `status` char(1) DEFAULT '0' COMMENT '状态（0正常 1停用）',
  `create_by` varchar(64) DEFAULT '' COMMENT '创建者',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_by` varchar(64) DEFAULT '' COMMENT '更新者',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `remark` varchar(500) DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`dict_id`) USING BTREE,
  UNIQUE KEY `dict_type` (`dict_type`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='字典类型表';


DROP TABLE IF EXISTS `fox_download`;
CREATE TABLE `fox_download` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `type` tinyint(2) DEFAULT '0' COMMENT '上传类型 0:本地；1:远程',
  `column_id` int(11) DEFAULT NULL COMMENT '栏目id',
  `column` varchar(255) DEFAULT NULL COMMENT '栏目名称',
  `tags` varchar(255) DEFAULT NULL COMMENT '文档标签',
  `title` tinytext COMMENT '标题',
  `brief_title` varchar(255) DEFAULT NULL COMMENT '简略标题',
  `pic_ids` varchar(255) DEFAULT NULL COMMENT '文章ids',
  `breviary_pic_id` int(11) DEFAULT NULL COMMENT '文章缩略图id',
  `click` int(11) DEFAULT '0' COMMENT '浏览量',
  `article_field` varchar(20) DEFAULT NULL COMMENT '文章属性(t:头条;c:推荐;h:热门;b:加粗;s:幻灯)',
  `keywords` varchar(255) DEFAULT NULL COMMENT 'SEO关键词',
  `description` varchar(255) DEFAULT NULL COMMENT 'SEO描述',
  `article_source` varchar(255) DEFAULT NULL COMMENT '文章来源',
  `author` varchar(255) DEFAULT NULL COMMENT '作者',
  `content` text COMMENT '内容',
  `team_status` varchar(50) DEFAULT '' COMMENT '状态 down,del',
  `release_time` datetime DEFAULT NULL COMMENT '发布时间',
  `lang` varchar(50) DEFAULT NULL COMMENT '语言标识',
  `create_time` datetime DEFAULT NULL COMMENT '新增时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `file_name` varchar(255) DEFAULT NULL COMMENT '文件名称',
  `file_size` varchar(255) DEFAULT NULL COMMENT '文件大小',
  `file_url` varchar(255) DEFAULT NULL COMMENT '文件地址',
  `file_type` tinyint(2) DEFAULT NULL COMMENT '文件类型 0:本地；1：远程',
  `server_name` varchar(255) DEFAULT NULL COMMENT '服务器名称',
  `extract_code` varchar(255) DEFAULT NULL COMMENT '提取码',
  `download_num` int(11) DEFAULT '0' COMMENT '下载次数',
  `file_category` varchar(255) DEFAULT NULL COMMENT '文件类别',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='下载模型';


DROP TABLE IF EXISTS `fox_field_type`;
CREATE TABLE `fox_field_type` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '字段类型',
  `title` varchar(64) NOT NULL DEFAULT '' COMMENT '中文类型名',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否需要设置选项 0：否；1:是',
  `sort_order` int(11) NOT NULL DEFAULT '0' COMMENT '排序',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `name` (`name`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='字段类型表';


DROP TABLE IF EXISTS `fox_form_field`;
CREATE TABLE `fox_form_field` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `form_list_id` int(11) DEFAULT '0' COMMENT '表单id',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '字段名称',
  `title` varchar(250) NOT NULL DEFAULT '' COMMENT '字段标题',
  `dtype` varchar(32) NOT NULL DEFAULT '' COMMENT '字段类型',
  `define` text NOT NULL COMMENT '字段定义',
  `maxlength` int(11) DEFAULT '0' COMMENT '最大长度，文本数据必须填写，大于255为text类型',
  `val` varchar(255) DEFAULT NULL COMMENT '值',
  `dfvalue` text COMMENT '默认值',
  `marke_word` varchar(255) DEFAULT NULL COMMENT '提示语',
  `remark` varchar(256) DEFAULT '' COMMENT '提示说明',
  `sort_order` int(11) DEFAULT '100' COMMENT '排序',
  `is_require` tinyint(1) DEFAULT '1' COMMENT '是否必填  0：否 1：是',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='自定义表单字段绑定表';


DROP TABLE IF EXISTS `fox_form_list`;
CREATE TABLE `fox_form_list` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '提交设置  0：不限制',
  `name` varchar(255) DEFAULT NULL COMMENT '名称',
  `table_name` varchar(255) DEFAULT NULL COMMENT '表名',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `commit_type` tinyint(4) DEFAULT '0' COMMENT '提交设置  0：不限制 1：5分钟内限提交1次',
  `notice_setting` tinyint(4) DEFAULT '0' COMMENT '消息通知设置 0：关闭  1：开启',
  `verify` tinyint(2) DEFAULT '0' COMMENT '安全验证0：关闭；1：开启',
  `email_setting` tinyint(2) DEFAULT '0' COMMENT '邮件通知0：关闭；1：开启',
  `template_id` int(11) DEFAULT NULL COMMENT '模板id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='表单列表';


DROP TABLE IF EXISTS `fox_images`;
CREATE TABLE `fox_images` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `column_id` int(11) DEFAULT NULL COMMENT '栏目id',
  `column` varchar(255) DEFAULT NULL COMMENT '栏目名称',
  `title` tinytext COMMENT '标题',
  `tags` varchar(255) DEFAULT NULL COMMENT '文档标签',
  `brief_title` varchar(255) DEFAULT NULL COMMENT '简略标题',
  `picset_ids` varchar(255) DEFAULT NULL COMMENT '图片集ids',
  `pic_ids` varchar(255) DEFAULT NULL COMMENT '详情ids',
  `breviary_pic_id` int(11) DEFAULT NULL COMMENT '文章缩略图id',
  `click` int(11) DEFAULT '0' COMMENT '浏览量',
  `article_field` varchar(20) DEFAULT NULL COMMENT '文章属性(t:头条;c:推荐;h:热门;b:加粗;s:幻灯)',
  `keywords` varchar(255) DEFAULT NULL COMMENT 'SEO关键词',
  `description` varchar(255) DEFAULT NULL COMMENT 'SEO描述',
  `article_source` varchar(255) DEFAULT NULL COMMENT '文章来源',
  `author` varchar(255) DEFAULT NULL COMMENT '作者',
  `content` text COMMENT '内容',
  `team_status` varchar(50) DEFAULT '' COMMENT '状态 down,del',
  `release_time` datetime DEFAULT NULL COMMENT '发布时间',
  `lang` varchar(50) DEFAULT NULL COMMENT '语言标识',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='文章表';


DROP TABLE IF EXISTS `fox_link`;
CREATE TABLE `fox_link` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sort` int(11) DEFAULT NULL COMMENT '排序',
  `title` varchar(255) DEFAULT NULL COMMENT '标题',
  `img_url` varchar(255) DEFAULT NULL COMMENT '图片',
  `link` varchar(255) DEFAULT NULL COMMENT '链接',
  `type` tinyint(4) DEFAULT '0' COMMENT '类型 默认0',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态1显示；0：隐藏',
  `lang` varchar(255) DEFAULT NULL COMMENT '语言',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='友情链接';


DROP TABLE IF EXISTS `fox_member_level`;
CREATE TABLE `fox_member_level` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT '' COMMENT '名称',
  `level` int(11) DEFAULT NULL COMMENT '会员等级',
  `discount_rate` decimal(10,0) DEFAULT NULL COMMENT '折扣率',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态 默认1：启用；0：禁用',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `name` (`name`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='会员级别表';


DROP TABLE IF EXISTS `fox_member_model`;
CREATE TABLE `fox_member_model` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nid` varchar(50) NOT NULL DEFAULT '' COMMENT '识别id',
  `name` varchar(30) DEFAULT '' COMMENT '名称',
  `table` varchar(50) DEFAULT '' COMMENT '表名',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态(1=启用，0=屏蔽)',
  `is_system` tinyint(1) DEFAULT '0' COMMENT '字段分类，1=系统(不可修改)，0=自定义',
  `is_delete` tinyint(1) DEFAULT '0' COMMENT '伪删除，1=是，0=否',
  `sort_order` smallint(6) DEFAULT '50' COMMENT '排序',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `member_type` tinyint(4) DEFAULT '3' COMMENT '会员类型（具体值可参照字典表）',
  `member_type_desc` varchar(255) DEFAULT NULL COMMENT '会员类型描述',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `nid` (`nid`) USING BTREE,
  UNIQUE KEY `table` (`table`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='会员模型表';


DROP TABLE IF EXISTS `fox_member_model_field`;
CREATE TABLE `fox_member_model_field` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `member_model_id` int(11) DEFAULT NULL COMMENT '会员模型id',
  `name` varchar(32) NOT NULL DEFAULT '' COMMENT '字段名称',
  `title` varchar(250) NOT NULL DEFAULT '' COMMENT '字段标题',
  `dtype` varchar(32) NOT NULL DEFAULT '' COMMENT '字段类型',
  `define` text NOT NULL COMMENT '字段定义',
  `maxlength` int(11) DEFAULT '0' COMMENT '最大长度，文本数据必须填写，大于255为text类型',
  `dfvalue` text COMMENT '默认值',
  `remark` varchar(256) DEFAULT '' COMMENT '提示说明',
  `is_system` tinyint(1) DEFAULT '0' COMMENT '字段分类，1=系统(不可修改)，0=自定义',
  `sort_order` int(11) DEFAULT '100' COMMENT '排序',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态1:显示；0：隐藏',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='会员模型自定义字段绑定表';


DROP TABLE IF EXISTS `fox_member_upgrade`;
CREATE TABLE `fox_member_upgrade` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT '' COMMENT '名称',
  `member_level_id` int(11) NOT NULL COMMENT '会员等级id',
  `price` decimal(10,0) DEFAULT NULL COMMENT '价格',
  `deadline` varchar(255) DEFAULT NULL COMMENT '会员期限',
  `description` varchar(255) DEFAULT NULL COMMENT '描述',
  `status` tinyint(4) DEFAULT '1' COMMENT '状态 默认1：启用；0：禁用',
  `sort` int(11) DEFAULT NULL COMMENT '排序',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `nid` (`name`) USING BTREE,
  UNIQUE KEY `table` (`price`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='升级套餐管理';


DROP TABLE IF EXISTS `fox_model_field`;
CREATE TABLE `fox_model_field` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `pid` int(11) DEFAULT '0' COMMENT '父id',
  `model` varchar(250) DEFAULT NULL COMMENT '模型',
  `name` varchar(32) DEFAULT '' COMMENT '字段名称',
  `title` varchar(250) DEFAULT '' COMMENT '字段标题',
  `dtype` varchar(32) DEFAULT '' COMMENT '字段类型',
  `define` text COMMENT '字段定义',
  `maxlength` int(11) DEFAULT '0' COMMENT '最大长度，文本数据必须填写，大于255为text类型',
  `dfvalue` text COMMENT '默认值',
  `remark` varchar(256) DEFAULT '' COMMENT '提示说明',
  `is_system` tinyint(1) DEFAULT '0' COMMENT '字段分类，1=系统(不可修改)，0=自定义',
  `sort_order` int(11) DEFAULT '100' COMMENT '排序',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态1:显示；0：隐藏',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='模型自定义字段绑定表';


DROP TABLE IF EXISTS `fox_model_record`;
CREATE TABLE `fox_model_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nid` varchar(50) NOT NULL DEFAULT '' COMMENT '识别id',
  `name` varchar(30) DEFAULT '' COMMENT '名称',
  `table` varchar(50) DEFAULT '' COMMENT '表名',
  `ctl_name` varchar(50) DEFAULT '' COMMENT '控制器名称（区分大小写）',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态(1=启用，0=屏蔽)',
  `is_system` tinyint(1) DEFAULT '0' COMMENT '字段分类，1=系统(不可修改)，0=自定义',
  `is_repeat_title` tinyint(1) DEFAULT '1' COMMENT '文档标题重复，1=允许，0=不允许',
  `is_delete` tinyint(1) DEFAULT '0' COMMENT '伪删除，1=是，0=否',
  `sort_order` smallint(6) DEFAULT '50' COMMENT '排序',
  `reference_model` tinyint(2) DEFAULT '0' COMMENT '参照模型；0:类似文章；1:类似单页',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `nid` (`nid`) USING BTREE,
  UNIQUE KEY `ctl_name` (`ctl_name`) USING BTREE,
  UNIQUE KEY `table` (`table`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='系统模型记录表';


DROP TABLE IF EXISTS `fox_plugin_mail_config`;
CREATE TABLE `fox_plugin_mail_config` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `smtp_url` varchar(255) DEFAULT NULL COMMENT 'SMTP地址',
  `smtp_port` int(10) DEFAULT NULL COMMENT 'SMTP端口',
  `send_account` varchar(255) DEFAULT NULL COMMENT '发送邮箱账号',
  `auth_code` varchar(255) DEFAULT NULL COMMENT '邮箱授权码',
  `test_account` varchar(255) DEFAULT NULL COMMENT '测试账号',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='邮件配置';


DROP TABLE IF EXISTS `fox_plugin_mail_template`;
CREATE TABLE `fox_plugin_mail_template` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '模板名称',
  `title` varchar(255) DEFAULT NULL COMMENT '邮件标题',
  `receiver` varchar(255) DEFAULT NULL COMMENT '接收人',
  `status` tinyint(1) DEFAULT '1' COMMENT '启用状态 0：否；1:是',
  `content` varchar(1000) DEFAULT NULL COMMENT '内容',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `type` tinyint(2) DEFAULT NULL COMMENT '模板类型 1:默认类型；2:表单模型',
  `is_default` tinyint(2) DEFAULT '0' COMMENT '是否默认 0:否；1：是',
  `preview_url` varchar(255) DEFAULT NULL COMMENT '预览地址',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='邮件模板';


DROP TABLE IF EXISTS `fox_product`;
CREATE TABLE `fox_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `column` varchar(255) DEFAULT NULL COMMENT '栏目',
  `column_id` int(11) DEFAULT NULL COMMENT '栏目id',
  `tags` varchar(255) DEFAULT NULL COMMENT '文档标签',
  `title` tinytext COMMENT '标题',
  `brief_title` varchar(255) DEFAULT NULL COMMENT '简略标题',
  `picset_ids` varchar(255) DEFAULT NULL COMMENT '图片集ids',
  `pic_ids` varchar(255) DEFAULT NULL COMMENT '详情ids',
  `breviary_pic_id` int(11) DEFAULT NULL COMMENT '文章缩略图id',
  `click` int(11) DEFAULT '0' COMMENT '浏览量',
  `article_field` varchar(20) DEFAULT NULL COMMENT '文章属性(t:头条;c:推荐;h:热门;b:加粗;s:幻灯)',
  `keywords` varchar(255) DEFAULT NULL COMMENT 'SEO关键词',
  `description` varchar(255) DEFAULT NULL COMMENT 'SEO描述',
  `status` tinyint(1) DEFAULT '1' COMMENT '参数编辑1启用；0：禁用',
  `article_source` varchar(255) DEFAULT NULL COMMENT '文章来源',
  `author` varchar(255) DEFAULT NULL COMMENT '作者',
  `content` text COMMENT '内容',
  `team_status` varchar(50) DEFAULT '' COMMENT '状态 down,del',
  `release_time` datetime DEFAULT NULL COMMENT '发布时间',
  `lang` varchar(50) DEFAULT NULL COMMENT '语言标识',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `price` varchar(255) DEFAULT '' COMMENT '产品价格',
  `buy_link` varchar(255) DEFAULT '' COMMENT '购买地址',
  `big_img` varchar(255) DEFAULT '' COMMENT '广告主图',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='图片集表';


DROP TABLE IF EXISTS `fox_product_attr`;
CREATE TABLE `fox_product_attr` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status` tinyint(4) DEFAULT '1' COMMENT '参数编辑 默认1：启用；0：关闭',
  `name` varchar(255) DEFAULT NULL COMMENT '属性名称',
  `sort` int(11) DEFAULT '1' COMMENT '排序',
  `lang` varchar(255) DEFAULT NULL COMMENT '语言',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='产品属性表';


DROP TABLE IF EXISTS `fox_product_attr_param`;
CREATE TABLE `fox_product_attr_param` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_attr_id` int(11) DEFAULT NULL COMMENT '产品属性id',
  `name` varchar(255) DEFAULT NULL COMMENT '参数名称',
  `type` varchar(255) DEFAULT NULL COMMENT '参数类型',
  `type_id` tinyint(4) DEFAULT NULL,
  `type_desc` varchar(255) DEFAULT NULL COMMENT '类型描述',
  `dfvalue` text COMMENT '默认值',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='产品属性参数表';


DROP TABLE IF EXISTS `fox_product_param`;
CREATE TABLE `fox_product_param` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(11) DEFAULT NULL COMMENT '产品id',
  `name` varchar(255) DEFAULT NULL COMMENT '参数名称',
  `type` varchar(255) DEFAULT NULL COMMENT '参数类型',
  `sel_value` varchar(255) DEFAULT '' COMMENT '下拉选项值',
  `type_id` tinyint(4) DEFAULT NULL COMMENT '规定参数类型id',
  `type_desc` varchar(255) DEFAULT NULL COMMENT '类型描述',
  `dfvalue` text COMMENT '默认值',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `attr_param_id` int(11) DEFAULT NULL COMMENT '产品参数id',
  `sort` int(11) DEFAULT '1' COMMENT '排序',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=165 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='产品参数表';


DROP TABLE IF EXISTS `fox_project_version`;
CREATE TABLE `fox_project_version` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `version` varchar(255) DEFAULT NULL COMMENT '版本',
  `only` varchar(255) DEFAULT NULL COMMENT '唯一标识',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='项目版本';


DROP TABLE IF EXISTS `fox_safe`;
CREATE TABLE `fox_safe` (
  `id` int(20) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `dict_label` varchar(100) DEFAULT '' COMMENT '标签',
  `dict_value` varchar(100) DEFAULT '' COMMENT '键值',
  `status` char(1) DEFAULT '0' COMMENT '状态',
  `description` varchar(255) DEFAULT NULL COMMENT '描述',
  `type` varchar(255) DEFAULT NULL COMMENT '类型',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='安全管理';


DROP TABLE IF EXISTS `fox_search`;
CREATE TABLE `fox_search` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `is_used` tinyint(255) DEFAULT '0' COMMENT '是否常用0：否；1：是',
  `search_group_id` int(10) DEFAULT NULL COMMENT '搜索组id',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `sort` int(10) DEFAULT '1' COMMENT '排序',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='搜索';


DROP TABLE IF EXISTS `fox_search_group`;
CREATE TABLE `fox_search_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sort` int(11) DEFAULT '1' COMMENT '排序',
  `name` varchar(255) NOT NULL COMMENT '名称',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态1启用；0：禁用',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='搜索组';


DROP TABLE IF EXISTS `fox_single`;
CREATE TABLE `fox_single` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `column_id` int(11) DEFAULT NULL COMMENT '栏目id',
  `column` varchar(255) DEFAULT NULL COMMENT '栏目名称',
  `team_status` varchar(50) DEFAULT '' COMMENT '状态 down,del',
  `content` text COMMENT '内容',
  `lang` varchar(50) DEFAULT NULL COMMENT '语言标识',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='简单模型';


DROP TABLE IF EXISTS `fox_slide`;
CREATE TABLE `fox_slide` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `advertising_space_id` int(11) DEFAULT NULL COMMENT '广告位id',
  `sort` int(11) DEFAULT NULL COMMENT '排序',
  `title` varchar(255) DEFAULT NULL COMMENT '标题',
  `img_url` varchar(255) DEFAULT NULL COMMENT '图片',
  `link` varchar(255) DEFAULT NULL COMMENT '幻灯片链接',
  `type` tinyint(4) DEFAULT '0' COMMENT '幻灯片类型 默认0',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态1显示；0：隐藏',
  `sid` varchar(255) DEFAULT NULL COMMENT '标识id',
  `lang` varchar(50) DEFAULT NULL COMMENT '语言',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `adv_info` text COMMENT '广告信息',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='广告位内容';


DROP TABLE IF EXISTS `fox_software_version`;
CREATE TABLE `fox_software_version` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '名称',
  `content` varchar(255) DEFAULT NULL COMMENT '内容',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='软件版本';


DROP TABLE IF EXISTS `fox_tag`;
CREATE TABLE `fox_tag` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '' COMMENT '名称',
  `is_used` tinyint(255) DEFAULT '0' COMMENT '是否常用0：否；1：是',
  `click` int(11) DEFAULT '0' COMMENT '点击数',
  `tag_group_id` int(10) DEFAULT NULL COMMENT '标签组id',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `name` (`name`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='tag标签';


DROP TABLE IF EXISTS `fox_tag_group`;
CREATE TABLE `fox_tag_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `sort` int(11) DEFAULT '1' COMMENT '排序',
  `name` varchar(255) DEFAULT NULL COMMENT '名称',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态1启用；0：禁用',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='标签组';


DROP TABLE IF EXISTS `fox_template`;
CREATE TABLE `fox_template` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '编号',
  `template` varchar(255) NOT NULL DEFAULT '' COMMENT '模板目录',
  `name` varchar(255) DEFAULT NULL COMMENT '模板名称',
  `code` varchar(255) DEFAULT NULL COMMENT '模板编号',
  `title` varchar(255) DEFAULT NULL COMMENT '标题',
  `author` varchar(255) DEFAULT NULL COMMENT '模板作者',
  `release_time` datetime DEFAULT NULL COMMENT '发布时间',
  `directory` varchar(255) DEFAULT NULL COMMENT '模板目录',
  `type` tinyint(4) DEFAULT '3' COMMENT '模板类型 1、只有电脑端； 2、只有移动端； 3、有电脑端和移动端； 4、自适应',
  `describe` text COMMENT '模板简述',
  `run_status` tinyint(3) unsigned DEFAULT '2' COMMENT '运行模式 2:未安装； 0:未运行；1：运行',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `html` varchar(255) DEFAULT 'html' COMMENT 'Html目录',
  `thumb_id` int(11) DEFAULT NULL COMMENT '缩略图id',
  `detail_id` int(11) DEFAULT NULL COMMENT '详细图id',
  `upload_template_id` int(11) DEFAULT NULL COMMENT '上传模板文件id',
  `status` tinyint(1) DEFAULT '1' COMMENT '模板1启用；0：禁用',
  `template_source` tinyint(2) DEFAULT '0' COMMENT '模板来源0：黔狐官网；1：其它模板',
  `sort` int(11) DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='前端模板表';


DROP TABLE IF EXISTS `fox_upload_files`;
CREATE TABLE `fox_upload_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `storage` tinyint(1) DEFAULT '0' COMMENT '存储位置 0本地 1阿里云 2七牛云 -1：表示全部',
  `app` smallint(6) DEFAULT '0' COMMENT '来自应用 0前台 1后台',
  `user_id` int(11) DEFAULT '0' COMMENT '根据app类型判断用户类型',
  `file_name` varchar(100) DEFAULT '' COMMENT '操作操作用户信息',
  `file_size` int(11) DEFAULT '0' COMMENT '文件大小(单位字节)',
  `extension` varchar(10) DEFAULT '' COMMENT '文件后缀',
  `url` varchar(500) DEFAULT '' COMMENT '图片路径',
  `create_time` datetime DEFAULT NULL,
  `file_type` varchar(255) DEFAULT NULL COMMENT '文件类型',
  `file_group_type` varchar(255) DEFAULT '0' COMMENT '默认0：默认分组',
  `size` varchar(255) DEFAULT '' COMMENT '图片尺寸',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=469 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='上传文件表';


DROP TABLE IF EXISTS `fox_variate_field`;
CREATE TABLE `fox_variate_field` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID',
  `group` varchar(255) DEFAULT NULL COMMENT '所属于组 （basic：基本设置；contact：联系方式）',
  `name` varchar(32) DEFAULT '' COMMENT '字段名称',
  `title` varchar(250) DEFAULT '' COMMENT '字段标题',
  `dtype` varchar(32) DEFAULT '' COMMENT '字段类型',
  `define` text COMMENT '字段定义',
  `maxlength` int(11) DEFAULT '0' COMMENT '最大长度，文本数据必须填写，大于255为text类型',
  `dfvalue` text COMMENT '默认值',
  `remark` varchar(256) DEFAULT '' COMMENT '提示说明',
  `is_system` tinyint(1) DEFAULT '0' COMMENT '字段分类，1=系统(不可修改)，0=自定义',
  `sort_order` int(11) DEFAULT '100' COMMENT '排序',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态1:启用；0：禁用',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `call_tag` varchar(255) DEFAULT NULL COMMENT '调用标签',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='站点自定义变量字段表';


DROP TABLE IF EXISTS `fox_version_record`;
CREATE TABLE `fox_version_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `copy_path` varchar(255) DEFAULT NULL COMMENT '副本路径',
  `remote_version_id` int(11) DEFAULT NULL COMMENT '远程版本id',
  `version` varchar(255) DEFAULT NULL COMMENT '版本',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='版本记录';


DROP TABLE IF EXISTS `fox_video`;
CREATE TABLE `fox_video` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `column_id` int(11) DEFAULT NULL COMMENT '栏目id',
  `column` varchar(255) DEFAULT NULL COMMENT '栏目名称',
  `tags` varchar(255) DEFAULT NULL COMMENT '文档标签',
  `title` tinytext COMMENT '标题',
  `brief_title` varchar(255) DEFAULT NULL COMMENT '简略标题',
  `pic_ids` varchar(255) DEFAULT NULL COMMENT '文章ids',
  `breviary_pic_id` int(11) DEFAULT NULL COMMENT '文章缩略图id',
  `click` int(11) DEFAULT '0' COMMENT '浏览量',
  `article_field` varchar(20) DEFAULT NULL COMMENT '文章属性(t:头条;c:推荐;h:热门;b:加粗;s:幻灯)',
  `keywords` varchar(255) DEFAULT NULL COMMENT 'SEO关键词',
  `description` varchar(255) DEFAULT NULL COMMENT 'SEO描述',
  `article_source` varchar(255) DEFAULT NULL COMMENT '文章来源',
  `author` varchar(255) DEFAULT NULL COMMENT '作者',
  `content` text COMMENT '内容',
  `team_status` varchar(50) DEFAULT '' COMMENT '状态 down,del',
  `release_time` datetime DEFAULT NULL COMMENT '发布时间',
  `lang` varchar(50) DEFAULT NULL COMMENT '语言标识',
  `create_time` datetime DEFAULT NULL COMMENT '新增时间',
  `update_time` datetime DEFAULT NULL COMMENT '更新时间',
  `videos` text COMMENT '视频集|0',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='视频模型';


DROP TABLE IF EXISTS `fox_video_files`;
CREATE TABLE `fox_video_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `storage` tinyint(1) DEFAULT '0' COMMENT '存储位置 0本地 1阿里云 2七牛云',
  `app` smallint(6) DEFAULT '0' COMMENT '来自应用 0前台 1后台',
  `user_id` int(11) DEFAULT '0' COMMENT '根据app类型判断用户类型',
  `file_name` varchar(100) DEFAULT '',
  `file_size` int(11) DEFAULT '0',
  `extension` varchar(10) DEFAULT '' COMMENT '文件后缀',
  `url` varchar(500) DEFAULT '' COMMENT '文件路径',
  `file_type` varchar(255) DEFAULT NULL COMMENT '文件类型',
  `pic` varchar(255) DEFAULT NULL COMMENT '图片',
  `create_time` datetime DEFAULT NULL,
  `duration` varchar(255) DEFAULT NULL COMMENT '时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='视频文件';


DROP TABLE IF EXISTS `fox_watermark`;
CREATE TABLE `fox_watermark` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '水印功能状态 1:开启；0:关闭',
  `type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '水印类型 1:文字；2:图片',
  `width` int(11) DEFAULT '0' COMMENT '宽度 单位像素(px)',
  `height` int(11) DEFAULT '0' COMMENT '高度 单位像素(px)',
  `water_font` varchar(255) DEFAULT '' COMMENT '水印文字',
  `font_size` int(11) DEFAULT '16' COMMENT '文字大小',
  `font_color` varchar(255) DEFAULT '#ffffff' COMMENT '文字颜色',
  `shadow_color` varchar(255) DEFAULT NULL COMMENT '文字阴影颜色',
  `opacity` tinyint(3) DEFAULT '100' COMMENT '水印透明度',
  `position` tinyint(2) DEFAULT '1' COMMENT '水印位置',
  `image` varchar(255) DEFAULT '' COMMENT '水印图片',
  `jpeg_quality` tinyint(2) DEFAULT '100' COMMENT 'JPEG水印质量',
  `create_time` datetime DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='水印配置';

