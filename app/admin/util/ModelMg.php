<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   15:53
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   15:53
 */

namespace app\admin\util;

use think\facade\Db;

// 模型工具
class ModelMg
{
    // 解析sql语句
    public function sql_split($sql, $tablepre)
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

    /**
     * 创建模型及模型表
     * @param $nid 模型标识
     * @param $cm 类名称
     * @param $templateHtml 模型文件
     * @param $name 表名字
     * @param string $reference_model 参照模型 0：文章模型; 1:单页模型
     * @return bool
     */
    public function createModel($nid, $cm, $templateHtml, $name, $reference_model = "0")
    {
        $modelPath = app()->getRootPath() . 'custom';
        $filelist = dirFile($modelPath);

        $referenceModelText = "article";
        if ($reference_model == 1) {
            $referenceModelText = "single";
        }
        foreach ($filelist as $key => $file) {

            $dst = $file; //目标文件
            //跳过模型
            if ((strpos($dst, 'admin') !== false)) {
                if (((strpos($dst, 'view') !== false) && !(strpos($dst, $referenceModelText) !== false))) {
                    continue;
                }
                if ((strpos($dst, 'controller') !== false) && !(strpos($dst, ucfirst($referenceModelText)) !== false)) {
                    continue;
                }
            }
            if (strpos($dst, 'common') !== false) { //模型
                if (!(strpos($dst, ucfirst($referenceModelText)) !== false)) {
                    continue;
                }
            }
            if (strpos($dst, 'home') !== false) { //前端控制
                if (!(strpos($dst, ucfirst($referenceModelText)) !== false)) {
                    continue;
                }
            }

            $src = $modelPath . DIRECTORY_SEPARATOR . $file; //源文件
            $dst = str_replace('custom', $nid, $dst);
            $dst = str_replace('Custom', $cm, $dst);
            if (strpos($dst, 'admin') !== false) { //后台控制
                if (strpos($dst, 'controller') !== false) { //控制文件
                    $dst = str_replace(ucfirst($referenceModelText), $cm, $dst);
                    $dst = app()->getRootPath() . "app" . DIRECTORY_SEPARATOR . $dst;
                    $fileContent = @file_get_contents($src);
                    $fileContent = str_replace('Custom', $cm, $fileContent);
                    $fileContent = str_replace('custom', $nid, $fileContent);

                    @file_put_contents($dst, $fileContent);
                } elseif (strpos($dst, 'view') !== false) { //显示文件
                    $dst = str_replace($referenceModelText, $nid, $dst);
                    $dst = app()->getRootPath() . "app" . DIRECTORY_SEPARATOR . $dst;
                    $dir = substr($dst, 0, strrpos($dst, DIRECTORY_SEPARATOR));
                    if (tp_mkdir($dir)) {
                        $fileContent = @file_get_contents($src);
                        $fileContent = str_replace('Custom', $cm, $fileContent);
                        $fileContent = str_replace('custom', $nid, $fileContent);
                        @file_put_contents($dst, $fileContent);
                    }
                }
            } elseif (strpos($dst, 'common') !== false) { //模型
                $dst = str_replace(ucfirst($referenceModelText), $cm, $dst);
                $dst = app()->getRootPath() . "app" . DIRECTORY_SEPARATOR . $dst;
                $fileContent = @file_get_contents($src);
                $fileContent = str_replace('Custom', $cm, $fileContent);
                @file_put_contents($dst, $fileContent);
            } elseif (strpos($dst, 'home') !== false) { //前端控制
                $dst = str_replace(ucfirst($referenceModelText), $cm, $dst);
                $dst = app()->getRootPath() . "app" . DIRECTORY_SEPARATOR . $dst;
                $fileContent = @file_get_contents($src);
                $fileContent = str_replace('Custom', $cm, $fileContent);
                $fileContent = str_replace('custom', $nid, $fileContent);
                @file_put_contents($dst, $fileContent);
            } elseif (strpos($dst, 'template') !== false) { //前端模型文件
                $dst = str_replace("template", "", $dst);
                $dst = str_replace(DIRECTORY_SEPARATOR . "html", "", $dst);
                $dst = $templateHtml . $dst;
                $dir = substr($dst, 0, strrpos($dst, DIRECTORY_SEPARATOR));
                if (tp_mkdir($dir)) {
                    $fileContent = @file_get_contents($src);
                    @file_put_contents($dst, $fileContent);
                }
            }
        }

        $columnFieldStr = "";
        if ($reference_model == "0") { //参照模型 0：文章模型;
            $columnFieldStr = implode(",", xn_cfg("article"));
        } elseif ($reference_model == "1") { //参照模型 1:单页模型
            $columnFieldStr = implode(",", xn_cfg("single"));
        }

        // 创建自定义模型附加表
        $table = 'fox_' . $nid;
        $tableSql = <<<EOF
        CREATE TABLE `{$table}` (
          `id`          int(10) NOT NULL    AUTO_INCREMENT,
          
         {$columnFieldStr},
          
          `create_time` datetime DEFAULT NULL  COMMENT  '新增时间',
          `update_time` datetime DEFAULT NULL  COMMENT  '更新时间',
           PRIMARY KEY (`id`)
        ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='$name';
EOF;
        $sqlFormat  = $this->sql_split($tableSql, "fox_");

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

    /**
     * 创建模型表
     * @param $nid 模型标识
     * @param $name 表名称
     * @return array|bool
     */
    public function createModelTable($nid, $name)
    {
        // 创建自定义模型附加表
        $table = 'fox_' . $nid;
        $tableSql = <<<EOF
        CREATE TABLE `{$table}` (
          `id`          int(10) NOT NULL    AUTO_INCREMENT,
          `create_time` datetime DEFAULT NULL COMMENT '新增时间',
          `update_time` datetime DEFAULT NULL COMMENT '更新时间',
           PRIMARY KEY (`id`)
        ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='$name';
EOF;
        $sqlFormat  = $this->sql_split($tableSql, "fox_");
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
}
