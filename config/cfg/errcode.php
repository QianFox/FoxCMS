<?php

/**
 * 自定义错误编号翻译
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/11/16   10:32
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/11/16   10:32
 */

return [
    "SQLSTATE[HY000] [2002]" => "<h2>连接数据库失败！</h2><ul><li>请确定数据库连接配置信息是否正确</li><li>如果你需要重新安装，请先从 install 安装目录中删除 install.lock</li></ul>",
    "SQLSTATE[HY000] [1045]" => "<h2>连接数据库失败！</h2><ul><li>请确定数据库连接配置信息是否正确</li><li>如果你需要重新安装，请先从 install 安装目录中删除 install.lock</li></ul>",
    "Class '\\think\db\connector" => "<h2>连接数据库失败！</h2><ul><li>请确定数据库连接配置信息是否正确</li><li>如果你需要重新安装，请先从 install 安装目录中删除 install.lock</li></ul>",
    "SQLSTATE[42S02]: Base table or view not found" => "<h2>表或视图文件未找到</h2><ul><li>请确定数据库连接配置信息是否正确</li>",
    "SQLSTATE[HY000] [1049]" => "<h2>连接数据库失败！</h2><ul><li>请确定数据库连接配置信息是否正确</li><li>如果你需要重新安装，请先从 install 安装目录中删除 install.lock</li></ul>",
    "The server requested authentication" => "<h2>没找到客户端，请先检查配置</h2>",
    "Unknown character set" => "<h2>未知字符编码，请检查连接配置信息是否正确</h2><ul><li>请确定数据库连接配置信息是否正确</li>",
];
