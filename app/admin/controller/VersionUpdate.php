<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   15:43
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   15:43
 */

namespace app\admin\controller;

use app\admin\util\Basckup;
use app\admin\util\ModelMg;
use app\admin\util\TableUtil;
use app\admin\util\Zipdown;
use app\common\controller\AdminBase;
use app\common\model\VersionRecord;
use app\common\util\FileUtil;
use think\Exception;
use think\facade\Db;
use think\facade\View;

class VersionUpdate extends AdminBase
{
    private $filterTables = []; //过滤数据
    private $versionInfo = [];

    public function initialize()
    {
        parent::initialize();
        $versionPath = root_path() . "data/update/version/info.php";
        if (file_exists($versionPath)) {
            $this->versionInfo = require_once($versionPath);
        }
    }

    // 版本信息
    private function version_info($param)
    {
        $paramStr = func_param_pack($param, "&"); //参数串
        $foxcmsDomain = config("adminconfig.version_domain"); //版本地址
        $foxcmsPathUrl = $foxcmsDomain . url('api/Version/info') . "?{$paramStr}";
        $foxcmsPathUrl = replaceSymbol($foxcmsPathUrl);
        $resJson = get_url_content($foxcmsPathUrl);
        return $resJson;
    }

    // 查询版本下载信息
    private function version_getinfo($param)
    {
        $paramStr = func_param_pack($param, "&"); //参数串
        $foxcmsDomain = config("adminconfig.version_domain"); //版本地址
        $foxcmsPathUrl = $foxcmsDomain . url("api/Version/getInfo") . "?{$paramStr}";
        $foxcmsPathUrl = replaceSymbol($foxcmsPathUrl);
        $resJson = get_url_content($foxcmsPathUrl);
        return $resJson;
    }

    // 版本文件
    private function version_files($param)
    {
        $paramStr = func_param_pack($param, "&"); //参数串
        $foxcmsDomain = config("adminconfig.version_domain"); //版本地址
        $foxcmsPathUrl = $foxcmsDomain . url("api/Version/files") . "?{$paramStr}";
        $foxcmsPathUrl = replaceSymbol($foxcmsPathUrl);
        $resJson = get_url_content($foxcmsPathUrl);
        return $resJson;
    }

    // 检查下一版本
    private function version_checkVersion($param)
    {
        $paramStr = func_param_pack($param, "&"); //参数串
        $foxcmsDomain = config("adminconfig.version_domain"); //版本地址
        $foxcmsPathUrl = $foxcmsDomain . url("api/Version/checkVersion") . "?{$paramStr}";
        $foxcmsPathUrl = replaceSymbol($foxcmsPathUrl);
        $resJson = get_url_content($foxcmsPathUrl);
        return $resJson;
    }

    // 版本列表
    public function index()
    {
        $param = $this->request->param();

        if ($this->request->isAjax()) {
            $param['domain'] = $this->domainNo;
            $resJson = $this->version_info($param);
            if (empty($resJson)) {
                $this->success('查询成功', '', []);
            }
            $res = json_decode($resJson);
            $data = $res->data; //授权信息
            //查询所有版本记录
            $vrList = VersionRecord::select();
            //            $versionInfo
            $list = [];
            foreach ($data as $d) {
                $rd = get_object_vars($d);
                $remote_version_id = $rd['id'];
                $iscopy = 0; //副本
                foreach ($vrList as $vr) {
                    if ($vr['remote_version_id'] == $remote_version_id) {
                        $iscopy = 1;
                        break;
                    }
                }
                $iscur = false;
                if ($rd['only'] == $this->versionInfo['only']) {
                    $iscur = true;
                }
                $rd['iscur'] = $iscur;
                $rd['iscopy'] = $iscopy;
                array_push($list, $rd);
            }
            $this->success('查询成功', '', $list);
        }
        return view();
    }

    // 更新单个文件
    public function singleUpdateFile()
    {
        $id = $this->request->param('id');
        $only = $this->request->param("only");
        $resJson = $this->version_files(['id' => $id, 'domain' => $this->domainNo, 'only' => $only]);
        $res = json_decode($resJson);
        $data = $res->data; //授权信息
        $dirs = []; //目录
        $rdata = [];
        $is_sql = 0; //是否存在sql文件
        foreach ($data as $d) {
            $file_relative_path = $d->file_relative_path;
            if ($is_sql == 0) {
                if (str_ends_with($file_relative_path, "cover.php")) {
                    $is_sql = 1;
                }
            }
            $update_file = root_path() . $file_relative_path; //要更新的文件
            $update_file = replaceSymbol($update_file);
            $is_update = 0; //新增
            if (file_exists($update_file)) {
                $is_update = 1; //更新
            }
            array_push($rdata, ['file_relative_path' => $file_relative_path, 'update_file_relative_path' => $file_relative_path, 'is_update' => $is_update]);
            $curDirArr = explode("/", $file_relative_path);
            unset($curDirArr[(sizeof($curDirArr) - 1)]);
            $update_dir = implode("/", $curDirArr);
            $curDir = root_path() . $update_dir . "/";
            $curDir = replaceSymbol($curDir);
            $isExist = false;
            foreach ($dirs as $dir) {
                if ($dir['dir'] == $curDir) {
                    $isExist = true;
                    break;
                }
            }
            if (!$isExist) {
                $writeable = new_is_writeable($curDir);
                if (empty($update_dir)) {
                    $update_dir = get_app_name();
                }
                if ($writeable == 0) {
                    if (!tp_mkdir($curDir)) {
                        $writeable = 0;
                    } else {
                        $writeable = 1;
                    }
                }
                array_push($dirs, ['dir' => $update_dir, 'writeable' => $writeable]);
            }
        }
        View::assign("is_sql", $is_sql); //是否存在sql文件
        View::assign("uFiles", $rdata);
        $only = $this->request->param('only');
        View::assign("only", $only); //下载标识
        //文件夹路径权限
        View::assign("dirs", $dirs);
        //版本id
        View::assign("id", $id);
        return view('update_file');
    }

    // 更新文件
    public function updateFile()
    {
        $param = $this->request->param();
        //查询当前版本
        $resJson = $this->version_info(['id' => $param['id'], "domain" => $this->domainNo]);
        $is_auth = 1;
        if (!empty($resJson)) {
            $res = json_decode($resJson);
            $data = $res->data;
            if (sizeof($data) > 0) {
                $param['only'] = $data[0]->only;
                $is_auth = $data[0]->is_auth;
            }
        }
        if ($is_auth == 0) {
            $noDomainUrl = "noauthorize";
            $this->success("未授权调整", "", $noDomainUrl);
        } elseif ($this->request->isAjax()) {
            $this->checkUpdateVersion($param['id'], $param['only'], $this->domainNo);
            $this->success("确认更新");
        } else {
            $singleUrl = url("VersionUpdate/singleUpdateFile", $param);
            $this->redirect($singleUrl);
            exit();
        }
    }

    // 检查sql更新文件是否备份
    public function checkBackupSql()
    {
        $beforeTime = time() - 1 * 60 * 60; //前一个小时数据,time()方法返回的秒
        $backList = Db::name('data_backup')->whereTime('create_time', '>=', strtotime(date('Y-m-d H:i:s', $beforeTime)))->select();
        $is_back = (sizeof($backList) > 0) ? 1 : 0;
        $this->success("判断是否存在sql文件", "", $is_back);
    }

    // 备份全部文件
    public function backupAll()
    {
        $time = date('Y-m-d');
        $path = app()->getRootPath() . 'data' . DIRECTORY_SEPARATOR . 'backupdata' . DIRECTORY_SEPARATOR . $time . DIRECTORY_SEPARATOR;
        if (!tp_mkdir($path)) {
            $this->error("创建文件夹失败");
        }
        $dataBackup = new \app\common\model\DataBackup();
        $cDataBackup = $dataBackup->where(['backup_file' => $time])->find();
        if ($cDataBackup) {
            $pid = $cDataBackup->id;
        } else {
            $dataBackup->save(['pid' => 0, 'backup_file' => $time, 'backup_file_path' => $path,  'table_name' => $time]);
            if (empty($dataBackup->id)) {
                $this->error("操作失败");
            }
            $pid = $dataBackup->id;
        }
        //查询数据库所有表
        $sql = 'SHOW TABLE STATUS';
        $list = Db::query($sql);
        $tableList = [];
        foreach ($list as $table) {
            $table_name = $table['Name'];
            if (!in_array($table_name, $this->filterTables)) {
                array_push($tableList, $table_name);
            }
        }
        $saveAll = [];
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
            $this->error('备份失败');
        }
        $this->success('备份成功');
    }

    // 更新处理
    public function checkUpdateVersion($id, $only, $domain = "")
    {
        $vrList = VersionRecord::where(['remote_version_id' => $id])->order(['version' => 'desc'])->select(); //更新版本记录信息
        $size = sizeof($vrList);
        if ($size > 0) { //更新过了
            $this->error("当前版本你已更新过了");
        } else { //未更新
            $resJson = $this->version_checkVersion(['domain' => $domain, "only" => $this->versionInfo['only']]); //判断更新版本是否匹配
            if (empty($resJson)) {
                $this->error("未知版本，请联系客服");
            }
            $res = json_decode($resJson);
            if ($res->code == 1) { //查询成功
                $data = $res->data;
                if (!($id == $data->id && $only == $data->only)) {
                    $this->error("不能跳版本更新");
                } else {
                    if ($data->flag == "last") {
                        $this->error($only . "不要乱搞,已是最新版本");
                    }
                }
            } else {
                $this->error($res->msg);
            }
        }
    }

    // 下载更新
    public function downloadUpdate()
    {
        $only = $this->request->param('only');
        $id = $this->request->param('id');
        $this->checkUpdateVersion($id, $only, $this->domainNo); //更新处理
        $resJson = $this->version_getinfo(['id' => $id, 'only' => $only, 'domain' => $this->domainNo]);
        if (empty($resJson)) {
            $this->error("未知版本，请联系客服");
        }
        $pack_file_url = "";
        $res = json_decode($resJson);
        if ($res->code == 1) { //查询成功
            $data = $res->data;
            if ($data) {
                $pack_file_url = $data->pack_file_url;
            }
        } else {
            $this->error($res->msg);
        }
        if (empty($pack_file_url)) {
            $this->error("未知版本更新失败，请联系客服");
        }
        $fileUtil = new FileUtil();
        $path = root_path() . 'data/update/backup/' . date("Y-m-d") . '/';
        $path = replaceSymbol($path);
        $down_path = $path . '/linshi/';
        if (!tp_mkdir($path)) {
            $this->error("创建文件夹失败");
        }
        $filepath = $fileUtil->download($pack_file_url, "", $down_path);
        $filepath = replaceSymbol($filepath);
        if (is_file($filepath)) {
            $this->success("下载成功", "", $filepath);
        }
        $this->error("下载失败");
    }

    // 解压更新文件
    function unFileUpdate()
    {
        $filepath = $this->request->param("filepath", "");
        if (empty($filepath)) {
            $this->error("解压失败");
        }
        $zipdown = new Zipdown();
        $path = root_path() . 'data/update/backup/' . date("Y-m-d") . '/';
        $path = replaceSymbol($path);
        $down_path = $path . 'linshi/';
        if (!tp_mkdir($path)) {
            $this->error("创建文件夹失败");
        }
        $r = $zipdown->unZipFile($filepath, $down_path);
        if ($r) {
            @unlink($filepath); //删除
            $this->success("减压成功", "", $down_path);
        }
        $this->error("减压失败");
    }

    // 文件更新
    public function fileUpdate()
    {
        $only = $this->request->param('only');
        $id = $this->request->param('id');
        $resJson = $this->version_getinfo(['id' => $id, 'only' => $only, 'domain' => $this->domainNo]);
        if (empty($resJson)) {
            $this->error("未知版本，请联系客服");
        }
        $saveVersion = [];
        $res = json_decode($resJson);
        if ($res->code == 1) { //查询成功
            $data = $res->data;
            if ($data) {
                $saveVersion = [
                    'version' => $data->code,
                    'only' => $data->only
                ];
            }
        } else {
            $this->error($res->msg);
        }
        $path = root_path() . 'data/update/backup/' . date("Y-m-d") . '/';
        $path = replaceSymbol($path);
        $down_path = $this->request->param("down_path", "");
        //版本路径
        $versionPath = root_path() . 'data/update/version/';
        $versionPath = replaceSymbol($versionPath);
        if (!tp_mkdir($versionPath)) {
            $this->error("创建文件夹失败");
        }
        $resJson = $this->version_files(['id' => $id, 'domain' => $this->domainNo]);
        $res = json_decode($resJson);
        $data = $res->data; //授权信息
        $rootPath = root_path(); //根目录
        $uFiles = []; //打包旧文件
        //执行sql文件
        $sqlFileArr = []; //更新的sql文件
        $oldBackPath = "";
        foreach ($data as $d) {
            $file_relative_path = $d->file_relative_path;
            $full_path = $rootPath . $file_relative_path;
            $full_path = replaceSymbol($full_path);
            if ((strpos($file_relative_path, "back.php") !== false)
                || (strpos($file_relative_path, "cover.php") !== false)
            ) {
                array_push($sqlFileArr, $file_relative_path);

                //备份对应旧sql语句
                if ((strpos($file_relative_path, "back.php") !== false)) {
                    $new_back_path = replaceSymbol($down_path . $file_relative_path);
                    $backInfoList = require_once($new_back_path);
                    $oldBack = $this->restoreBackup($backInfoList, $versionPath);
                    array_push($uFiles, $oldBack);
                    $oldBackPath = $oldBack['full_path'];
                }
            } else {
                array_push($uFiles, ['full_path' => $full_path, 'file_relative_path' => $file_relative_path]);
            }
        }
        //打包旧文件生成副本
        $filename = func_random_num(18) . date('YmdHis') . '.zip';
        $zipName = $path . $filename;
        // 实例化类,使用本类，linux需开启zlib，windows需取消php_zip.dll前的注释
        $zip = new \ZipArchive();
        $res = $zip->open($zipName, \ZipArchive::OVERWRITE | \ZipArchive::CREATE);
        if (!$res) {
            $this->error('无法打开文件，或者文件创建失败');
        }
        // 文件打包 旧文件打包
        $is_exist = false;
        foreach ($uFiles as $file) {
            $full_path = $file["full_path"];
            if (file_exists($full_path)) {
                $is_exist = true;
                $file_relative_path = $file["file_relative_path"];
                $zip->addFile($full_path, $file_relative_path);
            }
        }
        //添加数据判断打包文件
        if (file_exists($versionPath . "cover.php")) {
            $zip->addFile($versionPath . "cover.php", "data/update/version/cover.php");
        }
        if (file_exists($versionPath . "back.php")) {
            $zip->addFile($versionPath . "back.php", "data/update/version/back.php");
        }
        if (file_exists($versionPath . "info.php")) {
            $zip->addFile($versionPath . "info.php", "data/update/version/info.php");
        } else {
            $this->error("更新失败，缺少当前版本信息");
        }
        // 关闭
        $zip->close();
        // 验证文件是否存在
        if ($is_exist) {
            if (!file_exists($zipName)) {
                $this->error("文件不存在" . $zipName);
            }
        }
        //删除旧备份记录
        if (file_exists($oldBackPath)) {
            @unlink($oldBackPath);
        }
        //更新替换文件
        foreach ($uFiles as $file) {
            $source = $down_path . $file['file_relative_path'];
            $source = replaceSymbol($source);
            if (file_exists($source)) {
                $dest = $file['full_path'];
                $rc = copy($source, $dest);
                if (!$rc) {
                    $this->error("更新失败");
                }
            }
        }
        //替换数据判断文件
        $table_prefix = config("database.connections.mysql.prefix");
        if (sizeof($sqlFileArr) > 0) {
            foreach ($sqlFileArr as $file) {
                $source = $down_path . $file;
                $source = replaceSymbol($source);
                if (file_exists($source)) {
                    if ((strpos($file, "cover.php") !== false)) {
                        copy($source, $versionPath . "cover.php");
                        //执行sql文件
                        $sqlContent = file_get_contents($source);
                        $sqlFormat  = (new ModelMg())->sql_split($sqlContent, $table_prefix);
                        // 执行SQL语句
                        $counts = count($sqlFormat);
                        for ($i = 0; $i < $counts; $i++) {
                            $sql = trim($sqlFormat[$i]);
                            if (trim($sql) == '')
                                continue;
                            try {
                                Db::execute($sql);
                            } catch (\Exception $e) {
                                //                                    $this->error($e->getMessage());
                            }
                        }
                    } elseif ((strpos($file, "back.php") !== false)) {
                        copy($source, $versionPath . "back.php");
                    }
                }
            }
        } else {
            if (file_exists($versionPath . "cover.php")) {
                @unlink($versionPath . "cover.php"); //删除
            }
            if (file_exists($versionPath . "back.php")) {

                @unlink($versionPath . "back.php"); //删除
            }
        }
        //删除临时文件夹
        delDir($down_path);
        //修改当前版本信息
        set_php_arr($versionPath,  'info.php', $saveVersion);
        //记录更新信息
        $versionRecord = VersionRecord::where(['remote_version_id' => $id])->find();
        if ($versionRecord) {
            @unlink($versionRecord['copy_path']); //删除之前的
            (new VersionRecord())->update(['id' => $versionRecord['id'], 'copy_path' => $zipName]);
        } else {
            (new VersionRecord())->save(['copy_path' => $zipName, 'remote_version_id' => $id, "version" => $saveVersion['version']]);
        }
        $this->success("更新成功", "", $saveVersion['version']);
    }

    // 备份旧的sql
    private function restoreBackup($backInfoList, $backPath)
    {
        if (!tp_mkdir($backPath)) {
            $this->error("创建文件夹失败");
        }
        $fileName = "old_back.php";
        try {
            if (file_exists($backPath . $fileName)) {
                @unlink($backPath . $fileName); //先删
            }
            foreach ($backInfoList as $backInfo) {
                $type = $backInfo['type'];
                $table = $backInfo['table'];
                if (!TableUtil::check_table($table)) { //判断表是否存在
                    continue;
                }
                $oldDataSql = "";
                if ($type == "drop") { //删除表
                    $oldDataStructSql = ((new Basckup())->getbackupTable($table));
                    write($backPath, $fileName, "{$oldDataStructSql[0]}\n");
                    if (sizeof($oldDataStructSql) >= 1) {
                        write($backPath, $fileName, "{$oldDataStructSql[1]}\n");
                    }
                } elseif ($type == "update") { //更新值
                    $tData = Db::table($table)->field($backInfo['field'])->find($backInfo['id']);
                    $val = $tData[$backInfo['field']];
                    if ($backInfo['value_type'] == "string") {
                        $val = "'{$val}'";
                    }
                    $oldDataSql = "UPDATE `{$table}` SET `{$backInfo['field']}` = {$val} WHERE `id` = {$backInfo['id']};";
                } elseif ($type == "delete") { //删除某值
                    $oldDataSql = (new Basckup())->getBackupTableData($table, "id={$backInfo['id']}");
                }
                write($backPath, $fileName, "{$oldDataSql}\n");
            }
        } catch (\Exception $e) {
        }
        return ['full_path' => "{$backPath}{$fileName}", 'file_relative_path' => "data/update/version/{$fileName}"];
    }

    // 还原
    public function restoreFile()
    {
        $id = $this->request->param('id');
        //记录更新信息
        $versionRecord = VersionRecord::where(['remote_version_id' => $id])->find();
        if (!$versionRecord) {
            $this->error("没有查到还原数据");
        }

        $restoreKey = -1;
        $vrList = VersionRecord::order(['version' => 'desc'])->select(); //更新版本记录信息
        if (sizeof($vrList) > 1) { //是否
            foreach ($vrList as $key => $vr) {
                if ($id == $vr['remote_version_id']) {
                    $restoreKey = $key;
                }
            }
            if ($restoreKey != 0) {
                $this->error("抱歉不能跳版本还原");
            }
        }

        $copy_path = $versionRecord['copy_path'];
        if (!file_exists($copy_path)) {
            $this->error("没有找到还原数据");
        }
        $resJson = $this->version_files(['id' => $id, 'domain' => $this->domainNo]);
        $res = json_decode($resJson);
        $data = $res->data; //授权信息
        $rootPath = root_path(); //根目录

        //数据判断打包文件
        $versionPath = root_path() . 'data/update/version/';
        $versionPath = replaceSymbol($versionPath);
        foreach ($data as $d) {
            $file_relative_path = $d->file_relative_path;
            $full_path = $rootPath . $file_relative_path;
            $full_path = replaceSymbol($full_path);
            if ((strpos($file_relative_path, "back.php") !== false)) { //还原执行
                if (file_exists($versionPath . "back.php")) {
                    $backInfoList = require_once($versionPath . "back.php");
                    $this->restoreExecute($backInfoList);
                }
            }
            if (file_exists($full_path)) {
                @unlink($full_path); //删除更新文件
            }
        }
        $zipdown = new Zipdown();
        $r = $zipdown->unZipFile($copy_path, $rootPath);
        if ($r) {
            @unlink($copy_path); //删除更新下载包
            VersionRecord::destroy($versionRecord['id']); //删除文件信息
            //执行恢复中数据
            $table_prefix = config("database.connections.mysql.prefix");
            if (file_exists($versionPath . "old_back.php")) { //是否存在
                $sqlContent = file_get_contents($versionPath . "old_back.php");
                $sqlFormat  = (new ModelMg())->sql_split($sqlContent, $table_prefix);
                // 执行SQL语句
                $counts = count($sqlFormat);
                for ($i = 0; $i < $counts; $i++) {
                    $sql = trim($sqlFormat[$i]);
                    if (trim($sql) == '')
                        continue;
                    Db::execute($sql);
                }
                @unlink($versionPath . "old_back.php"); //删除
            }
            $this->success("恢复成功");
        }
        $this->error("恢复失败");
    }

    // 恢复操作
    private function restoreExecute($backInfoList)
    {
        foreach ($backInfoList as $backInfo) {
            $type = $backInfo['type'];
            $table = $backInfo['table'];
            if (!TableUtil::check_table($table)) { //判断表是否存在
                continue;
            }
            try {
                if ($type == "create") { //创建
                    //删表
                    $r = TableUtil::del_table($table);
                } elseif ($type == "insert") { //添加数据
                    //删数据
                    Db::table($table)->where(['id' => $backInfo['id']])->delete();
                } elseif ($type == "alter") { //添加字段
                    //删表字段
                    $r = TableUtil::del_column($table, $backInfo['field']);
                }
            } catch (\Exception $e) {
            }
        }
    }

    // 说明
    public function explain()
    {
        $id = $this->request->param('id');
        $resJson = $this->version_files(['id' => $id, 'domain' => $this->domainNo]);

        $res = json_decode($resJson);
        if ($res->code == 0) { //未授权
            if ($this->request->isAjax()) {
                $this->success("未授权", "", "noauthorize");
            }
        } else {
            if ($this->request->isAjax()) {
                $this->success("授权");
            }
        }
        View::assign("authorize_code", $res->code);
        $data = $res->data; //授权信息
        $rdata = [];
        foreach ($data as $d) {
            $file_relative_path = $d->file_relative_path;
            array_push($rdata, ['file_relative_path' => $file_relative_path]);
        }
        View::assign("uFiles", $rdata);
        //版本信息
        $resJson = $this->version_info(['id' => $id, 'domain' => $this->domain]);
        $res = json_decode($resJson);
        $dataVersionList = $res->data; //授权信息
        $version = [];
        if (sizeof($dataVersionList) > 0) {
            $version = $dataVersionList[0];
            $version = get_object_vars($version);
        }
        View::assign("version", $version);
        return view();
    }
}