<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/29   11:27
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/29   11:27
 */

namespace app\admin\controller;

use app\common\controller\AdminContentBase;
use app\common\model\Column;
use app\common\model\UploadFiles;
use think\facade\Db;
use think\facade\View;

class SimilarArticle extends AdminContentBase
{
    private $model = "article";

    public function initialize()
    {
        parent::initialize();
        $this->model = $this->request->param("model");
        //查询栏目
        $columns  = Column::where(["column_model" => $this->model])->field('id,name as title')->order('level asc')->order('sort asc')->select(); //栏目
        View::assign('columns', $columns);
        View::assign("model", $this->model);
    }

    // 文章属性列表
    private function getAttrList($item)
    {
        $attrTextList = [
            'c' => ['text' => '推荐', 'state' => 0, 'type' => 'c'],
            't' => ['text' => '头条', 'state' => 0, 'type' => 't'],
            'h' => ['text' => '热门', 'state' => 0, 'type' => 'h'],
        ];
        $attrTextListR = [];
        $articleFieldArr = explode(',', $item['article_field']);
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

    public function index()
    {
        $param = $this->request->param();
        if ($this->request->isAjax(true)) {
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
            if (!empty($param["column"])) {
                array_push($where, ['column', '=', $param['column']]);
            }
            $list = Db::name($this->model)->where($where)->order("create_time", "desc")->paginate(['page' => $param['currentPage'], 'list_rows' => $param['pageSize']])->each(function ($item) {
                $item['attr_list'] = $this->getAttrList($item);
                if (!empty($item['breviary_pic_id'])) {
                    $img_url = UploadFiles::field('url')->find($item['breviary_pic_id'])['url'];
                } else {
                    $img_url = "";
                }
                $item['img_url'] = $img_url;

                return $item;
            });
            $this->success('查询成功', null, $list);
        }
        $addUrl = ucfirst($this->model) . "/add";
        $addLink = url("{$addUrl}");
        View::assign("addLink", $addLink);
        return view();
    }
}