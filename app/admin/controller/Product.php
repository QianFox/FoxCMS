<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   15:22
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   15:22
 */

namespace app\admin\controller;

use app\common\controller\AdminContentBase;
use app\common\model\Column;
use app\common\model\DictType as DictTypeModel;
use app\common\model\ProductAttr;
use app\common\model\ProductAttrParam;
use app\common\model\ProductParam;
use think\Exception;
use think\facade\View;

class Product extends AdminContentBase
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
        $columnDatas  = Column::where(["column_model" => "product"])->field('id,name as title')->order('level asc')->order('sort asc')->select(); //栏目
        View::assign('columns', $columnDatas);

        //查询产品预设
        $productAttr = new ProductAttr();
        $productAttrList = $productAttr->where('status', 1)->where('lang', $this->getMyLang())->order('sort', 'asc')->select();
        View::assign('productAttrList', $productAttrList);

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

            if (empty($param["click"])) {
                $param["click"] = rand(1, 100);
            }

            //向下查询所有子孙栏目
            $columnIdArr = getChildrensColumnId($columnId, 'product', $this->getMyLang());
            if (sizeof($columnIdArr) > 0) {
                array_push($where, ['column_id', 'in', implode(",", $columnIdArr)]);
            } else {
                array_push($where, ['column_id', '=', $columnId]);
            }
            array_push($where, ['lang', '=', $this->getMyLang()]);

            $product = new \app\common\model\Product();
            $list = $product->where($where)->order("create_time", "desc")->paginate(['page' => $param['currentPage'], 'list_rows' => $param['pageSize']]);
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
        array_push($breadcrumb, ['id' => '', 'title' => '添加产品', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/Product/add', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);
        $clang = $this->getMyLang();
        if ($this->request->isAjax()) {
            //更新产品属性顺序
            if (array_key_exists("product_attr_ids", $param) && !empty($param['product_attr_ids'])) {
                $product_attr_ids = $param['product_attr_ids'];
                $product_attr_idArr = explode(",", $product_attr_ids);
                $ProductAttrUData = [];
                foreach ($product_attr_idArr as $key => $product_attr_id) {
                    array_push($ProductAttrUData, ['sort' => ($key + 1), "id" => $product_attr_id, 'lang' => $clang]);
                }
                (new ProductAttr())->saveAll($ProductAttrUData);
            }
            unset($param['product_attr_ids']);

            //替换内容
            if (array_key_exists("content", $param)) {
                $rdata = $this->replaceContent($param, 'content');
                if ($rdata["code"] == 0) {
                    $this->error($rdata['msg']);
                }
                $param['content'] = $rdata['content']; //替换内容
            }
            $productParams = $param['productParams']; //产品参数
            $product = new \app\common\model\Product();
            if (empty($param["release_time"])) {
                $param["release_time"] = date("Y-m-d H:i:s");
            }
            $param['lang'] = $clang;
            $product->save($param);
            $productId = $product->id;
            $productParamDatas = []; //产品参数
            foreach ($productParams as $key => $productParam) {
                $pid = $productParam["pid"];
                if ($productParam["is_select"] == 1) { //判断是否下拉
                    //获取下拉选项拉取默认值
                    $productParamObj = (new ProductAttrParam())->find($pid);
                    if ($productParamObj) {
                        $dfvalueStr = str_replace("\n", ",", $productParamObj->dfvalue);
                        array_push(
                            $productParamDatas,
                            [
                                'product_id' => $productId,
                                'name' => $productParam["name"],
                                'dfvalue' => $productParam["dfvalue"],
                                'type' => $productParamObj->type,
                                "type_id" => $productParamObj->type_id,
                                'type_desc' => $productParamObj->type_desc,
                                'sel_value' => $dfvalueStr,
                                "attr_param_id" => $pid,
                                'id' => $productParam["id"],
                                'sort' => $productParam['sort']
                            ]
                        );
                    }
                } else {
                    array_push($productParamDatas, [
                        'product_id' => $productId,
                        'name' => $productParam["name"],
                        'dfvalue' => $productParam["dfvalue"],
                        'type' => "varchar(255)",
                        "type_id" => 11,
                        'type_desc' => "默认输入",
                        "attr_param_id" => $pid,
                        'id' => $productParam["id"],
                        'sort' => $productParam['sort']
                    ]);
                }
            }
            if (sizeof($productParamDatas) > 0) {
                (new ProductParam())->saveAll($productParamDatas);
            }

            $tags = $param["tags"];
            if (!empty($tags)) { //文档标签
                $this->addTags($tags);
            }
            xn_add_admin_log("添加产品", "product", $param['title']); //添加日志
            $seo = xn_cfg("seo");
            if ($seo["url_model"] == 3) {
                $rparam =  ["columnId" => $param["column_id"], "oneId" => "key3", "first" => 1, "column_model" => "product", "id" => $productId];
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
        array_push($breadcrumb, ['id' => '', 'title' => '编辑产品', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/Product/add', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);

        View::assign('id', $param['id']);
        if (array_key_exists('id', $param)) {
            $product = \app\common\model\Product::find($param['id']);
            $articleFieldList = $this->getAttrListAttr($product->article_field);
            View::assign('articleFieldList', $articleFieldList);
            View::assign('product', $product);
        }

        //查询产品参数
        $productParamList = (new ProductParam())->where("product_id", $param['id'])->order("sort asc")->select(); //产品属性
        $clang = $this->getMyLang();
        if ($this->request->isAjax()) {

            //更新产品属性顺序
            if (array_key_exists("product_attr_ids", $param) && !empty($param['product_attr_ids'])) {
                $product_attr_ids = $param['product_attr_ids'];
                $product_attr_idArr = explode(",", $product_attr_ids);
                $ProductAttrUData = [];
                foreach ($product_attr_idArr as $key => $product_attr_id) {
                    array_push($ProductAttrUData, ['sort' => ($key + 1), "id" => $product_attr_id, 'lang' => $clang]);
                }
                (new ProductAttr())->saveAll($ProductAttrUData);
            }
            unset($param['product_attr_ids']);

            //替换内容
            if (array_key_exists("content", $param)) {
                $rdata = $this->replaceContent($param, 'content');
                if ($rdata["code"] == 0) {
                    $this->error($rdata['msg']);
                }
                $param['content'] = $rdata['content']; //替换内容
            }
            $productParams = $param['productParams']; //产品参数
            $product = new \app\common\model\Product();
            $param['lang'] = $clang;
            $product->update($param);
            $productId = $param["id"];

            $productParamDatas = []; //产品参数
            $updateProductParamIds = []; //更新的产品参数id
            foreach ($productParams as $key => $productParam) {
                $saveData = [];
                $pid = $productParam["pid"];
                if ($productParam["is_select"] == 1) { //判断是否下拉
                    //获取下拉选项拉取默认值
                    $productParamObj = (new ProductAttrParam())->find($pid);
                    if ($productParamObj) {
                        $dfvalueStr = str_replace("\n", ",", $productParamObj->dfvalue);

                        $saveData = [
                            'product_id' => $productId,
                            'name' => $productParam["name"],
                            'dfvalue' => $productParam["dfvalue"],
                            'type' => $productParamObj->type,
                            "type_id" => $productParamObj->type_id,
                            'type_desc' => $productParamObj->type_desc,
                            'sel_value' => $dfvalueStr,
                            "attr_param_id" => $pid,
                            'sort' => $productParam['sort']
                        ];
                    }
                } else {
                    $saveData = [
                        'product_id' => $productId,
                        'name' => $productParam["name"],
                        'dfvalue' => $productParam["dfvalue"],
                        'type' => "varchar(255)",
                        "type_id" => 11,
                        'type_desc' => "默认输入",
                        "attr_param_id" => $pid,
                        'sort' => $productParam['sort']
                    ];
                }
                $id = $productParam["id"];
                if (!empty($id)) {
                    $saveData['id'] = intval($id);
                    array_push($updateProductParamIds, intval($id));
                    array_push($productParamDatas, $saveData);
                } else {
                    array_push($productParamDatas, $saveData);
                }
            }

            //查询需要多余参数
            $delProductParamIds = []; //删除的产品参数id
            foreach ($productParamList as $key => $pParam) {
                if (!in_array($pParam["id"], $updateProductParamIds)) {
                    array_push($delProductParamIds, $pParam["id"]);
                }
            }

            if (sizeof($delProductParamIds) > 0) { //删除多余的参数
                ProductParam::whereIn('id', implode(",", $delProductParamIds))->delete();
            } else {
                //                ProductParam::where('product_id', $productId)->delete();//删除之前的参数
            }
            if (sizeof($productParamDatas) > 0) { //保存
                (new ProductParam())->saveAll($productParamDatas);
            }

            $tags = $param["tags"];
            if (!empty($tags)) { //文档标签
                $this->addTags($tags);
            }
            xn_add_admin_log("编辑产品", "product", $param['title']); //添加日志
            $seo = xn_cfg("seo");
            if ($seo["url_model"] == 3) {
                $rparam =  ["columnId" => $param["column_id"], "oneId" => "key3", "first" => 1, "column_model" => "product", "id" => $productId];
                $rparam = array_merge($rparam, $seo);
                $this->success('操作成功', "", $rparam);
            } else {
                $this->success('操作成功');
            }
        }

        View::assign('productParamList', $productParamList);
        return view();
    }

    public function batchAddField()
    {
        $param = $this->request->param();
        if (array_key_exists('articleField', $param) && array_key_exists('ids', $param)) {
            $idsArr = explode(",", $param['ids']);
            $res = \app\common\model\Product::whereIn("id", $idsArr)->update(['article_field' => $param['articleField']]);
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
            $res = \app\common\model\Product::whereIn("id", $idsArr)->update(['article_field' => '']);
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
            $res = \app\common\model\Product::whereIn("id", $idsArr)->update(['column_id' => $param['columnId']]);
            if ($res) {
                xn_add_admin_log("批量移除产品", "product"); //添加日志
                $this->success("操作成功");
            }
        }
        $this->error("操作失败");
    }

    public function batchCope()
    {
        $param = $this->request->param();
        if (array_key_exists('ids', $param)) {
            try {
                $ids = $param['ids'];
                if (!preg_match('/^[\d,]+$/', $ids)) {
                    $this->error("非法的ID列表");
                }
                //文章字段
                $tableFields = (new \app\common\model\Product())->getTableFields();
                $tableFields = array_diff($tableFields, ["id", "create_time", "update_time", 'click']);
                $fields = "";
                foreach ($tableFields as $key => $field) {
                    $fields .= "`{$field}`,";
                }
                if (strlen($fields) <= 0) {
                    $this->error("操作失败,表字段为空");
                }
                $fields .= "`id`,";
                $fields = substr($fields, 0, strlen($fields) - 1);
                $productList = \app\common\model\Product::field($fields)->whereIn("id", $ids)->select()->toArray();

                //商品属性
                $prodectParamFields = (new ProductParam())->getTableFields();
                $prodectParamFields = array_diff($prodectParamFields, ["id", "create_time", "update_time"]);
                $paramFields = "";
                foreach ($prodectParamFields as $keyP => $fieldP) {
                    $paramFields .= "`{$fieldP}`,";
                }
                $paramFields = substr($paramFields, 0, strlen($paramFields) - 1);
                foreach ($productList as $p) {
                    $ppArr = ProductParam::field($paramFields)->where("product_id", $p["id"])->select()->toArray(); //产品参数
                    $p['create_time'] = date('Y-m-d H:i:s', time());
                    $p['update_time'] = $p['create_time'];
                    unset($p["id"]); //删除产品id
                    $newPId = (new \app\common\model\Product())->strict(false)->insertGetId($p);
                    if (sizeof($ppArr) > 0) {
                        $newppArr = [];
                        foreach ($ppArr as $pp) {
                            $pp["product_id"] = $newPId;
                            array_push($newppArr, $pp);
                        }
                        (new ProductParam())->saveAll($newppArr);
                    }
                }
                xn_add_admin_log("批量复制产品", "product"); //添加日志
                $this->success("操作成功");
            } catch (Exception $e) {
                $this->error("操作失败--" . $e->getMessage());
            }
        }
        $this->error("操作失败");
    }

    public function batchDel()
    {
        $param = $this->request->param();
        if (array_key_exists('ids', $param)) {
            $ids = $param['ids'];
            $res = \app\common\model\Product::whereIn('id', $ids)->delete();
            if ($res) {
                ProductParam::whereIn("product_id", $ids)->delete();
                xn_add_admin_log("批量删除产品", "product"); //添加日志
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

    // 新增参数预设
    public function addAttrParam()
    {
        $param = $this->request->param();
        if (!array_key_exists('attrName', $param)) {
            $this->error("预设名称参数不存在");
        }
        $id = $param['id'];
        $attrName = $param['attrName'];
        $attrParamArr = $param['attrParamArr']; //参数
        if (empty($id)) {
            $productAttr = new ProductAttr();
            $productAttr->lang = $this->getMyLang();
            $productAttr->name = $attrName;
            $productAttr->save();
            $productAttrId = $productAttr->id;
            $saveData = [];
            foreach ($attrParamArr as $key => $attrParam) {
                $attrParam["product_attr_id"] = $productAttrId;
                array_push($saveData, $attrParam);
            }
            if (sizeof($saveData) > 0) {
                $productAttrParam = new ProductAttrParam();
                $productAttrParam->saveAll($saveData);
            }
            $this->success("添加成功", null, $productAttrId);
        } else { //编辑
            $productAttr = new ProductAttr();
            $rProductAttr = $productAttr->find($id);
            if ($rProductAttr) {
                if ($rProductAttr->name != $attrName) {
                    $rProductAttr->name = $attrName;
                    $rProductAttr->save();
                }
                $saveData = [];
                foreach ($attrParamArr as $key => $attrParam) {
                    $attrParam["product_attr_id"] = $id;
                    array_push($saveData, $attrParam);
                }
                $productAttrParam = new ProductAttrParam();
                $productAttrParam->saveAll($saveData);
                $this->success("编辑成功", null, $saveData);
            } else {
                $this->error("修改失败");
            }
        }
    }

    // 查询参数预设产品属性表
    public function getAttrParamList()
    {
        $productAttr = new ProductAttr();
        $list = $productAttr->where('lang', $this->getMyLang())->where('status', 1)->order('create_time', 'desc')->select();
        $this->success("查询成功", null, $list);
    }

    // 查询产品属性参数表
    public function getAttrParams()
    {
        $param = $this->request->param();
        $attrParamId = $param['attrParamId'];
        if (!empty($attrParamId)) {
            $productAttrParam = new ProductAttrParam();
            $list = $productAttrParam->field('id,name as title, dfvalue as value, type_id as typeId')->whereIn('product_attr_id', $attrParamId)->order('create_time', 'desc')->select();
            foreach ($list as $key => $item) {
                if ($item->typeId == 13) {
                    $dfvalueStr = str_replace("\n", ",", $item->value);
                    $dfvalueArr = explode(",", $dfvalueStr);
                    $selectList = [];
                    $index = 1;
                    foreach ($dfvalueArr as $k => $dfv) {
                        array_push($selectList, ["id" => $index, "text" => $dfv]);
                        $index++;
                    }
                    if (sizeof($dfvalueArr) > 0) {
                        $item->value = $dfvalueArr[0];
                        $item->valueId = 1;
                    }
                    $item->selectList = $selectList;
                }
            }
            $this->success("查询成功", null, $list);
        } else {
            $this->error("预设参数查询失败");
        }
    }

    // 获取产品属性
    public function getProductParams()
    {
        $param = $this->request->param();
        $productId = $param['productId'];
        if (!empty($productId)) {
            $productParam = new ProductParam();
            $list = $productParam->field('id,name as title, dfvalue as value, type_id as typeId, sel_value')->whereIn('product_id', $productId)->order('create_time', 'desc')->select();
            foreach ($list as $key => $item) {
                if ($item->typeId == 13) {
                    $dfvalueArr = explode(",", $item->sel_value);
                    $selectList = [];
                    $index = 1;
                    foreach ($dfvalueArr as $k => $dfv) {
                        array_push($selectList, ["id" => $index, "text" => $dfv]);
                        if ($item->value == $dfv) {
                            $item->valueId = $index;
                        }
                        $index++;
                    }
                    $item->selectList = $selectList;
                }
            }
            $this->success("查询成功", null, $list);
        } else {
            $this->error("产品参数查询失败");
        }
    }

    // 删除产品属性参数表
    public function attrParamDelete()
    {
        $param = $this->request->param();
        $attrParamId = $param['attrParamId'];
        if (!empty($attrParamId)) {
            $productAttr = new ProductAttr();
            $productAttr->startTrans();
            try {
                $result = $productAttr->destroy($attrParamId);
                if ($result) {
                    $productAttrParam = new ProductAttrParam();
                    $productAttrParam->where('product_attr_id', $attrParamId)->delete();
                    $productAttr->commit();
                    $this->success("删除成功");
                } else {
                    $productAttr->rollback();
                    $this->error("删除失败");
                }
            } catch (Exception $e) {
                $productAttr->rollback();
            }
        } else {
            $this->error("删除失败");
        }
    }

    // 编辑产品属性参数表
    public function attrParamEdit()
    {
        $param = $this->request->param();
        $attrParamId = $param['attrParamId'];
        if (!empty($attrParamId)) {
            $productAttr = new ProductAttr();
            $pa = $productAttr->find($attrParamId);
            $dataList = [];
            foreach ($pa->param_list as $key => $pl) {
                array_push($dataList, ["id" => $pl->id, "title" => $pl->name, "type" => $pl->type_desc, "typeId" => $pl->type_id, "value" => $pl->dfvalue]);
            }
            $this->success("查询成功", null, $dataList);
        } else {
            $this->error("查询失败");
        }
    }

    // 删除单个产品属性参数
    public function paramDelete()
    {
        $param = $this->request->param();
        $paramId = $param['paramId'];
        if (!empty($paramId)) {
            $productParam = new ProductParam();
            $r = $productParam->destroy($paramId);
            if ($r) {
                $this->success("删除成功");
            } else {
                $this->error("删除失败");
            }
        } else {
            $this->error("删除失败");
        }
    }

    public function look($id)
    {
        $article = \app\common\model\Product::find($id);
        $model = "";
        $columnId = $article->column_id;
        $columnO = Column::find($columnId);
        if ($columnO) {
            $model = $columnO["column_model"];
        }
        $path = "/$model/detail";
        $url = detailSetUrl($path, $id, $columnId, $model, $this->getMyLang());
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
        $article = \app\common\model\Product::find($param['id']);
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

        $r = \app\common\model\Product::update(['id' => $param['id'], 'article_field' => implode(",", $article_fields)]);
        if ($r <= 0) {
            $this->error("操作失败");
        }
        $this->success("操作成功");
    }
}
