<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   15:54
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   15:54
 */

namespace app\admin\util;

use think\facade\Db;

class TableUtil
{
    // 创建表
    public static function createTable($table, $columns = array(), $remark = "")
    {
        $table = str_replace("`", "", $table);
        if (sizeof($columns) <= 0) {
            return false;
        }

        $columnStr = implode(",", $columns) . ",";
        // 创建自定义模型附加表
        $tableSql = <<<EOF
                CREATE TABLE `{$table}` (
                  `id` int(10) NOT NULL AUTO_INCREMENT,
                    $columnStr
                   PRIMARY KEY (`id`)
                ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='$remark';
EOF;
        $sqlFormat  = (new ModelMg())->sql_split($tableSql, "fox_");
        // 执行SQL语句
        try {
            // 执行SQL语句
            $counts = count($sqlFormat);
            for ($i = 0; $i < $counts; $i++) {
                $sql = trim($sqlFormat[$i]);
                if (stristr($sql, 'CREATE TABLE')) {
                    Db::execute($sql);
                } else {
                    if (trim($sql) == '')
                        continue;
                    Db::execute($sql);
                }
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    // 删除表
    public static function del_table($table)
    {
        $table = str_replace("`", "", $table);
        try {
            $sql = "DROP TABLE `{$table}`";
            Db::execute($sql);
            return 1;
        } catch (\Exception $e) {
            return  0;
        }
    }

    // 判断表是否存在
    public static function check_table($table)
    {
        $table = str_replace("`", "", $table);
        $res = Db::query('SHOW TABLES LIKE ' . "'" . $table . "'");
        if ($res) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * 判断字段是否存在
     * @param $table 表名
     * @param $column 字段
     */
    public static function check_column($table, $column)
    {
        $table = str_replace("`", "", $table);
        $res = Db::query('select count(*) from information_schema.columns where table_name = ' . "'" . $table . "' " . 'and column_name =' . "'" . $column . "'");
        if ($res[0]['count(*)'] != 0) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * 添加表字段
     * @param $table 表名
     * @param $column 字段
     * @param $type 类型如 varchar(255)
     * @param $condition 条件如 DEFAULT NULL或 NOT NULL
     * @param $after 某个字段后
     */
    public static function add_column($table, $column, $type, $condition, $after)
    {
        $table = str_replace("`", "", $table);
        try {
            Db::execute('alter table' . " `" . $table . "` " . 'add' . " `" . $column . "` " . $type . " " . $condition . " " . 'after' . " `" . $after . "`");
            return 1;
        } catch (\Exception $e) {
            return  0;
        }
    }

    /**
     * 删除表字段
     * @param $table 表名
     * @param $column 字段
     */
    public static function del_column($table, $column)
    {
        $table = str_replace("`", "", $table);
        try {
            Db::execute('alter table ' . "`" . $table . "`" . ' drop column' . "`" . $column . "`");
            return 1;
        } catch (\Exception $e) {
            return  0;
        }
    }

    /**
     * 修改表字段
     * @param $table 表名
     * @param $old_column 要修改字段
     * @param $column 新字段
     * @param $type 字段类型如 varchar(250)
     */
    public static function update_column($table, $old_column, $column, $type)
    {
        $table = str_replace("`", "", $table);
        //字段名和类型同时修改才会返回1不然返回0
        try {
            Db::execute('alter table' . " `" . $table . "` " . 'change' . " `" . $old_column . "` " . "`" . $column . "`" . $type);
            return 1;
        } catch (\Exception $e) {
            return  0;
        }
    }

    // 拆分sql内容
    public static function sql_split($sql, $tablepre = "fox_")
    {
        if ($tablepre != "fox_")
            $sql = str_replace("`fox_", '`' . $tablepre, $sql);

        $sql = preg_replace("/TYPE=(InnoDB|MyISAM|MEMORY)( DEFAULT CHARSET=[^; ]+)?/", "ENGINE=\\1 DEFAULT CHARSET=utf8", $sql);

        $sql = str_replace("\r", "\n", $sql);
        $ret = array();
        $num = 0;
        $queriesarray = explode(";\n", trim($sql));
        unset($sql);
        foreach ($queriesarray as $query) {
            $query = remove_str_bom($query);
            $ret[$num] = '';
            $queries = explode("\n", trim($query));
            $queries = array_filter($queries);
            foreach ($queries as $query) {
                $str1 = substr($query, 0, 1);
                if ($str1 != '#' && $str1 != '-')
                    $ret[$num] .= $query;
            }
            $num++;
        }
        return $ret;
    }
}