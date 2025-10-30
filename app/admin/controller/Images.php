<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   14:54
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   14:54
 */

namespace app\admin\controller;

use app\common\controller\AdminContentBase;
use app\common\model\Column;
use app\common\model\DictType as DictTypeModel;
use think\facade\Db;
use think\facade\View;

class Images extends AdminContentBase
{
    public function initialize()
    {
        parent::initialize();

        //文章来源 开始
        $articleSourceList = DictTypeModel::where('dict_type', 'article_source')->with('dictDatas')->find()->dictDatas; //文章来源
        $articleSources = [];
        $articleSourceDictCode = 0;
        if (sizeof($articleSourceList) == 1) {
            $articleSource = $articleSourceList[0];
            $dict_value = $articleSource['dict_value'];
            $articleSources = explode(',', $dict_value);
            $articleSourceDictCode = $articleSource['dict_code'];
        }
        View::assign('articleSources', $articleSources);
        View::assign('articleSourceDictCode', $articleSourceDictCode);
        //结束
        //作者 开始
        $authorList = DictTypeModel::where('dict_type', 'author')->with('dictDatas')->find()->dictDatas; //作者
        $authors = [];
        $authorDictCode = 0;
        if (sizeof($authorList) == 1) {
            $author = $authorList[0];
            $dict_value = $author['dict_value'];
            $authors = explode(',', $dict_value);
            $authorDictCode = $author['dict_code'];
        }
        View::assign('authors', $authors);
        View::assign('authorDictCode', $authorDictCode);

        //查询栏目
        $columnDatas  = Column::where(["column_model" => "images"])->field('id,name as title')->order('level asc')->order('sort asc')->select(); //栏目
        View::assign('columns', $columnDatas);

        //查询文档标签
        $tagList = \app\common\model\Tag::select();
        View::assign('tagList', $tagList);
    }

    public function index()
    {
        $param = $this->request->param();
        View::assign('bcid', $param['bcid']);
        $columnId = $param['columnId'];
        if ($this->request->isAjax()) {
            $where = array();

            $images = new \app\common\model\Images();
            if (empty($param["currentPage"])) {
                $param["currentPage"] = 1;
            }
            if (empty($param["pageSize"])) {
                $param["pageSize"] = 10;
            }
            if (!empty($param["keyword"]) && !empty($param["prop"])) {
                $prop = $param["prop"];
                if ($prop == 1) { //属性
                    $articleField = $this->getArticleField($param['keyword']);
                    array_push($where, ['article_field', 'like', '%' . $articleField . '%']);
                } elseif ($prop == 2) { //栏目
                    array_push($where, ['column', 'like', '%' . $param['keyword'] . '%']);
                } elseif ($prop == 3) { //标题
                    array_push($where, ['title', 'like', '%' . $param['keyword'] . '%']);
                }
            }

            //向下查询所有子孙栏目
            $columnIdArr = getChildrensColumnId($columnId, 'images', $this->getMyLang());
            if (sizeof($columnIdArr) > 0) {
                array_push($where, ['column_id', 'in', implode(",", $columnIdArr)]);
            } else {
                array_push($where, ['column_id', '=', $columnId]);
            }
            array_push($where, ['lang', '=', $this->getMyLang()]);

            $list = $images->where($where)->order("create_time", "desc")->paginate(['page' => $param['currentPage'], 'list_rows' => $param['pageSize']]);

            //            $list = $images->where($where)->order("create_time", "desc")->buildSql();
            $this->success('查询成功', null, $list);
        }

        View::assign('columnId', $columnId);
        return view();
    }

    public function add()
    {
        $param = $this->request->param();

        $bcid = $param['bcid'];
        View::assign('bcid', $bcid);
        $ids = explode('_', $bcid);
        $columnId = $ids[sizeof($ids) - 1]; //栏目id
        $column = Column::find($columnId);
        if (!empty($column->tier)) {
            $bcidStr = '4_' . str_replace(",", "_", $column->tier);
        } else {
            $bcidStr = '4';
        }
        $breadcrumb = Column::getBreadcrumb($bcidStr);
        array_push($breadcrumb, ['id' => '', 'title' => '添加图片集', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/Images/add', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);
        if ($this->request->isAjax()) {
            if (array_key_exists("content", $param)) {
                $rdata = $this->replaceContent($param, 'content'); //替换内容
                if ($rdata["code"] == 0) {
                    $this->error($rdata['msg']);
                }
                $param['content'] = $rdata['content']; //替换内容
            }
            if (empty($param["release_time"])) {
                $param["release_time"] = date("Y-m-d H:i:s");
            }
            if (empty($param["click"])) {
                $param["click"] = rand(1, 100);
            }
            $tags = $param["tags"];
            if (!empty($tags)) { //文档标签
                $this->addTags($tags);
            }
            $param['lang'] = $this->lang;
            $images = \app\common\model\Images::create($param);
            xn_add_admin_log($param['title'], "images"); //添加日志
            $seo = xn_cfg("seo");
            xn_add_admin_log("添加图片集", "images", $param['title']); //添加日志
            if ($seo["url_model"] == 3) {
                $rparam =  ["columnId" => $param["column_id"], "oneId" => "key3", "first" => 1, "column_model" => "images", "id" => $images->id];
                $rparam = array_merge($rparam, $seo);
                $this->success('操作成功', "", $rparam);
            } else {
                $this->success('操作成功');
            }
        }

        View::assign('column', $column);
        return view();
    }

    private function getAttrListAttr($article_field)
    {
        $attrTextList = [
            'c' => ['text' => '推荐', 'state' => 0, 'tag' => 'c'],
            't' => ['text' => '头条', 'state' => 0, 'tag' => 't'],
            'h' => ['text' => '热门', 'state' => 0, 'tag' => 'h'],
            'b' => ['text' => '加粗', 'state' => 0, 'tag' => 'b'],
            's' => ['text' => '幻灯', 'state' => 0, 'tag' => 's'],
        ];

        $attrTextListR = [];
        $articleFieldArr = explode(',', $article_field);
        foreach ($attrTextList as $akey => $ak) {
            foreach ($articleFieldArr as $key => $articleField) {
                if ($articleField == $akey) {
                    $ak['state'] = 1;
                    break;
                }
            }
            array_push($attrTextListR, $ak);
        }
        return $attrTextListR;
    }

    public function edit()
    {
        $param = $this->request->param();

        $bcid = $param['bcid'];
        View::assign('bcid', $bcid);
        $ids = explode('_', $bcid);
        $columnId = $ids[sizeof($ids) - 1]; //栏目id
        $column = Column::find($columnId);
        if (!empty($column->tier)) {
            $bcidStr = '4_' . str_replace(",", "_", $column->tier);
        } else {
            $bcidStr = '4';
        }
        $breadcrumb = Column::getBreadcrumb($bcidStr);
        array_push($breadcrumb, ['id' => '', 'title' => '编辑图片集', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/Article/edit', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);

        View::assign('id', $param['id']);
        if (array_key_exists('id', $param)) {
            $images = \app\common\model\Images::find($param['id']);
            $articleFieldList = $this->getAttrListAttr($images->article_field);
            View::assign('articleFieldList', $articleFieldList);
            View::assign('images', $images);
        }

        if ($this->request->isAjax()) {
            if (array_key_exists("content", $param)) {
                $rdata = $this->replaceContent($param, 'content'); //替换内容
                if ($rdata["code"] == 0) {
                    $this->error($rdata['msg']);
                }
                $param['content'] = $rdata['content']; //替换内容
            }
            $tags = $param["tags"];
            if (!empty($tags)) { //文档标签
                $this->addTags($tags);
            }

            $fc = \app\common\model\Images::field("id,create_time")->find($param['id']);
            if ($fc) {
                if (empty($fc['create_time'])) {
                    $param['create_time'] = date('Y-m-d H:i:s', time());
                }
                if (empty($fc['update_time'])) {
                    $param['update_time'] = date('Y-m-d H:i:s', time());
                }
            } else {
                $this->error("抱歉没找到修改数据");
            }

            $param['lang'] = $this->lang;
            \app\common\model\Images::update($param);
            xn_add_admin_log("修改图片集", "images", $param['title']); //添加日志
            $seo = xn_cfg("seo");
            if ($seo["url_model"] == 3) {
                $rparam =  ["columnId" => $param["column_id"], "oneId" => "key3", "first" => 1, "column_model" => "images", "id" => $param["id"]];
                $rparam = array_merge($rparam, $seo);
                $this->success('操作成功', "", $rparam);
            } else {
                $this->success('操作成功');
            }
        }

        return view();
    }

    public function batchAddField()
    {
        $param = $this->request->param();
        if (array_key_exists('articleField', $param) && array_key_exists('ids', $param)) {
            $idsArr = explode(",", $param['ids']);
            $res = \app\common\model\Images::whereIn("id", $idsArr)->update(['article_field' => $param['articleField']]);
            if ($res) {
                $this->success("操作成功");
            }
        }
        $this->error("操作失败");
    }

    public function batchDelField()
    {
        $param = $this->request->param();
        if (array_key_exists('ids', $param)) {
            $idsArr = explode(",", $param['ids']);
            $res = \app\common\model\Images::whereIn("id", $idsArr)->update(['article_field' => '']);
            if ($res) {
                $this->success("操作成功");
            }
        }
        $this->error("操作失败");
    }

    public function batchMove()
    {
        $param = $this->request->param();
        if (array_key_exists('columnId', $param) && array_key_exists('ids', $param)) {
            $idsArr = explode(",", $param['ids']);
            $res = \app\common\model\Images::whereIn("id", $idsArr)->update(['column_id' => $param['columnId']]);
            if ($res) {
                xn_add_admin_log("批量移除图片集", "images"); //添加日志
                $this->success("操作成功");
            }
        }
        $this->error("操作失败");
    }

    public function batchCope()
    {
        $param = $this->request->param();
        if (array_key_exists('ids', $param)) {
            $ids = $param['ids'];
            if (!preg_match('/^[\d,]+$/', $ids)) {
                $this->error("非法的ID列表");
            }
            
            $idsArray = explode(',', $ids);
            
            // 使用ORM方法查询数据
            $images = \app\common\model\Images::whereIn('id', $idsArray)->select();
            
            // 准备插入数据
            $insertData = [];
            foreach ($images as $image) {
                $data = $image->toArray();
                // 移除不需要复制的字段
                unset($data['id'], $data['create_time'], $data['update_time'], $data['click']);
                $insertData[] = $data;
            }
            
            // 批量插入
            if (!empty($insertData)) {
                (new \app\common\model\Images())->saveAll($insertData);
                $this->success("操作成功");
            } else {
                $this->error("未找到要复制的数据");
            }
        }
        $this->error("操作失败");
    }

    public function batchDel()
    {
        $param = $this->request->param();
        if (array_key_exists('ids', $param)) {
            $ids = $param['ids'];
            
            // 验证格式
            if (!preg_match('/^[\d,]+$/', $ids)) {
                $this->error("非法的ID列表");
            }
            
            $res = \app\common\model\Images::whereIn('id', $ids)->delete();
            if ($res) {
                xn_add_admin_log("批量删除图片集", "images"); //添加日志
                $this->success("操作成功");
            }
        }
        $this->error("操作失败");
    }

    // 保存字典
    public function saveDict()
    {
        $param = $this->request->param();
        if (array_key_exists("dataList", $param) && array_key_exists("id", $param)) {
            $idFlag = $param['id'];
            $dataList = $param['dataList'];
            if (sizeof($dataList) <= 0) {
                $this->error("数据为空,操作失败");
            }
            $id = 0;
            $saveData = [];
            foreach ($dataList as $data) {
                if (($id == 0) && !empty($data['id'])) {
                    $id = intval($data['id']);
                }
                array_push($saveData, $data['text']);
            }

            //去重
            $saveData = array_unique($saveData);
            //重新排序
            $saveData = array_values($saveData);

            $dictdata = new \app\common\model\DictData();
            $adminId = $this->getAdminId(); //当前登录用户id
            $dataStr = implode(",", $saveData);
            $isOp = false; //是否操作成功
            if ($id <= 0) { //保存
                $data =  array('dict_label' => $dataStr, 'dict_value' => $dataStr, 'dict_type' => $idFlag, 'remark' => "保存", 'create_by' => $adminId, 'update_by' => $adminId);
                $isOp = $dictdata->save($data);
                $id = $dictdata["id"];
            } else { //更新
                $data =  array('dict_label' => $dataStr, 'dict_value' => $dataStr, 'dict_type' => $idFlag, 'remark' => "更新", 'create_by' => $adminId, 'update_by' => $adminId);
                $isOp = $dictdata->where("dict_code", $id)->update($data);
            }
            if ($isOp) {
                $rdata = ['id' => $id, 'dataList' => $saveData];
                $this->success('操作成功', null, $rdata);
            } else {
                $this->error("操作失败");
            }
        } else {
            $this->error("参数错误,操作失败!");
        }
    }

    public function look($id)
    {
        $images = \app\common\model\Images::find($id);
        $model = "";
        $columnId = $images->column_id;
        $columnO = Column::find($columnId);
        if ($columnO) {
            $model = $columnO["column_model"];
        }
        $path = "/$model/detail";
        $url = detailSetUrl($path, $id, $columnId, $model, $this->lang);
        $url = replaceSymbol($url);
        $this->redirect($url);
        exit();
    }

    private function addTags($tags)
    {
        try {
            $findTags = \app\common\model\Tag::select();
            $is_used = 1;
            $tagList = explode(",", $tags);
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
                    array_push($tagArr, ["name" => $tag, "is_used" => $is_used]);
                }
            }
            (new \app\common\model\Tag())->saveAll($tagArr);
        } catch (\Exception $e) {
        }
    }

    public function clickField()
    {
        $param = $this->request->param();
        if (empty($param['id']) || empty($param['field_type'])) {
            $this->error("缺少文章属性参数");
        }
        $article = \app\common\model\Images::find($param['id']);
        if (!$article) {
            $this->error("没找到文章数据");
        }
        $field_type = $param['field_type'];
        $article_field = $article['article_field'];
        $article_fields = [];
        if (empty($article_field)) {
            array_push($article_fields, $field_type);
        } else {
            $article_fields = explode(",", $article_field);
            if (in_array($field_type, $article_fields)) {
                $afs = [];
                foreach ($article_fields as $af) {
                    if ($af != $field_type) {
                        array_push($afs, $af);
                    }
                }
                $article_fields = $afs;
            } else {
                array_push($article_fields, $field_type);
            }
        }

        $r = \app\common\model\Images::update(['id' => $param['id'], 'article_field' => implode(",", $article_fields)]);
        if ($r <= 0) {
            $this->error("操作失败");
        }
        $this->success("操作成功");
    }
}