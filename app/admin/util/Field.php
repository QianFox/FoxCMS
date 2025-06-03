<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   15:52
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   15:52
 */

namespace app\admin\util;

class Field
{

    /**
     * 获得字段创建信息
     * @param     string  $dtype  字段类型
     * @param     string  $fieldname  字段名称
     * @param     string  $dfvalue  默认值
     * @param     string  $fieldtitle  字段标题
     */
    function GetFieldMake($dtype, $fieldname, $dfvalue, $fieldtitle, $isNull = 0)
    {
        $nullStr = ($isNull == 1) ? "NOT NULL" : "";

        $fields = array();
        if ("int" == $dtype) {
            empty($dfvalue) && $dfvalue = 0;
            $default_sql = '';
            if (preg_match("#[0-9]+#", $dfvalue)) {
                $default_sql = "DEFAULT '$dfvalue'";
            }
            $maxlen = 0;
            $fields[0] = " `$fieldname` int $default_sql $nullStr COMMENT '$fieldtitle';";
            $fields[1] = "int";
            $fields[2] = $maxlen;
        } else if ("datetime" == $dtype) {
            $default_sql = "DEFAULT NULL";
            if (!empty($dfvalue)) {
                $default_sql = "DEFAULT '$dfvalue'";
            }
            $maxlen = 0;
            $fields[0] = " `$fieldname` datetime $default_sql $nullStr COMMENT '$fieldtitle';";
            $fields[1] = "datetime";
            $fields[2] = $maxlen;
        } else if ("date" == $dtype) {
            $default_sql = "DEFAULT NULL";
            if (!empty($dfvalue)) {
                $default_sql = "DEFAULT '$dfvalue'";
            }
            $maxlen = 0;
            $fields[0] = " `$fieldname` datetime $default_sql $nullStr COMMENT '$fieldtitle';";
            $fields[1] = "datetime";
            $fields[2] = $maxlen;
        } else if ("switch" == $dtype) {
            if (empty($dfvalue) || preg_match("#[^0-9]+#", $dfvalue)) {
                $dfvalue = 1;
            }
            $maxlen = 1;
            $fields[0] = " `$fieldname` tinyint($maxlen) DEFAULT '$dfvalue' $nullStr COMMENT '$fieldtitle';";
            $fields[1] = "tinyint($maxlen)";
            $fields[2] = $maxlen;
        } else if ("float" == $dtype) {
            empty($dfvalue) && $dfvalue = 0;
            $default_sql = '';
            if (preg_match("#[0-9\.]+#", $dfvalue)) {
                $default_sql = "DEFAULT '$dfvalue'";
            }
            $maxlen = 9;
            $fields[0] = " `$fieldname` float($maxlen,2) $default_sql $nullStr COMMENT '$fieldtitle';";
            $fields[1] = "float($maxlen,2)";
            $fields[2] = $maxlen;
        } else if ("decimal" == $dtype) {
            empty($dfvalue) && $dfvalue = 0;
            $default_sql = '';
            if (preg_match("#[0-9\.]+#", $dfvalue)) {
                $default_sql = "DEFAULT '$dfvalue'";
            }
            $maxlen = 10;
            $fields[0] = " `$fieldname` decimal($maxlen,2) $default_sql $nullStr COMMENT '$fieldtitle';";
            $fields[1] = "decimal($maxlen,2)";
            $fields[2] = $maxlen;
        } else if ("img" == $dtype) {
            if (empty($dfvalue)) {
                $dfvalue = '';
            }
            $maxlen = 255;
            $fields[0] = " `$fieldname` varchar($maxlen) DEFAULT '$dfvalue' $nullStr COMMENT '$fieldtitle';";
            $fields[1] = "varchar($maxlen)";
            $fields[2] = $maxlen;
        } else if ("pic" == $dtype) {
            $maxlen = 255;
            $fields[0] = " `$fieldname` varchar($maxlen) DEFAULT '$dfvalue' $nullStr COMMENT '$fieldtitle';";
            $fields[1] = "varchar($maxlen)";
            $fields[2] = $maxlen;
        } else if ("pics" == $dtype) {
            $maxlen = 0;
            $fields[0] = " `$fieldname` text COMMENT '$fieldtitle';";
            $fields[1] = "text";
            $fields[2] = $maxlen;
        } else if ("datepicker" == $dtype) {
            if (empty($dfvalue)) {
                $dfvalue = '';
            }
            $maxlen = 255;
            $fields[0] = " `$fieldname` varchar($maxlen)  DEFAULT '$dfvalue' $nullStr COMMENT '$fieldtitle';";
            $fields[1] = "varchar($maxlen)";
            $fields[2] = $maxlen;
        } else if ("timepicker" == $dtype) {
            if (empty($dfvalue)) {
                $dfvalue = '';
            }
            $maxlen = 255;
            $fields[0] = " `$fieldname` varchar($maxlen)  DEFAULT '$dfvalue' $nullStr COMMENT '$fieldtitle';";
            $fields[1] = "varchar($maxlen)";
            $fields[2] = $maxlen;
        } else if ("imgs" == $dtype) {
            $maxlen = 0;
            $fields[0] = " `$fieldname` text $nullStr COMMENT '$fieldtitle|{$maxlen}';";
            $fields[1] = "text";
            $fields[2] = $maxlen;
        } else if ("media" == $dtype) {
            $maxlen = 0;
            $fields[0] = " `$fieldname` text $nullStr COMMENT '$fieldtitle|{$maxlen}';";
            $fields[1] = "text";
            $fields[2] = $maxlen;
        } else if ("files" == $dtype) {
            if (empty($dfvalue)) {
                $dfvalue = '';
            }
            $maxlen = 10002;
            $fields[0] = " `$fieldname` text $nullStr COMMENT '$fieldtitle|{$maxlen}';";
            $fields[1] = "text";
            $fields[2] = $maxlen;
        } else if ("multitext" == $dtype) {
            $maxlen = 0;
            $fields[0] = " `$fieldname` text $nullStr COMMENT '$fieldtitle';";
            $fields[1] = "text";
            $fields[2] = $maxlen;
        } else if ("htmltext" == $dtype) {
            $maxlen = 0;
            $fields[0] = " `$fieldname` longtext $nullStr COMMENT '$fieldtitle';";
            $fields[1] = "longtext";
            $fields[2] = $maxlen;
        } else if ("checkbox" == $dtype) {
            $maxlen = 0;
            $dfvalueArr = explode(',', $dfvalue);
            $default_value = '';
            // $default_value = !empty($dfvalueArr[0]) ? $dfvalueArr[0] : '';
            $dfvalue = str_replace(',', "','", $dfvalue);
            $dfvalue = "'" . $dfvalue . "'";
            $fields[0] = " `$fieldname` SET($dfvalue) NULL DEFAULT '{$default_value}' $nullStr COMMENT '$fieldtitle';";
            $fields[1] = "SET($dfvalue)";
            $fields[2] = $maxlen;
        } else if ("select" == $dtype || "radio" == $dtype) {
            $maxlen = 0;
            $dfvalueArr = explode(',', $dfvalue);
            $default_value = !empty($dfvalueArr[0]) ? $dfvalueArr[0] : '';
            $dfvalue = str_replace(',', "','", $dfvalue);
            $dfvalue = "'" . $dfvalue . "'";
            $fields[0] = " `$fieldname` enum($dfvalue) NULL DEFAULT '{$default_value}' $nullStr COMMENT '$fieldtitle';";
            $fields[1] = "enum($dfvalue)";
            $fields[2] = $maxlen;
        } else if ("color" == $dtype) {
            if (empty($dfvalue)) {
                $dfvalue = '';
            }
            $maxlen = 255;
            $fields[0] = " `$fieldname` varchar($maxlen) DEFAULT '$dfvalue'  $nullStr COMMENT '$fieldtitle';";
            $fields[1] = "varchar($maxlen)";
            $fields[2] = $maxlen;
        } else {
            if (empty($dfvalue)) {
                $dfvalue = '';
            }
            $maxlen = 255;
            $fields[0] = " `$fieldname` varchar($maxlen) DEFAULT '$dfvalue' $nullStr COMMENT '$fieldtitle';";
            $fields[1] = "varchar($maxlen)";
            $fields[2] = $maxlen;
        }

        return $fields;
    }

    // 转换控制
    function convertField($dtype)
    {
        $convertField = [
            'text' => ['radio', 'switch', 'select', 'int', 'datetime', 'decimal', 'float'],
            'checkbox' => ['select', 'datetime'],
            'multitext' => ['checkbox', 'radio', 'int', 'datetime', 'decimal', 'float'],
            'radio' => ['datetime'],
            'select' => ['datetime'],
            'pic' => ['checkbox', 'radio', 'int', 'datetime', 'decimal', 'float'],
            'int' => ['datetime'],
            'datetime' => ['checkbox', 'radio', 'switch', 'select', 'int', 'decimal', 'float'],
            'htmltext' => ['checkbox', 'radio', 'switch', 'select', 'int'],
            'decimal' => ['checkbox', 'radio', 'switch', 'select', 'datetime', 'icon'],
            'float' => ['checkbox', 'radio', 'switch', 'select', 'datetime', 'icon'],
            'icon' => ['radio', 'switch', 'select', 'int', 'datetime', 'decimal', 'float']
        ];
        return $convertField[$dtype];
    }
}