<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   15:32
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   15:32
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\model\AuthRule;
use app\common\model\ColumnLevel;
use app\common\model\ModelRecord;
use \app\common\model\Tag as TagModel;
use think\facade\Db;
use think\facade\View;

class Tag extends AdminBase
{
    public function initialize()
    {
        parent::initialize();
        $action = request()->action();
        if ($action == "add" || $action == "edit") {
            $tagGroupList = \app\common\model\TagGroup::where(['status' => 1])->order(['sort' => 'desc'])->select();
            View::assign("tagGroupList", $tagGroupList);
        }
    }

    public function index($page = 1, $pageSize = 100)
    {
        $param = $this->request->param();

        if ($this->request->isAjax()) {
            $where = array();
            if (array_key_exists('keyword', $param) && !empty($param['keyword'])) {
                if ($param['keyword'] == '否') {
                    array_push($where, ['is_used', '=', 0]);
                } else if ($param['keyword'] == '是') {
                    array_push($where, ['is_used', '=', 1]);
                } else {
                    array_push($where, ['name', 'like', '%' . $param['keyword'] . '%']);
                }
            }
            //查询所有模型 生成
            $dataSql = $this->getArticleSql();
            $sql = "({$dataSql})";
            $list = (new TagModel())->where($where)->paginate(['page' => $page, 'list_rows' => $pageSize])->each(function ($item, $key) use ($sql) {
                $count = Db::table($sql . " as a")->whereFindInSet("tags", $item['name'])->count();
                $item['document_num'] = $count;
                return $item;
            });

            $this->success('查询成功', '', $list);
        }
        return view('index');
    }

    public function updateStatus()
    {
        $param = $this->request->param();
        $idList = json_decode($param['idList']);
        if (sizeof($idList) <= 0) {
            $this->error("操作失败，请选择对应启用数据");
        }
        $is_used = intval($param['is_used']);
        $tagModel = new TagModel();
        try {
            $tagModel->whereIn("id", implode(",", $idList))->update(["is_used" => $is_used]);
        } catch (\Exception $e) {
            $this->error('操作失败,' . $e->getMessage());
        }
        $this->success('操作成功');
    }

    public function add()
    {
        $columnId = $this->request->param("columnId");
        $authRule = AuthRule::find($columnId);
        $bcidStr = str_replace(",", "_", $authRule->tier);
        $breadcrumb = AuthRule::getBreadcrumb($bcidStr);
        array_push($breadcrumb, ['id' => '', 'title' => '批量新增', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/Tag/add', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);

        if ($this->request->isPost()) {
            $param = $this->request->param();
            $tags = $param['tags'];
            if (empty($tags)) {
                $this->error("Tag标签列表为空");
            }

            $findTags = TagModel::select();
            $is_used = $param['is_used'];
            $tagList = explode("\n", $tags);
            $tagArr = [];
            foreach ($tagList as $tag) {
                $isExist = false;
                foreach ($findTags as $findTag) {
                    if ($findTag['name'] == $tag) {
                        $isExist = true;
                        break;
                    }
                }
                if (!$isExist && !empty($tag)) {
                    array_push($tagArr, ["name" => $tag, "is_used" => $is_used, 'tag_group_id' => $param['tag_group_id']]);
                }
            }
            (new TagModel())->saveAll($tagArr);
            $this->success("操作成功", "", $tagList);
        }
        return view('add');
    }

    public function edit()
    {
        $columnId = $this->request->param("columnId");
        $authRule = AuthRule::find($columnId);
        $bcidStr = str_replace(",", "_", $authRule->tier);
        $breadcrumb = AuthRule::getBreadcrumb($bcidStr);
        array_push($breadcrumb, ['id' => '', 'title' => '编辑', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/Tag/edit', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);

        if ($this->request->isPost()) {
            $param = $this->request->param();
            TagModel::update($param);
            $this->success("操作成功");
        }
        $id = $this->request->get('id');
        $tag = TagModel::find($id);
        $taggroupname = "";
        if ($tag) {
            $tagGroup = \app\common\model\TagGroup::find($tag['tag_group_id']);
            $taggroupname = $tagGroup['name'];
        }
        $tag['taggroupname'] = $taggroupname;
        return view('edit', ['tag' => $tag]);
    }

    public function delete()
    {
        $id = intval($this->request->get('id'));
        !($id > 0) && $this->error('参数错误');
        $tag = \app\common\model\Tag::find($id);
        if (!$tag) {
            $this->error("操作失败");
        }

        //查询所有模型 生成
        $dataSql = $this->getArticleSql();
        $sql = "({$dataSql})";
        $where = array();
        $dataList = Db::table($sql . " as a")->where($where)->whereFindInSet("tags", $tag['name'])->select();
        if (sizeof($dataList) > 0) {
            foreach ($dataList as $data) {
                $tagsArr = explode(",", $data["tags"]);
                foreach ($tagsArr as $key => $ta) {
                    if ($ta == $tag["name"]) {
                        unset($tagsArr[$key]);
                        break;
                    }
                }
                try {
                    Db::name($data['model'])->update(['id' => $data['id'], "tags" => implode(",", $tagsArr)]);
                } catch (\Exception $e) {
                }
            }
        }
        TagModel::destroy($id);
        $this->success('删除成功');
    }

    public function deletes()
    {
        $param = $this->request->param();
        if (array_key_exists("idList", $param)) {
            $idList = json_decode($param['idList']);
            $count = 0;
            $tagModel = new TagModel();
            $tagModel->startTrans();

            //查询所有模型 生成
            $dataSql = $this->getArticleSql();
            $sql = "({$dataSql})";
            foreach ($idList as $key => $id) {
                try {
                    $tag = \app\common\model\Tag::find($id);
                    $dataList = Db::table($sql . " as a")->where([])->whereFindInSet("tags", $tag['name'])->select();
                    if (sizeof($dataList) > 0) {
                        foreach ($dataList as $data) {
                            $tagsArr = explode(",", $data["tags"]);
                            foreach ($tagsArr as $key => $ta) {
                                if ($ta == $tag["name"]) {
                                    unset($tagsArr[$key]);
                                    break;
                                }
                            }
                            Db::name($data['model'])->update(['id' => $data['id'], 'tags' => implode(",", $tagsArr)]);
                        }
                    }
                } catch (\Exception $e) {
                }

                $r = $tagModel->destroy($id);
                if ($r) {
                    $count++;
                }
            }
            if (sizeof($idList) == $count) {
                $tagModel->commit();
                $this->success('操作成功');
            } else {
                $tagModel->rollback();
                $this->error('操作失败');
            }
        }
    }

    // 关联
    public function associate()
    {
        $id = $this->request->param("id"); //标签id
        $tag = \app\common\model\Tag::find($id);
        View::assign("id", $id);

        $columnId = $this->request->param("columnId");
        $authRule = AuthRule::find($columnId);
        $bcidStr = str_replace(",", "_", $authRule->tier);
        $breadcrumb = AuthRule::getBreadcrumb($bcidStr);
        array_push($breadcrumb, ['id' => '', 'title' => '关联', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/Tag/associate', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);

        if ($this->request->isAjax()) {
            $param = $this->request->param();
            $where = array();
            if (array_key_exists('keyword', $param) && !empty($param['keyword'])) {
                array_push($where, ['title', 'like', '%' . $param['keyword'] . '%']);
            }
            if (array_key_exists('column_id', $param) && !empty($param['column_id']) && $param['column_id'] != -1) {
                array_push($where, ['column_id', '=', $param['column_id']]);
            }
            if (empty($param['currentPage'])) {
                $param['currentPage'] = 1;
            }
            if (empty($param['pageSize'])) { //全查
                $param['pageSize'] = 10;
            }

            //查询所有模型 生成
            $dataSql = $this->getArticleSql();
            $sql = "({$dataSql})";
            $dataList = Db::table($sql . " as a")->where($where)->paginate(['page' => $param['currentPage'], 'list_rows' => $param['pageSize']])->each(function ($item) use ($tag) {
                $isCheck = "";
                if (!empty($item['tags'])) {
                    $tagsArr = explode(",", $item['tags']);
                    if (in_array($tag['name'], $tagsArr)) {
                        $isCheck = "is-checked";
                    }
                }
                $item['isCheck'] = $isCheck;
                return $item;
            });
            $this->success('查询成功', '', $dataList);
        }

        //栏目列表
        $columnLevels = ColumnLevel::select();
        $level = 3;
        if (sizeof($columnLevels) > 0) {
            $level = $columnLevels[0]['level'];
        }
        $where = [];
        array_push($where, ['status', '=', 1]);
        $nids = [];
        //查询所有模型 生成
        $mrList = ModelRecord::field("table, nid, name")->where(['is_delete' => 0, 'status' => 1, "reference_model" => 0])->select();
        foreach ($mrList as $mr) {
            if (!empty($mr["nid"])) {
                array_push($nids, $mr['nid']);
            }
        }
        if (sizeof($nids) > 0) {
            array_push($where, ['column_model', 'in', implode(",", $nids)]);
        }
        $columns = \app\common\model\Column::where($where)->where('level', '<=', $level)->order('sort ASC,id DESC')->select();
        $columnList = $this->channelLevel($columns);
        array_unshift($columnList, ["columnName" => "所有栏目", "columnId" => -1]);
        View::assign("columnList", $columnList);
        return view();
    }

    // 关联数据
    public function associateData()
    {
        $param = $this->request->param();
        if (empty($param['id'])) {
            $this->error("缺少关联参数");
        }
        $id = $param["id"]; //标签id
        $tag = \app\common\model\Tag::find($id);
        $where = array();
        array_push($where, ['tags', 'like', '%' . $tag['name'] . '%']);
        //查询所有模型 生成
        $dataSql = $this->getArticleSql();
        $sql = "({$dataSql})";
        $dataList = Db::table($sql . " as a")->where($where)->select();
        $this->success('查询成功', '', $dataList);
    }

    // 取消关联
    public function cancelAssociate()
    {
        $param = $this->request->param();
        if (empty($param['id']) || empty($param['model']) || empty($param['tagId'])) {
            $this->error("缺少关联参数");
        }
        $model = $param['model'];
        $id = $param['id'];
        $tagId = $param['tagId'];
        $aData = Db::name($model)->find($id); //关联数据
        if (!$aData) {
            $this->error("没有找到关联数据");
        }
        $tag = \app\common\model\Tag::find($tagId);
        $tagName = $tag['name'];
        $tagArr = explode(",", $aData['tags']);
        $tagC = [];
        foreach ($tagArr as $val) {
            if ($tagName != $val) {
                array_push($tagC, $val);
            }
        }
        if (sizeof($tagC) > 0) {
            $tags = implode(",", $tagC);
        } else {
            $tags = "";
        }
        $r = Db::name($model)->update(['id' => $id, 'tags' => $tags]);
        if ($r <= 0) {
            $this->error("操作失败");
        }
        $this->success("操作成功");
    }

    // 批量取消
    public function cancelAssociates()
    {
        $param = $this->request->param();
        if (empty($param['associateList']) || (key_exists("associateList", $param) && sizeof($param['associateList']) <= 0)) {
            $this->error("没有操作数据");
        }
        $associateList = json_decode($param['associateList']);
        foreach ($associateList as $associate) {
            $associate = get_object_vars($associate);
            if (empty($associate['id']) || empty($associate['model']) || empty($associate['tagId'])) {
                continue;
            }
            $model = $associate['model'];
            $id = $associate['id'];
            $tagId = $associate['tagId'];
            $aData = Db::name($model)->find($id); //关联数据
            if (!$aData) {
                continue;
            }
            $tag = \app\common\model\Tag::find($tagId);
            $tagName = $tag['name'];
            $tagArr = explode(",", $aData['tags']);
            $tagC = [];
            foreach ($tagArr as $val) {
                if ($tagName != $val) {
                    array_push($tagC, $val);
                }
            }
            if (sizeof($tagC) > 0) {
                $tags = implode(",", $tagC);
            } else {
                $tags = "";
            }
            Db::name($model)->update(['id' => $id, 'tags' => $tags]);
        }
        $this->success("操作成功");
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
                $arr['columnSort'] = $v->sort;
                $arr['level'] = $level;
                $arr['children'] = self::channelLevel($dataArr, $v[$fieldPri], $fieldPri, $fieldPid, $level + 1);
                foreach ($this->modelRecords as $k => $vo) {
                    if ($vo->id == $v->column_model) {
                        $arr['modelTitle'] = $vo->title;
                        break;
                    }
                }
                array_push($rArr, $arr);
            }
        }
        return $rArr;
    }

    // 确认关联
    public function confirmAssociate()
    {

        $param = $this->request->param();
        $list = json_decode($param['list']);
        if (sizeof($list) <= 0) {
            $this->error("操作失败，请选择对应启用数据");
        }
        $id = $param['id'];
        $tag = \app\common\model\Tag::find($id);
        if (!$tag) {
            $this->error("操作失败");
        }
        foreach ($list as $data) {
            Db::name($data->model)->update(["id" => $data->id, "tags" => $tag['name']]);
        }
        $this->success("操作成功", "", $param);
    }

    // 获取所有文章的sql
    private function getArticleSql()
    {
        //查询所有模型 生成
        $mrList = ModelRecord::field("table, nid, name")->where(['is_delete' => 0, 'status' => 1, "reference_model" => 0])->select();
        $mmfArr = xn_cfg("list");
        $commonFiled = implode(",", $mmfArr);
        $sqlArr = [];
        foreach ($mrList as $key => $mmf) {
            if ($key > 0) {
                $commonFiledSMore = $commonFiled . ',' . "'{$mmf['nid']}' as model" . ',' . "'{$mmf['name']}' as model_name";
                array_push($sqlArr, "SELECT {$commonFiledSMore} FROM {$mmf['table']}");
            }
        }
        $commonFiledFirst = $commonFiled . ',' . "'{$mrList[0]['nid']}' as model" . ',' . "'{$mrList[0]['name']}' as model_name";
        $tableName = $mrList[0]['table'];
        $dataSql = Db::table($tableName)->field($commonFiledFirst)->union($sqlArr)->buildSql();
        return $dataSql;
    }
}