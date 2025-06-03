<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   14:10
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   14:10
 */

namespace app\admin\controller;

use app\admin\util\Basckup;
use app\admin\util\ModelMg;
use app\admin\util\Zipdown;
use app\common\controller\AdminBase;
use app\common\model\AuthRule;
use think\facade\Cache;
use think\facade\Db;
use think\facade\View;

// 模型管理
class ModelManage extends AdminBase
{
    public function index($page = 1, $pageSize = 100)
    {
        $param = $this->request->param();
        if (array_key_exists('bcid', $param)) {
            View::assign('bcid', $param['bcid']);
        }
        if ($this->request->isAjax()) {

            $where = [];
            array_push($where, ['is_delete', '=', 0]); //未删除
            if (array_key_exists('isSystem', $param) && !empty($param['isSystem'])) { //字段分类，1=系统(不可修改)，0=自定义
                if ($param['isSystem'] == '系统') {
                    array_push($where, ['is_system', '=', 1]);
                } else if ($param['isSystem'] == '自定义') {
                    array_push($where, ['is_system', '=', 0]);
                }
            }
            if (array_key_exists('keyword', $param) && !empty($param['keyword'])) {
                array_push($where, ['name', 'like', '%' . $param['keyword'] . '%']);
            }
            $modelRecord = new \app\common\model\ModelRecord();
            $list = $modelRecord->where($where)->order('create_time', 'desc')->paginate(['page' => $page, 'list_rows' => $pageSize]);
            $this->success('查询成功', null, $list);
        }
        return view('index');
    }

    public function add()
    {
        $param = $this->request->param();
        //功能面包屑
        $bcid = $param['bcid'];
        View::assign('bcid', $bcid);
        $columnId = $param['columnId']; //栏目id
        $authRule = AuthRule::find($columnId);
        $bcidStr = str_replace(",", "_", $authRule->tier);
        $breadcrumb = AuthRule::getBreadcrumb($bcidStr);
        array_push($breadcrumb, ['id' => '', 'title' => '添加模型', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/ModelManage/add', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);
        View::assign("columnId", $columnId);

        if ($this->request->isAjax()) {
            $param = $this->request->post();
            if (empty($param['nid']) || empty($param['name'])) {
                $this->error("缺少必填信息！");
            }
            $mr = \app\common\model\ModelRecord::where('nid', $param['nid'])->find();
            if ($mr) {
                $this->error("模型标识已存在");
            }
            $nid = $param['nid'];
            if (is_exist_table('fox_' . $nid)) {
                $this->error("表已存在");
            }

            $cm = ucfirst($nid); //首字母大写
            $templateHtml = $this->templateHtml;
            $r = (new ModelMg())->createModel($nid, $cm, $templateHtml, $param['name'], $param["reference_model"]); //创建模型和模型表

            $modelRecord = [
                "nid" => $nid,
                "name" => $param["name"],
                "table" => 'fox_' . $nid,
                "ctl_name" => $cm,
                "status" => $param["status"],
                "reference_model" => $param["reference_model"]
            ];
            (new \app\common\model\ModelRecord())->save($modelRecord);

            Cache::clear();
            xn_add_admin_log("添加模型", "model", $param['name']); //添加日志
            $this->success("操作成功", null, $r);
        }

        return  view('add');
    }

    public function edit()
    {
        $param = $this->request->param();
        $columnId = $param['columnId']; //栏目id
        $id = $param['id'];
        $authRule = AuthRule::find($columnId);
        $bcidStr = str_replace(",", "_", $authRule->tier);
        $breadcrumb = AuthRule::getBreadcrumb($bcidStr);
        array_push($breadcrumb, ['id' => '', 'title' => '编辑模型', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/ModelManage/edit', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);
        View::assign("columnId", $columnId);

        $modelRecord = \app\common\model\ModelRecord::find($id);
        View::assign("modelRecord", $modelRecord);
        return  view('edit');
    }

    public function delete()
    {
        $param = $this->request->param();
        $id = $param['id'];
        if (!array_key_exists('id', $param)) {
            $this->error('参数错误');
        }
        $mr = \app\common\model\ModelRecord::find($id);
        if ($mr['is_system'] == 1) {
            $this->error("系统模型不允许删除");
        }
        if ($mr) {
            if ($mr->nid == "ask") { //文档模型
                Db::execute("DROP TABLE fox_ask_problem");
            }
            \app\common\model\ModelRecord::destroy($id); //删除
            $table = $mr->table;
            $sql = "DROP TABLE $table";
            Db::execute($sql);
            //删除模型文件
            $adminController = app()->getRootPath() . "app" . DIRECTORY_SEPARATOR . "admin" . DIRECTORY_SEPARATOR . "controller" . DIRECTORY_SEPARATOR . $mr->ctl_name . ".php"; //后台控制器
            @unlink($adminController); //删除
            $adminViewPath = app()->getRootPath() . "app" . DIRECTORY_SEPARATOR . "admin" . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR . $mr->nid . DIRECTORY_SEPARATOR;
            delDir($adminViewPath); //删除
            $commonModel = app()->getRootPath() . "app" . DIRECTORY_SEPARATOR . "common" . DIRECTORY_SEPARATOR . "model" . DIRECTORY_SEPARATOR . $mr->ctl_name . ".php"; //模型
            @unlink($commonModel); //删除
            $homeController = app()->getRootPath() . "app" . DIRECTORY_SEPARATOR . "home" . DIRECTORY_SEPARATOR . "controller" . DIRECTORY_SEPARATOR . $mr->ctl_name . ".php"; //前端控制器
            @unlink($homeController); //删除
            $publicTPath = $this->templateHtml;
            $publicTView = $publicTPath . "view_" . $mr->nid . ".html";
            @unlink($publicTView); //删除
            $publicTList = $publicTPath . "list_" . $mr->nid . ".html";
            @unlink($publicTList); //删除
            $sqlPath = app()->getRootPath() . config('filesystem.sql.model') . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . $mr->table . ".sql";
            @unlink($sqlPath); //删除
            Cache::clear();
            xn_add_admin_log("删除模型", "model"); //添加日志
            $this->success('操作成功');
        } else {
            $this->error('未找到模型');
        }
    }

    public function deletes()
    {
        $param = $this->request->param();

        if (array_key_exists("idList", $param)) {
            $idList = json_decode($param['idList']);
            $mrList = \app\common\model\ModelRecord::whereIn('id', implode(",", $idList))->select();
            //有系统模型不删除
            foreach ($mrList as $mr) {
                if ($mr['is_system'] == 1) {
                    $this->error($mr["name"] . "是系统模型不允许删除");
                }
            }
            \app\common\model\ModelRecord::destroy($idList); //批量删除
            $count = 0;
            foreach ($mrList as $mr) {
                $table = $mr->table;
                $sql = "DROP TABLE $table";
                Db::execute($sql);
                $count++;
                //删除模型文件
                $adminController = app()->getRootPath() . "app" . DIRECTORY_SEPARATOR . "admin" . DIRECTORY_SEPARATOR . "controller" . DIRECTORY_SEPARATOR . $mr->ctl_name . ".php"; //后台控制器
                @unlink($adminController); //删除
                $adminViewPath = app()->getRootPath() . "app" . DIRECTORY_SEPARATOR . "admin" . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR . $mr->nid . DIRECTORY_SEPARATOR;
                delDir($adminViewPath); //删除
                $commonModel = app()->getRootPath() . "app" . DIRECTORY_SEPARATOR . "common" . DIRECTORY_SEPARATOR . "model" . DIRECTORY_SEPARATOR . $mr->ctl_name . ".php"; //模型
                @unlink($commonModel); //删除
                $homeController = app()->getRootPath() . "app" . DIRECTORY_SEPARATOR . "home" . DIRECTORY_SEPARATOR . "controller" . DIRECTORY_SEPARATOR . $mr->ctl_name . ".php"; //前端控制器
                @unlink($homeController); //删除
                $publicTPath = $this->templateHtml;
                $publicTView = $publicTPath . "view_" . $mr->nid . ".html";
                @unlink($publicTView); //删除
                $publicTList = $publicTPath . "list_" . $mr->nid . ".html";
                @unlink($publicTList); //删除
                $sqlPath = app()->getRootPath() . config('filesystem.sql.model') . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . $mr->table . ".sql";
                @unlink($sqlPath); //删除
            }
            if (sizeof($idList) == $count) {
                Cache::clear();
                xn_add_admin_log("批量删除模型", "model"); //添加日志
                $this->success('操作成功');
            } else {
                $this->error('操作失败');
            }
        }
    }

    // 导出模型
    public function export($id)
    {
        $modelRecord = \app\common\model\ModelRecord::find($id);
        try {
            if ($modelRecord) {
                $adminController = app()->getRootPath() . "app" . DIRECTORY_SEPARATOR . "admin" . DIRECTORY_SEPARATOR . "controller" . DIRECTORY_SEPARATOR . $modelRecord->ctl_name . ".php"; //后台控制器
                $adminViewPath = app()->getRootPath() . "app" . DIRECTORY_SEPARATOR . "admin" . DIRECTORY_SEPARATOR . "view" . DIRECTORY_SEPARATOR . $modelRecord->nid . DIRECTORY_SEPARATOR;
                $adminViewFiles = dirFile($adminViewPath); //后台文件
                $arrayFile = array(); //保存的模型所需文件
                foreach ($adminViewFiles as $avF) {
                    array_push($arrayFile, $adminViewPath . $avF);
                }
                $commonModel = app()->getRootPath() . "app" . DIRECTORY_SEPARATOR . "common" . DIRECTORY_SEPARATOR . "model" . DIRECTORY_SEPARATOR . $modelRecord->ctl_name . ".php"; //模型
                $homeController = app()->getRootPath() . "app" . DIRECTORY_SEPARATOR . "home" . DIRECTORY_SEPARATOR . "controller" . DIRECTORY_SEPARATOR . $modelRecord->ctl_name . ".php"; //前端控制器
                $publicTPath = str_replace("/", DIRECTORY_SEPARATOR, $this->templateHtml);
                $publicTView = $publicTPath . "view_" . $modelRecord->nid . ".html";
                $publicTList = $publicTPath . "list_" . $modelRecord->nid . ".html";
                $zip = new Zipdown();
                array_push($arrayFile, $adminController);
                array_push($arrayFile, $commonModel);
                array_push($arrayFile, $homeController);
                array_push($arrayFile, $publicTView);
                array_push($arrayFile, $publicTList);
                //生成sql
                $path = app()->getRootPath() . config('filesystem.sql.model') . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR;
                $fileName = $modelRecord->table . ".sql";
                $content =  (new Basckup())->backupTable($modelRecord->table); //模型表与数据内容
                $c =  (new Basckup())->backupTableData("fox_model_field", "model='" . $modelRecord->nid . "'"); //模型字段数据
                $cmr =  (new Basckup())->backupTableData("fox_model_record", "nid='" . $modelRecord->nid . "'"); //模型数据
                write($path, $fileName, $cmr); //模型数据
                write($path, $fileName, $content); //存入模型及数据
                $fpath = write($path, $fileName, $c); //存入模型字段数据
                if (!empty($fpath)) {
                    array_push($arrayFile, $fpath);
                }
                //生成结束
                $zip->zip_file($arrayFile, $modelRecord->nid);
                exit("导出失败");
            }
        } catch (\Exception $e) {
            $this->error('导出失败,对应模板信息不存在');
        }
    }

    // 修改状态
    public function updateStatus()
    {
        $param = $this->request->param();
        $idList = json_decode($param['idList']);
        if (sizeof($idList) <= 0) {
            $this->error("操作失败，请选择对应启用数据");
        }
        $status = intval($param['status']);
        $modelRecord = new \app\common\model\ModelRecord();
        try {
            $modelRecord->whereIn("id", implode(",", $idList))->update(["status" => $status]);
        } catch (\Exception $e) {
            $this->error('操作失败,' . $e->getMessage());
        }
        $this->success('操作成功');
    }
}