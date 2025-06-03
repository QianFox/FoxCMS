<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   14:35
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   14:35
 */

namespace app\admin\controller;

use app\common\model\AuthRule;
use app\common\model\Column as ColumnModel;
use app\common\model\ColumnLevel;
use app\common\model\DictData as DictDataModel;

use app\common\controller\AdminBase;
use app\common\util\PinyinUtil;
use think\facade\Db;
use think\facade\View;

class Column extends AdminBase
{
    private $modelRecords;
    private $extraModlRecords = [['id' => 'formmodel', 'title' => '表单模型', 'reference_model' => 0]]; //额外增加的模型

    public function initialize()
    {
        parent::initialize();
        //缩略图
        $columnLevels = ColumnLevel::select();
        $columnLevel = ['level' => 3, 'is_thumb' => 1];
        if (sizeof($columnLevels) > 0) {
            $columnLevel = $columnLevels[0];
        }
        View::assign("columnLevel", $columnLevel);

        //模型
        $modelRecordTs = (new \app\common\model\ModelRecord())->field("nid as id, name as title, reference_model")->where(['status' => 1])->select()->toArray();
        $modelRecordTs = array_merge($modelRecordTs, $this->extraModlRecords);
        $this->modelRecords = $modelRecordTs;
        View::assign("modelRecords", $modelRecordTs);
        //会员模型
        $where = ['status' => 1];
        $mList = (new \app\common\model\MemberLevel())->field("id, name")->where($where)->order('create_time', 'desc')->select()->toArray();
        array_unshift($mList, ["id" => "0", "name" => "开放浏览"]);
        View::assign("mList", $mList); //模型数据

        $action = $this->request->action();
        if ($action == "columnSet") {
            //应用表单
            $formList = \app\common\model\FormList::field("id,name")->select();
            View::assign("formList", $formList);
        }
    }

    public function index()
    {
        $columnDatas  = ColumnModel::where([['lang', '=', $this->getMyLang()]])->order('level asc')->order('sort asc')->select();
        $columnList = $this->channelLevel($columnDatas);
        View::assign("columnList", $columnList);
        return view('index');
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
                $arr['nid'] = $v->nid;
                $arr['columnName'] = $v->name;
                $arr['isShow'] = $v->status;
                $arr['columnSort'] = $v->sort;
                $arr['model'] = $v->column_model;
                $arr['level'] = $level;
                $arr['pic_url'] = $v->pic_url;
                $arr['pic_ids'] = $v->pic_ids;
                $arr['children'] = self::channelLevel($dataArr, $v[$fieldPri], $fieldPri, $fieldPid, $level + 1);

                foreach ($this->modelRecords as $k => $vo) {
                    if ($vo['id'] == $v->column_model) {
                        $arr['modelTitle'] = $vo['title'];
                        break;
                    }
                }
                array_push($rArr, $arr);
            }
        }
        return $rArr;
    }

    // 查询栏目模型
    public function getColumModels()
    {
        $dictDatas = (new \app\common\model\ModelRecord())->field("nid as id, name as title")->where(['status' => 1])->select()->toArray();
        $dictDatas = array_merge($dictDatas, $this->extraModlRecords);
        $this->success('查询成功', '', $dictDatas);
    }

    public function deleteColumn()
    {
        $param = $this->request->param();
        if (empty($param['id'])) {
            $this->error('参数不能为空');
        }
        $id = intval($param['id']);
        $columnList =  ColumnModel::where(['pid' => $id])->select();
        if (sizeof($columnList) > 0) {
            $this->error('删除失败,有子栏目');
        }
        $fcolumn = ColumnModel::field("nid,name")->find($id);
        if (!$fcolumn) {
            $this->error("操作失败,没找到对应栏目");
        }
        $r = ColumnModel::where('nid', $fcolumn['nid'])->delete();
        if ($r) {
            xn_add_admin_log("删除栏目“{$fcolumn['name']}”", "column"); //添加日志
            $this->success('删除成功');
        }
        $this->error("操作失败");
    }

    public function save()
    {
        $param = $this->request->post();
        if (!array_key_exists('columns', $param)) {
            $this->error('保存失败参数不存在');
        }
        $columns = json_decode($param['columns']);
        $clang = $this->getMyLang();
        if (sizeof($columns) > 0) {
            try {
                $index = 1;
                foreach ($columns as $key => $column) {
                    $this->saveColumn($column, 0, 1, '', $index, $clang);
                    $index++;
                }
            } catch (\Exception $e) {
                $this->error('保存栏目失败,' . $e->getMessage());
            }
        } else {
            $this->error('保存失败栏目数据不能为空');
        }
        xn_add_admin_log("添加栏目", "column"); //添加日志
        //多语言复制
        try {
            $langList = Db::name('lang')->field("lang,name")->where([['status', '=', 1], ['lang', '<>', $clang]])->select()->toArray();
            foreach ($langList as $key => $item) {
                $this->copyColumns($clang, $item['lang']);
            }
        } catch (\Exception $e) {
        }
        $this->success('保存成功');
    }

    private function saveColumn($column, $pid = 0, $level = 1, $tier = '', $sort = 1, $lang = "")
    {
        $coumnModel = new ColumnModel();
        $sc = [
            'name' => $column->name,
            'pic_ids' => $column->pic_ids,
            'pid' => $pid,
            'level' => $level,
            'column_model' => $column->column_model,
            'status' => $column->status,
            'sort' => $sort,
            'lang' => $lang
        ];
        $id = $column->id;
        $updateColumn = [];
        if (!empty($column->id)) {
            $sc['id'] = $column->id;
            $oldColumnModel = ColumnModel::find($column->id);
            if ($oldColumnModel && $oldColumnModel['column_model'] != $column->column_model) {
                $v_path = $this->getVPath($column->column_model);
                $sc['v_path'] = $v_path;
            }
            $sc['column_template'] = $oldColumnModel['column_template'];
            $sc['model_template'] = $oldColumnModel['model_template'];
            $coumnModel->update($sc);
        } else {
            $v_path = $this->getVPath($sc["column_model"]);
            $en_name = PinyinUtil::utf8_to($sc["name"]); //栏目拼音
            $sc['dir_path'] = "/" . $en_name;
            $sc['en_name'] = $en_name;
            $sc['v_path'] = $v_path;
            $coumnModel->save($sc);
            $id = $coumnModel->id;
            $updateColumn['nid'] = "s" . $id;
        }
        if (empty($tier)) {
            $tier .= $id;
        } else {
            $tier .= ',' . $id;
        }
        $updateColumn['id'] = $id;
        $updateColumn['tier'] = $tier;
        $coumnModel::update($updateColumn);
        if (sizeof($column->colunms) > 0) {
            $index = 1;
            foreach ($column->colunms as $key => $col) {
                $this->saveColumn($col, $id, ($level + 1), $tier, $index, $lang);
                $index++;
            }
        }
    }

    // 添加编辑图片分组
    public function addOrEdit()
    {
        $param = $this->request->param();
        if (!empty($param['dictValue'])) {
            $dictDataModel = DictDataModel::where(['dict_value' => $param['dictValue'], 'dict_type' => 'fox_pic_group_type'])->find();
            $dictDataModel->dict_label = $param['dictLabel'];
            $dictDataModel->save();
            $this->success('更新成功');
        } else {
            //先查分组
            $dict_value = DictDataModel::field('dict_value')->where(['dict_type' => 'fox_pic_group_type'])->max('dict_value');
            $saveData = ['dict_label' => $param['dictLabel']];
            $saveData['dict_value'] = intval($dict_value) + 1;
            $saveData['dict_sort'] = intval($dict_value) + 2;
            $saveData['dict_type'] = 'fox_pic_group_type';
            $saveData['remark'] = '图片添加分组';
            $saveData['type'] = 'custom';
            $dictDataModel = new DictDataModel();
            $dictDataModel->save($saveData);
            $this->success('保存成功');
        }
    }

    public function delete()
    {
        $param = $this->request->param();
        if (empty($param['dictValue'])) {
            $this->error('参数不能为空');
        }
        $dictValue = intval($this->request->get('dictValue'));
        $isD = DictDataModel::where(['dict_value' => $dictValue, 'dict_type' => 'fox_pic_group_type'])->delete();
        if (!$isD) {
            $this->error('删除失败');
        }
        xn_add_admin_log("删除栏目", "column"); //添加日志
        $this->success('删除成功');
    }

    // 栏目设置
    public function columnSet()
    {
        $param = $this->request->param();
        //查询
        $lang = xn_cfg("base.lang");

        $bcid = $param['bcid'];
        View::assign('bcid', $bcid);
        $ids = explode('_', $bcid);
        $authRuleId = $ids[sizeof($ids) - 1]; //栏目id
        $authRule = AuthRule::find($authRuleId);
        $bcidStr = str_replace(",", "_", $authRule->tier);
        $breadcrumb = AuthRule::getBreadcrumb($bcidStr);
        array_push($breadcrumb, ['id' => '', 'title' => '栏目设置', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/column/columnSet', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);

        if (empty($param['id'])) {
            $this->error('栏目id参数不能为空');
        }
        $id = $param['id'];
        View::assign('id', $id);
        if ($this->request->isAjax()) {
            $oldColumnModel = ColumnModel::find($param["id"]);
            //            if($oldColumnModel['nid'] != $param['nid']){
            //                //判断当前语言下标识是否存在
            //                $fList = ColumnModel::where([['lang','=', $lang],['nid','=',$param['nid']]])->select();
            //                if(sizeof($fList) > 0){
            //                    $this->error("当前语言下标识已存在");
            //                }
            //            }
            if (!empty($param['dir_path'])) {
                $param['dir_path'] =  add_slash($param['dir_path']);
            } else {
                $en_name = implode("", PinyinUtil::utf8_to($param["name"])); //栏目拼音
                $param['dir_path'] = add_slash($en_name);
            }
            if ($oldColumnModel["column_model"] != $param["column_model"]) {
                $v_path = $this->getVPath($param["column_model"]);
                $param['v_path'] = $v_path;
            }

            $ret = ColumnModel::update($param);
            if ($ret) {
                //修改其子栏目的模型
                $inherit_option = intval($param['inherit_option']);
                //批量修改继承选项栏目
                try {
                    if ($inherit_option == 1) { //继承选项栏目模板风格 0:不继承；1:继承
                        $sql = "update fox_column set column_template = '{$param['column_template']}',model_template = '{$param['model_template']}' where FIND_IN_SET({$param['id']}, tier)>0 and id != {$param['id']} and column_model = '{$param['column_model']}'";
                        Db::execute($sql);
                    }
                } catch (\Exception $e) {
                    $e->getMessage();
                }
                $this->success('操作成功');
            } else {
                $this->error("操作失败");
            }
        }
        $column = ColumnModel::find($id);
        $iew_suffix = config('view.view_suffix');

        if (empty($column->column_template)) { //栏目模型文件名
            $mr = \app\common\model\ModelRecord::where('nid', $column->column_model)->find();
            if ($mr && $mr['reference_model'] == 0) {
                $column["column_template"] = "list_" . $column->column_model . ".{$iew_suffix}";
                $column["model_template"] = "view_" . $column->column_model . ".{$iew_suffix}";
            } else {
                $column["column_template"] = "index_" . $column->column_model . ".{$iew_suffix}";
            }
        }
        //模型
        $modelTitle = '';
        foreach ($this->modelRecords as $k => $vo) {
            if ($vo['id'] == $column->column_model) {
                $modelTitle = $vo['title'];
                break;
            }
        }
        View::assign('model', '/home/');
        //查询所有栏目
        $columnList = \app\common\model\Column::where([['level', '<=', 3], ['lang', '=', $this->getMyLang()]])->order('level asc')->select();
        $columns = [['name' => '顶层栏目', 'id' => 0, "pid" => 0]];
        foreach ($columnList as $k => $cm) {
            array_push($columns, ["name" => $cm["name"], "id" => $cm["id"]]);
            if (empty($column['innerColumnText']) && !empty($column['inner_column']) && ($column['inner_column'] == $cm["id"])) {
                $column['innerColumnText'] = $cm["name"];
            }
        }
        $columnListC = $this->channelLevel($columnList);
        View::assign('columnListC', $columnListC);

        $column["modelTitle"] = $modelTitle; //模型
        //是否显示栏目数据显示
        $columnCount = \app\common\model\Column::where("pid", $id)->count();
        View::assign('columnCount', $columnCount);
        if ($columnCount > 0) { //查询本栏目及指定子栏目
            $rColumnList = get_column_down($id, $column["column_model"]);
            $limit_column = $column["limit_column"];
            $limitColumnTextArr = []; //限制栏目
            if (!empty($limit_column)) {
                $limitColumnIdArr = explode(",", $limit_column);
                foreach ($limitColumnIdArr as $limitColumnId) {
                    foreach ($rColumnList as $rColumn) {
                        if ($limitColumnId == $rColumn["id"]) {
                            array_push($limitColumnTextArr, $rColumn['name']);
                            $rColumn["is_active"] = "is-active";
                            break;
                        }
                    }
                }
                View::assign('limitColumnIdArr', $limitColumnIdArr);
            }
            View::assign('rColumnList', $rColumnList);

            View::assign('limitColumnText', implode(",", $limitColumnTextArr));
        }
        //特殊模型
        if (!empty($column['form_list_id'])) { //应用表单id
            $formL = \app\common\model\FormList::field("name")->find($column['form_list_id']);
            if ($formL) {
                $column['formTitle'] = $formL['name'];
            }
        }
        //表单地址
        $apply = \app\common\model\Apply::where(['status' => 1, 'name' => '自定义表单'])->find();
        $formUrl = url("Apply/index") . "?columnId=42&type=1";
        if ($apply) {
            if (!empty($apply["path"])) {
                $path = $apply['mark'] . $apply["path"];
                $url = url($path) . "?columnId=" . $apply['column_id'];
                if (!empty($apply['auth_rule_ids'])) {
                    $url = $url . "&ruleIds=" . $apply['auth_rule_ids'];
                }
                $formUrl = $url . "&type=1";
            }
        }
        $column['formUrl'] = $formUrl;

        $ocList = \app\common\model\Column::where([['lang', '<>', $lang], ['column_model', '=', $column['column_model']]])->order('level asc')->order('sort asc')->select();
        $ocList = $this->channelLevel($ocList);
        View::assign('ocList', $ocList);

        return view('column_set', ['column' => $column, 'columns' => $columns, "activepath" => $this->relativeTemplateHtml]);
    }

    // 选择模型
    public function temp()
    {

        $activepath = $this->request->param('activepath') ?? $this->relativeTemplateHtml;
        $activepath = replaceSymbol($activepath);
        if (str_ends_with($activepath, "/")) {
            $activepath = substr($activepath, 0, -1);
        }
        $basePath = root_path() . DIRECTORY_SEPARATOR . 'templates' . $activepath;
        $basePath = replaceSymbol($basePath);
        $fArr = array();
        $arr_file = getDirFile($basePath, $activepath, $fArr, $this->template['template']);
        $this->success('查询成功==' . $activepath, null, $arr_file);
    }

    // 查询所有栏目
    public function getColumns()
    {
        $param = $this->request->param();
        $where = [["lang", "=", $this->getMyLang()]];
        if (array_key_exists("columnModel", $param) && !empty($param['columnModel'])) {
            array_push($where, ['column_model', '=', $param['columnModel']]);
        }
        $columnDatas  = ColumnModel::where($where)->field('id,name as title')->order('level asc')->order('sort asc')->select();
        $this->success("查询成功", null, $columnDatas);
    }

    public function look($columnId, $lookNum)
    {
        $inde_url = resetIndexUrl("/index", $this->getMyLang());
        $url = $inde_url;
        $columnModel = new ColumnModel();
        $columnO = $columnModel->find($columnId);
        if (!$columnO) {
            $this->redirect($url);
            exit();
        }
        if ($lookNum == 1) { //内容
            $model = $columnO["column_model"];
            $bcid = str_replace(",", "_", $columnO["tier"]);
            if ($model == 'single') {
                $model = 'single_c';
            }
            $url = url(DIRECTORY_SEPARATOR . config("adminconfig.admin_path") . DIRECTORY_SEPARATOR . "$model", ["columnId" => $columnId, "bcid" => $bcid]);
            $this->redirect($url);
            exit();
        } elseif ($lookNum == 2) { //浏览
            if ($columnO["column_attr"] == 0) {
                $url =  resetUrl($columnO['v_path'], $columnO["id"], $this->getMyLang());
            } elseif ($columnO["column_attr"] == 1) { //外部链接
                $url = $columnO['out_link_head'] . $columnO['out_link'];
            } elseif ($columnO["column_attr"] == 2) { //内链栏目
                $inner_column = $columnO['inner_column'];
                if (!empty($columnO['inner_column'])) {
                    $columnInner = \app\common\model\Column::find($inner_column);
                    if ($columnInner) {
                        $vPath = $columnInner["v_path"];
                        $url = resetUrl($vPath, $inner_column);
                    } else {
                        $url = $inde_url;
                    }
                } else {
                    $url = $inde_url;
                }
            }
            $url = replaceSymbol($url);
            $this->redirect($url);
            exit();
        }
    }

    // 查询栏目是否有数据
    public function checkData()
    {
        $param = $this->request->param();
        if (!(array_key_exists('column_id', $param))) {
            $this->error("参数不存在");
        }
        $column = \app\common\model\Column::field('column_model,nid')->find($param['column_id']);
        if (!$column) {
            $this->error("没查到栏目数据");
        }
        $columnList = \app\common\model\Column::field("column_model as old_model, id as column_id")->where(['nid' => $column['nid']])->select()->toArray();
        $rlist = [];
        foreach ($columnList as $key => $item) {
            $item['data_rm'] = $param['data_rm'];
            $item['model'] = $param['model'];
            $rlist[] = $item;
        }
        $this->success("查询成功", "", $rlist);
    }

    // 切换类似文章模型
    public function modelSwitch()
    {
        $param = $this->request->param();
        if (
            empty($param['model'])
            || empty($param['column_id'])
            || empty($param['old_model'])
            || ($param['data_rm'] != 0 && empty($param['data_rm']))
        ) {
            $this->error("参数为空");
        }
        if ($param['data_rm'] == 0 && $param['model'] != "formmodel") {
            $column = \app\common\model\Column::field('column_model')->find($param['column_id']);
            if (!$column) {
                $this->error("没查到栏目数据");
            }
            $findFieldArr = array_unique(xn_cfg("list")); //表字段
            $fieldArr = [];
            foreach ($findFieldArr as $field) {
                $field = str_replace("`", "", $field);
                if ($field != "id") {
                    array_push($fieldArr, $field);
                }
            }
            //查询以前模型数据
            $oldModelDataList = Db::name($column['column_model'])->where("column_id", $param['column_id'])->select();
            $saveList = [];
            foreach ($oldModelDataList as $omd) {
                $save = [];
                foreach ($fieldArr as $field) {
                    $save[$field] = $omd[$field];
                }
                array_push($saveList, $save);
            }

            if (sizeof($saveList) > 0) {
                $r = Db::name($param['model'])->insertAll($saveList);
                if ($r) {
                    Db::name($column['column_model'])->where("column_id", $param['column_id'])->delete(); //删除之前模型数据
                } else {
                    $this->error("操作失败");
                }
            }
        }
        $column_model = $param['model'];
        $v_path = $this->getVPath($column_model);
        $r =  \app\common\model\Column::update(['id' => $param['column_id'], 'column_model' => $column_model, 'v_path' => $v_path]);
        if (!$r) {
            $this->error("操作失败");
        }
        $this->success("操作成功");
    }

    // 复制语言栏目
    public function copyLangColumn()
    {
        $lang = $this->request->param("lang", "");
        if (empty($lang)) {
            $this->error("缺少参数，操作失败");
        }
        $curLang = $this->getMyLang(); //当前语言
        if ($lang == $curLang) {
            $this->error("同语言不允许复制哟");
        }
        try {
            $this->copyColumns($lang, $curLang);
        } catch (\Exception $e) {
            $this->error('复制栏目失败,' . $e->getMessage());
        }

        $this->success("操作成功");
    }

    //复制语言多栏目
    private function copyColumns($lang, $curLang)
    {
        $fieldArr = \think\facade\Db::name("column")->getFields(); //表属性
        $copyFields = [];
        $filterFields = ["id", "pid", "tier", "limit_column", "inner_column", "lang"];
        foreach ($fieldArr as $key => $field) {
            if (!in_array($key, $filterFields)) {
                $copyFields[] = $key;
            }
        }
        $columnDatas  = ColumnModel::where([['lang', '=', $lang]])->order('level asc')->order('sort asc')->select()->toArray();
        $columnList = $this->columnLevel($columnDatas, $copyFields);
        $index = 1;
        foreach ($columnList as $key => $column) {
            $this->copyColumn($column, 0, 1, '', $index, $curLang);
            $index++;
        }
    }

    //返回多栏目
    private function columnLevel($dataArr, $copyFields = [], $pid = 0, $fieldPri = 'id', $fieldPid = 'pid', $level = 1)
    {
        if (empty($dataArr)) {
            return array();
        }
        $rArr = [];
        foreach ($dataArr as $v) {
            if ($v[$fieldPid] == $pid) {
                $arr = array();
                foreach ($copyFields as $key => $field) {
                    $arr[$field] = $v[$field];
                }
                $arr['children'] = self::columnLevel($dataArr, $copyFields, $v[$fieldPri], $fieldPri, $fieldPid, $level + 1);
                array_push($rArr, $arr);
            }
        }
        return $rArr;
    }

    //复制栏目
    private function copyColumn($column, $pid = 0, $level = 1, $tier = '', $sort = 1, $lang = "")
    {
        $column['sort'] = $sort;
        $columnModel = new ColumnModel();
        $fColumn = $columnModel->where(['lang' => $lang, 'nid' => $column['nid']])->find();
        if ($fColumn) {
            $id = $fColumn['id'];
            $tier = $fColumn['tier'];
        } else {
            $column['lang'] = $lang;
            $column['pid'] = $pid;
            $columnModel->save($column);
            $id = $columnModel->id;
            if (empty($tier)) {
                $tier .= $id;
            } else {
                $tier .= ',' . $id;
            }
        }
        $columnModel::update(['id' => $id, 'tier' => $tier]);
        if (sizeof($column['children']) > 0) {
            $index = 1;
            foreach ($column['children'] as $key => $col) {
                $this->copyColumn($col, $id, ($level + 1), $tier, $index, $lang);
                $index++;
            }
        }
    }
}