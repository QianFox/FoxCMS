<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   15:49
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   15:49
 */

namespace app\admin\util;

use think\facade\Db;

// 备份数据
class Basckup
{
    // 生成表结构与数据
    public function backupTable($tableName, $isData = true, $start = 0)
    {
        $result = Db::query("SHOW CREATE TABLE `{$tableName}`");
        $sql  = "\n";
        $sql .= "-- --------------开始-----------------\n";
        $sql .= "-- Table structure for `{$tableName}`\n";
        $sql .= "-- -------------------------------\n";
        $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
        $sql .= trim($result[0]['Create Table']) . ";\n\n";

        if ($isData) { //保存数据
            //数据总数
            $result = Db::query("SELECT COUNT(*) AS count FROM `{$tableName}`");
            $count  = $result['0']['count'];
            if ($count > 0) { //有数据才整
                $sql .= "-- ---------------------------------\n";
                $sql .= "-- --------------数据开始-------------\n";
                $sql .= "-- ---------------------------------\n";
                //备份数据记录
                $result = Db::query("SELECT * FROM `{$tableName}` LIMIT {$start}, {$count}");
                foreach ($result as $row) {
                    $row = array_map('addslashes', $row); //字符串加入斜线
                    //                    unset($row['id']);
                    $fields = array_keys($row);
                    $field = implode("`, `", $fields);
                    $value = str_replace(array("\r", "\n"), array('\r', '\n'), implode("', '", $row));
                    $sql .= "INSERT INTO `{$tableName}`(`" . $field . "`) VALUES ('" . $value . "');\n";
                }
                $sql .= "-- ---------------------------------\n";
                $sql .= "-- --------------数据结束-------------\n";
                $sql .= "-- ---------------------------------\n";
            }
        }
        $sql .= "-- ----------------结束---------------\n";
        return $sql;
    }

    // 生成表数据
    public function backupTableData($tableName, $where = "1=1", $start = 0)
    {
        $sql  = "\n";
        //数据总数
        $result = Db::query("SELECT COUNT(*) AS count FROM `{$tableName}` where {$where}");
        $count  = $result['0']['count'];
        if ($count > 0) { //有数据才整
            $sql .= "-- ---------------------------------\n";
            $sql .= "-- --------------数据开始-------------\n";
            $sql .= "-- ---------------------------------\n";
            //备份数据记录
            $result = Db::query("SELECT * FROM `{$tableName}` where {$where} LIMIT {$start}, {$count}");
            foreach ($result as $row) {
                $row = array_map('addslashes', $row); //字符串加入斜线
                //                unset($row['id']);
                $fields = array_keys($row);
                $field = implode("`, `", $fields);
                $value = str_replace(array("\r", "\n"), array('\r', '\n'), implode("', '", $row));
                $sql .= "INSERT INTO `{$tableName}`(`" . $field . "`) VALUES ('" . $value . "');\n";
            }
            $sql .= "-- ---------------------------------\n";
            $sql .= "-- --------------数据结束-------------\n";
            $sql .= "-- ---------------------------------\n";
        }
        return $sql;
    }

    // 生成表数据
    public function getBackupTableData($tableName, $where = "1=1", $start = 0)
    {
        $sql  = "\n";
        //数据总数
        $result = Db::query("SELECT COUNT(*) AS count FROM `{$tableName}` where {$where}");
        $count  = $result['0']['count'];
        if ($count > 0) { //有数据才整
            $sql .= "-- ---------------------------------\n";
            $sql .= "-- --------------数据开始-------------\n";
            $sql .= "-- ---------------------------------\n";
            //备份数据记录
            $result = Db::query("SELECT * FROM `{$tableName}` where {$where} LIMIT {$start}, {$count}");
            $fieldArr = \think\facade\Db::table("{$tableName}")->getFields(); //表属性
            foreach ($result as $row) {
                $row = array_map('addslashes', $row); //字符串加入斜线
                //                unset($row['id']);
                $fields = array_keys($row);

                $fieldStr = implode("`, `", $fields);
                $valueArr = [];
                foreach ($fields as $field) {
                    $fieldVal = $row[$field];
                    $fieldVal = $this->getFieldVal($fieldArr, $field, $fieldVal);
                    array_push($valueArr, $fieldVal);
                }
                $val = implode(",", $valueArr);
                $value = str_replace(array("\r", "\n"), array('\r', '\n'), $val);
                $sql .= "INSERT INTO `{$tableName}`(`" . $fieldStr . "`) VALUES (" . $value . ");\n";
            }
            $sql .= "-- ---------------------------------\n";
            $sql .= "-- --------------数据结束-------------\n";
            $sql .= "-- ---------------------------------\n";
        }
        return $sql;
    }

    // 获取表数据及结构
    public function getbackupTable($tableName, $isData = true, $start = 0)
    {
        $result = Db::query("SHOW CREATE TABLE `{$tableName}`");
        $structSql  = "\n";
        //        $structSql .= "-- Table structure for `{$tableName}`\n";
        $structSql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
        $structSql .= trim($result[0]['Create Table']) . ";\n\n";
        if (!$isData) {
            $structSql = preg_replace("/ENGINE=InnoDB AUTO_INCREMENT=(\d+)/i", "ENGINE=InnoDB AUTO_INCREMENT=1", $structSql);
            $structSql = preg_replace("/ENGINE=MyISAM AUTO_INCREMENT=(\d+)/i", "ENGINE=InnoDB AUTO_INCREMENT=1", $structSql);
        }
        $rdata = [$structSql];
        if ($isData) { //保存数据
            $dataql = "\n";
            //数据总数
            $result = Db::query("SELECT COUNT(*) AS count FROM `{$tableName}`");
            $count  = $result['0']['count'];
            if ($count > 0) { //有数据才整
                //备份数据记录
                $result = Db::query("SELECT * FROM `{$tableName}` LIMIT {$start}, {$count}");

                $fieldArr = \think\facade\Db::table("{$tableName}")->getFields(); //表属性

                foreach ($result as $row) {
                    $row = array_map('addslashes', $row); //字符串加入斜线
                    //                    unset($row['id']);
                    $fields = array_keys($row);
                    $fieldStr = implode("`, `", $fields);
                    $valueArr = [];
                    foreach ($fields as $field) {
                        $fieldVal = $row[$field];
                        $fieldVal = $this->getFieldVal($fieldArr, $field, $fieldVal);
                        array_push($valueArr, $fieldVal);
                    }

                    $val = implode(",", $valueArr);
                    $value = str_replace(array("\r", "\n"), array('\r', '\n'), $val);
                    $dataql .= "INSERT INTO `{$tableName}`(`" . $fieldStr . "`) VALUES (" . $value . ");\n";
                }
            }
            array_push($rdata, $dataql);
        }
        return $rdata;
    }

    // 获取属性值
    private function getFieldVal($fields, $field, $value)
    {
        $fieldArr = $fields[$field];
        $type = $fieldArr["type"];
        //        $notnull = $fieldArr["notnull"];
        if (str_starts_with($type, "int") || str_starts_with($type, "tinyint")) { //整形
            if ($value != null) {
                return $value;
            } else {
                return "null";
            }
        } elseif (str_starts_with($type, "varchar")) { //字符串
            if ($value != '0' && empty($value)) {
                return "''";
            } else {
                return "'" . $value . "'";
            }
        } elseif (str_starts_with($type, "datetime")) { //时间
            if (empty($value) || $value == "0000-00-00 00:00:00") {
                return "null";
            } else {
                return "'" . $value . "'";
            }
        } else {
            if ($value != null && $value != "") {
                return "'" . $value . "'";
            } else {
                return "null";
            }
        }
    }
}