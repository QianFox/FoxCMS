<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   14:43
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   14:43
 */

namespace app\admin\controller;

use app\admin\util\Basckup;
use app\admin\util\ModelMg;
use app\common\controller\AdminBase;
use think\facade\Db;
use think\facade\View;

class DataBackup extends AdminBase
{
    private $filterTable = ['fox_admin_log'];

    public function index($page = 1, $pageSize = 1000)
    {
        if ($this->request->isAjax()) {
            $sql = 'SHOW TABLE STATUS';
            $list = Db::query($sql);
            $rlist = [];
            foreach ($list as $table) {
                $tableName = $table['Name'];
                if (!in_array($tableName, $this->filterTable)) {
                    $count = Db::table($tableName)->count();
                    $comment = $table['Comment'];
                    array_push($rlist, ['name' => $tableName, 'count' => $count, 'comment' => $comment]);
                }
            }
            try {
                $bfday = date("Y-m-d", strtotime("-7 day"));
                \app\common\model\DataBackup::where([['create_time', '<', $bfday]])->delete();
            } catch (\Exception $e) {
            }
            $this->success('查询成功', '', $rlist);
        }
        //SQL命令行
        $safeExcute = \app\common\model\Safe::where(['dict_label' => 'sql_execute'])->find();
        $sql_execute_value = 0;
        if ($safeExcute) {
            $sql_execute_value = $safeExcute['dict_value'];
        }
        View::assign("sql_execute_value", $sql_execute_value); //SQL状态
        return view('index');
    }

    // 备份
    public function backup()
    {
        $param = $this->request->param();
        $tableName = $param['tableName'];
        if (empty($tableName)) {
            $this->error("备份失败,表名为空");
        }
        $random_num = func_random_num(18);
        $fileName = $tableName . "-" . $random_num . ".sql";
        $content =  (new Basckup())->getbackupTable($tableName); //模型表与数据内容
        $time = date('Y-m-d');
        $path = app()->getRootPath() . 'data' . DIRECTORY_SEPARATOR . 'backupdata' . DIRECTORY_SEPARATOR . $time . DIRECTORY_SEPARATOR;
        if (!tp_mkdir($path)) {
            $this->error("创建文件夹失败");
        }
        $fpath = write($path, $fileName, $content); //存入模型字段数据
        if (empty($fpath)) {
            $this->error("备份失败");
        }
        $backup_file = $tableName . ".sql";

        $dataBackup = new \app\common\model\DataBackup();

        $cDataBackup = $dataBackup->where(['backup_file' => $time])->find();
        $pid = 0;
        if ($cDataBackup) {
            $pid = $cDataBackup->id;
        } else {
            $dataBackup->save(['pid' => 0, 'backup_file' => $time, 'backup_file_path' => $path,  'table_name' => $time]);
            if (empty($dataBackup->id)) {
                $this->error("操作失败");
            }
            $pid = $dataBackup->id;
        }
        $r = (new \app\common\model\DataBackup())->save(['pid' => $pid, 'backup_file' => $backup_file, 'backup_file_path' => $fpath, 'random_num' => $random_num, 'table_name' => $tableName]);
        if ($r) {
            xn_add_admin_log("备份数据", "backup"); //添加日志
            $this->success('操作成功', "", $fpath);
        } else {
            $this->error("操作失败");
        }
    }

    // 备份全部文件
    public function backupAll()
    {
        $param = $this->request->param();
        $tableList = json_decode($param['tableList']);
        if (array_key_exists("tableList", $param)) {
            if (sizeof($tableList) <= 0) {
                $this->error("备份失败,参数错误");
            }
        }
        $saveAll = [];
        $time = date('Y-m-d');
        $path = app()->getRootPath() . 'data' . DIRECTORY_SEPARATOR . 'backupdata' . DIRECTORY_SEPARATOR . $time . DIRECTORY_SEPARATOR;
        if (!tp_mkdir($path)) {
            $this->error("创建文件夹失败");
        }
        $dataBackup = new \app\common\model\DataBackup();
        $cDataBackup = $dataBackup->where(['backup_file' => $time])->find();
        $pid = 0;
        if ($cDataBackup) {
            $pid = $cDataBackup->id;
        } else {
            $dataBackup->save(['pid' => 0, 'backup_file' => $time, 'backup_file_path' => $path,  'table_name' => $time]);
            if (empty($dataBackup->id)) {
                $this->error("操作失败");
            }
            $pid = $dataBackup->id;
        }

        foreach ($tableList as $tableName) {
            $random_num = func_random_num(18);
            $fileName = $tableName . "-" . $random_num . ".sql";
            $content =  (new Basckup())->backupTable($tableName); //模型表与数据内容
            $fpath = write($path, $fileName, $content); //存入模型字段数据
            if (empty($fpath)) {
                $this->error("备份失败");
            }
            $backup_file = $tableName . ".sql";
            array_push($saveAll, ['pid' => $pid, 'backup_file' => $backup_file, 'backup_file_path' => $fpath, 'random_num' => $random_num, 'table_name' => $tableName]);
        }
        $rSaveData = (new \app\common\model\DataBackup())->saveAll($saveAll);
        if (sizeof($rSaveData) <= 0) {
            $this->error('操作失败');
        }
        xn_add_admin_log("批量备份数据", "backup"); //添加日志
        $this->success('操作成功', "", $rSaveData);
    }

    // 数据还原初始化 备份系列
    public function backupIndex()
    {
        $fArr = array();
        $activepath = '/backupdata';
        $basePath =  app()->getRootPath() . 'data' . $activepath;
        $arr_file = getDirFile($basePath, $activepath, $fArr);
        $list = [];
        foreach ($arr_file as $ar) {
            if ($ar['filetype'] != "dir2" && $ar['filemine'] == "dir") {
                $rdata['id'] = $ar['filepath'];
                $rdata['backup_file'] = $ar['filename'];
                $creationTime = filectime(replaceSymbol($basePath . $ar['filepath']));
                $rdata['create_time'] =  date("Y-m-d H:i:s", $creationTime);
                array_push($list, $rdata);
            }
        }
        $this->success("查询成功", "", $list);
    }

    // 恢复备份系列
    public function restoreSerie()
    {
        $id = $this->request->param("id");
        if (empty($id)) {
            $this->error("恢复失败,参数错误");
        }
        $basePath =  app()->getRootPath() . 'data';
        $sqlFolderPath = replaceSymbol($basePath . $id); //执行模板sql文件
        if (!is_dir($sqlFolderPath)) {
            $this->error("恢复失败,没找到对应恢复数据");
        }
        $files = dirFile($sqlFolderPath);
        if (sizeof($files) > 0) {
            foreach ($files as $file) {
                try {
                    $sqlPath = $sqlFolderPath . DIRECTORY_SEPARATOR . $file;
                    $sqlContent = @file_get_contents($sqlPath);
                    $sqlFormat  = (new ModelMg())->sql_split($sqlContent, env('database.prefix', 'fox_'));
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
                } catch (\Exception $e) {
                    $this->error("恢复失败,执行sql错误");
                }
            }
        } else {
            $this->error("恢复失败");
        }
        $this->success("恢复成功");
    }

    // 删除备份系列
    public function delRestoreSerie()
    {
        $id = $this->request->param("id");
        if (empty($id)) {
            $this->error("删除失败,参数错误");
        }
        $basePath =  app()->getRootPath() . 'data';
        $sqlFolderPath = replaceSymbol($basePath . $id); //执行模板sql文件
        if (!is_dir($sqlFolderPath)) {
            $this->error("恢复失败,没找到对应恢复数据");
        }
        delDir($sqlFolderPath); //删除子文件数据
        $this->success("删除成功");
    }

    // 删除批量备份系列
    public function delRestoreSeries()
    {
        $param = $this->request->param();
        $idList = json_decode($param['idList']);
        if (array_key_exists("idList", $param)) {
            if (sizeof($idList) <= 0) {
                $this->error("删除失败,参数错误");
            }
        }
        $basePath =  app()->getRootPath() . 'data';
        foreach ($idList as $id) {
            $sqlFolderPath = replaceSymbol($basePath . $id); //执行模板sql文件
            if (!is_dir($sqlFolderPath)) {
                continue;
            }
            delDir($sqlFolderPath);
        }
        $this->success("删除成功");
    }

    // 点击备份目录文件
    public function tempFile()
    {
        $param = $this->request->param();
        if ($this->request->isAjax()) {
            $fArr = array();
            $activepath = $param['pid'];
            $basePath =  app()->getRootPath() . 'data';
            $basePath1 = replaceSymbol($basePath . $activepath);
            $arr_file = getDirFile($basePath1, $activepath, $fArr);
            $list = [];
            foreach ($arr_file as $ar) {
                if ($ar['filemine'] == "file") {
                    $rdata['id'] = $ar['filepath'];
                    $rdata['filesize'] = $ar['filesize'];
                    $rdata['backup_file'] = $ar['filename'];
                    $creationTime = filectime(replaceSymbol($basePath . $ar['filepath']));
                    $rdata['create_time'] =  date("Y-m-d H:i:s", $creationTime);
                    array_push($list, $rdata);
                }
            }
            $this->success("查询成功", "", $list);
        }
        View::assign("pid", $param['id']);
        return view();
    }

    // 执行命令
    public function executeCommand()
    {
        $command = $this->request->param("command");
        if (empty($command)) {
            $this->error("参数错误");
        }
        $command = trim($command);
        $commands = explode("\n", $command);
        $rlist = [];
        foreach ($commands as $comm) {
            $comm = trim($comm);
            $type = 1; //执行语句
            $msg = "";
            $list = [];
            if (strpos($comm, 'drop') !== false || strpos($comm, 'DROP') !== false) { //不能删除
                $msg = "删除'数据表'或'数据库'的语句不允许在这里执行！";
            } else {
                if (strpos($comm, 'select') !== false || strpos($comm, 'SELECT') !== false) { //查询语句
                    try {
                        $list = Db::query($comm);
                        $rfList = [];
                        foreach ($list as $result) {
                            $rf = "";
                            foreach ($result as $key => $kr) {
                                $rf .= $key . "=" . $kr . ";";
                            }
                            array_push($rfList, $rf);
                        }
                        $list = $rfList;
                        $msg = '成功查询SQL语句！';
                    } catch (\Exception $e) {
                        $msg = '执行SQL语句失败！';
                    }
                    $type = 2; //查询语句
                } else { //执行语句
                    try {
                        $r = Db::execute($comm);
                        if ($r) {
                            $msg = '成功执行SQL语句！';
                        } else {
                            $msg = '执行SQL语句无结果！';
                        }
                    } catch (\Exception $e) {
                        $msg = '执行SQL语句失败！';
                    }
                }
            }
            array_push($rlist, ['type' => $type, 'msg' => $msg, 'result' => $list, 'command' => $comm]);
        }
        $this->success("执行成功", '', $rlist);
    }

    // 删除恢复文件
    public function delRestoreFile()
    {
        $id = $this->request->param("id");
        if (empty($id)) {
            $this->error("删除失败,参数错误");
        }
        $sqlPath = app()->getRootPath() . 'data' . $id;
        if (!file_exists($sqlPath)) {
            $this->error("恢复失败,参数错误");
        }
        $sqlPath = replaceSymbol($sqlPath);
        $r = @unlink($sqlPath);
        if ($r) {
            $this->success("删除成功");
        }
        $this->error("删除失败");
    }

    // 批量删除恢复文件
    public function delRestoreFiles()
    {
        $param = $this->request->param();
        $idList = json_decode($param['idList']);
        if (array_key_exists("idList", $param)) {
            if (sizeof($idList) <= 0) {
                $this->error("删除失败,参数错误");
            }
        }
        $errorCount = 0;
        $basepath = app()->getRootPath() . 'data';
        foreach ($idList as $id) {
            $sqlPath = replaceSymbol($basepath . $id);
            if (!file_exists($sqlPath)) {
                continue;
            }
            $r = @unlink($sqlPath);
            if (!$r) {
                $errorCount++;
            }
        }
        if ($errorCount > 0) {
            $this->error("删除失败");
        }
        $this->success("删除成功");
    }

    // 恢复数据
    public function restore()
    {
        $id = $this->request->param("id");
        if (empty($id)) {
            $this->error("恢复失败,参数错误");
        }
        $sqlPath = app()->getRootPath() . 'data' . $id;
        if (!file_exists($sqlPath)) {
            $this->error("恢复失败,参数错误");
        }
        $sqlPath = replaceSymbol($sqlPath);
        $sqlContent = @file_get_contents($sqlPath);
        $sqlFormat  = (new ModelMg())->sql_split($sqlContent, env('database.prefix', 'fox_'));
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
        $this->success("恢复成功");
    }

    // 批量恢复数据
    public function restores()
    {
        $param = $this->request->param();
        $idList = json_decode($param['idList']);
        if (array_key_exists("idList", $param)) {
            if (sizeof($idList) <= 0) {
                $this->error("删除失败,参数错误");
            }
        }
        $basepath = app()->getRootPath() . 'data';
        foreach ($idList as $id) {
            $sqlPath = $basepath . $id;
            if (!file_exists($sqlPath)) {
                continue;
            }
            $sqlPath = replaceSymbol($sqlPath);
            $sqlContent = @file_get_contents($sqlPath);
            $sqlFormat  = (new ModelMg())->sql_split($sqlContent, env('database.prefix', 'fox_'));
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
        }

        $this->success("恢复成功");
    }
}