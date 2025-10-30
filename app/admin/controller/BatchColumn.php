<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   14:34
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   14:34
 */

namespace app\admin\controller;

use app\common\model\Column as ColumnModel;

use app\common\controller\AdminBase;
use app\common\util\PinyinUtil;
use think\facade\View;

class BatchColumn extends AdminBase
{
    public function index()
    {
        $param = $this->request->param();
        if (array_key_exists('bcid', $param)) {
            View::assign('bcid', $param['bcid']);
        }
        //模型
        $modelRecords = (new \app\common\model\ModelRecord())->field("nid as id, name as title")->where(['status' => 1])->select();

        //查询所有栏目
        $columnList = \app\common\model\Column::where([['level', '<', 2], ['column_model', '=', $modelRecords[0]['id']], ['lang', '=', $this->getMyLang()]])->field('name,id')->order('level asc')->select();
        $columns = [['name' => '顶层栏目', 'id' => 0]];
        foreach ($columnList as $k => $cm) {
            array_push($columns, $cm);
        }
        if ($this->request->isAjax()) {
            $columnDatas  = ColumnModel::order('level asc')->order('sort asc')->select();
            $columnList = $this->channelLevel($columnDatas);
            $this->success('栏目查询成功', '', $columnList);
        }
        $arr_file = getDirFile($this->templateHtml, $this->templateHtml, $fArr, $this->template['template']);

        return view('index', ['modelRecords' => $modelRecords, 'columns' => $columns, 'arr_file' => $arr_file,  "activepath" => $this->relativeTemplateHtml]);
    }

    // 返回多层栏目
    private function channelLevel($dataArr, $pid = 0, $fieldPri = 'id', $fieldPid = 'pid', $level = 1)
    {
        if (empty($dataArr)) {
            return array();
        }
        $rArr = [];
        foreach ($dataArr as $v) {
            if ($v[$fieldPid] == $pid) {
                $arr = array();
                $arr['columnId'] = $v->id;
                $arr['columnName'] = $v->name;
                $arr['isShow'] = $v->status;
                if (sizeof($v->uploadFiles) > 0) {
                    $arr['columnImg'] = [['url' => $v->uploadFiles[0]->url, 'id' => $v->uploadFiles[0]->id]];
                } else {
                    $arr['columnImg'] = [];
                }
                $arr['model'] = $v->column_model;
                $arr['level'] = $level;
                $arr['children'] = self::channelLevel($dataArr, $v[$fieldPri], $fieldPri, $fieldPid, $level + 1);
                array_push($rArr, $arr);
            }
        }
        return $rArr;
    }

    public function save()
    {
        $param = $this->request->post();
        if (!array_key_exists('columns', $param)) {
            $this->error('保存失败参数不存在');
        }
        $pid = $param['pid'];
        $columns = json_decode($param['columns']);
        if (empty($pid)) {
            $pid = 0;
        }
        $clang = $this->getMyLang();
        if (sizeof($columns) > 0) {
            if (intval($pid) == 0) { //顶层栏目
                $index = 1;
                foreach ($columns as $key => $column) {
                    $this->saveColumn($column, 0, '', 0, $index, $clang);
                    $index++;
                }
            } else { //其他栏目
                $columnP = ColumnModel::find($pid);
                if ($columnP) {
                    $index = 1;
                    foreach ($columns as $key => $column) {
                        $this->saveColumn($column, intval($pid), $columnP->tier, $columnP->level, $index, $clang);
                        $index++;
                    }
                } else {
                    $this->error('所属栏目不存在');
                }
            }
        } else {
            $this->error('保存失败栏目数据不能为空');
        }
        xn_add_admin_log("批量添加栏目", "column"); //添加日志
        $this->success('保存成功');
    }

    // 栏目保存
    private function saveColumn($column, $pid = 0, $tier = '', $level = 1, $sort = 1, $lang = "")
    {

        $columns = ColumnModel::where(['name' => $column->name, 'pid' => $pid])->select();
        if (!(sizeof($columns) > 0)) { //栏目不存在就添加
            $coumnModel = new ColumnModel();
            $level = ($level + 1);
            $v_path = $this->getVPath($column->column_model);
            $sc = [
                'v_path' => $v_path,
                'name' => $column->name,
                'pid' => $pid,
                'level' => $level,
                'column_model' => $column->column_model,
                'sort' => $sort,
                "column_template" => $column->column_template,
                "model_template" => $column->model_template,
                'lang' => $lang,
                'status' => 1
            ];

            $en_name = PinyinUtil::utf8_to($sc["name"]); //栏目拼音
            $sc['dir_path'] = "/" . $en_name;
            $sc['en_name'] = $en_name;
            $coumnModel->save($sc);
            $id = $coumnModel->id;
            if (empty($tier)) {
                $tier .= $id;
            } else {
                $tier .= ',' . $id;
            }
            $coumnModel::update(['id' => $id, 'tier' => $tier, 'nid' => 's' . $id]);
            $colunmstr = $column->colunmstr; //子栏目
            if (!empty($colunmstr)) {
                $childColumns = explode(',', $colunmstr);
                foreach ($childColumns as $k => $columnStr) {
                    $scc = [
                        'v_path' => $v_path,
                        'name' => $columnStr,
                        'pid' => $id,
                        'level' => ($level + 1),
                        'column_model' => $column->column_model,
                        'sort' => $sort,
                        "column_template" => $column->column_template,
                        "model_template" => $column->model_template,
                        'lang' => $lang,
                        'status' => 1
                    ];

                    $en_name_cc = PinyinUtil::utf8_to($scc["name"]); //栏目拼音
                    $scc['dir_path'] = "/" . $en_name_cc;
                    $scc['en_name'] = $en_name_cc;
                    $columnCildM = new ColumnModel();
                    $columnCildM->save($scc);
                    $idd = $columnCildM->id;
                    $cTier = $tier;
                    if (empty($cTier)) {
                        $cTier .= $idd;
                    } else {
                        $cTier .= ',' . $idd;
                    }
                    $columnCildM::update(['id' => $idd, 'tier' => $cTier, 'nid' => 's' . $idd]);
                }
            }
        }
    }

    // 获取模型文件
    public function getModelHtml($model)
    {
        $isView = 0; //是否有文档模板 0：否； 1：是
        $column_template = "list_model.html"; //栏目模板
        $model_template = "view_model.html"; //文档模板
        $modelRecord = \app\common\model\ModelRecord::where("nid", $model)->find();
        if ($modelRecord) {
            if ($modelRecord["reference_model"] == 1) { //无
                $column_template = str_replace("model", $model, $column_template);
                $model_template = "";
            } else {
                $isView = 1;
                $column_template = str_replace("model", $model, $column_template);
                $model_template = str_replace("model", $model, $model_template);
            }
        }
        return $this->success('查询成功', null, ['column_template' => $column_template, 'model_template' => $model_template, 'is_view' => $isView]);
    }
}