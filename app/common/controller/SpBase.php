<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   18:12
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   18:12
 */

declare(strict_types=1);

namespace app\common\controller;

error_reporting(E_ERROR);

use app\common\model\Column;
use app\common\model\ModelRecord;
use app\sp\model\SpJumpLink;
use app\common\model\UploadFiles;
use think\App;
use think\facade\Db;

// 小程序控制器基础类
abstract class SpBase
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 请求域名
     * @var
     */
    protected $domain;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {
        $this->domain = $this->request->domain() . "/";
    }

    // 添加前缀
    protected function addPrefix($value, $domain = "")
    {
        if (empty($domain)) {
            $domain = $this->domain;
        }
        if (empty($value)) {
            return "";
        }
        if (preg_match('/(http:\/\/)|(https:\/\/)/i', $value)) { //判断是否存在
            return $value;
        } else {
            if (str_starts_with($value, "/")) {
                $value = substr($value, 1, strlen($value));
            }
            $value = $domain . $value;
            return $value;
        }
    }

    // 链接查询
    protected function getLink($id)
    {
        $link = SpJumpLink::field('link as url,sign,column_id')->find($id);
        return $link;
    }

    // 查询模型
    protected function getModel($columnId)
    {
        if (empty($columnId)) {
            return "";
        }
        $column = Column::field('column_model')->find($columnId);
        if ($column) {
            return $column['column_model'];
        }
        return "";
    }

    // 获取当前栏目的id集合
    protected function getCurrColumnIds($columnId)
    {
        if (empty($columnId)) {
            return [];
        }
        $column = Column::find($columnId);
        if (!$column) {
            return [];
        }
        $data_limit = $column['data_limit'];
        $columnIdArr = [];
        if ($data_limit == 1) { //仅本栏目
            array_push($columnIdArr, $columnId);
        } elseif ($data_limit == 2 || $data_limit == 3) { //本栏目及下级栏目||本栏目及指定子栏目
            $columnIdArr = explode(",", $column["limit_column"]);
        }

        return $columnIdArr;
    }

    // 获取当前栏目
    protected function getCurrColumn($columnId)
    {
        if (empty($columnId)) {
            return null;
        }
        $column = Column::find($columnId);
        return $column;
    }

    // 查询栏目模型列表
    protected function getColumnModelList($columnId, $isLimit = true)
    {
        if (empty($columnId)) {
            return [];
        }
        $column = Column::find($columnId);
        if (!$column) {
            return [];
        }
        $modelList = [];
        if ($isLimit) {
            $data_limit = $column['data_limit'];
            $columnIdArr = [];
            if ($data_limit == 1) { //仅本栏目
                array_push($columnIdArr, $columnId);
            } elseif ($data_limit == 2 || $data_limit == 3) { //本栏目及下级栏目||本栏目及指定子栏目
                $columnIdArr = explode(",", $column["limit_column"]);
            }
            $columns = Column::field('id,column_model')->whereIn('id', implode(",", $columnIdArr))->select();
            foreach ($columns as $col) {
                $column_model = $col['column_model'];
                $isExist = false;
                foreach ($modelList as $m) {
                    if ($m['column_model'] == $column_model) {
                        $isExist = true;
                        break;
                    }
                }
                if (!$isExist) {
                    array_push($modelList, ["column_model" => $column_model]);
                }
            }
        } else {
            $column_model = $column['column_model'];
            array_push($modelList, ["column_model" => $column_model]);
        }
        return $modelList;
    }

    // 查询栏目数据更加文章属性
    protected function getColumnDatasByArticleField($columnId, $pageSize, $article_field, $currentPage = 1, $where = [])
    {
        $modelList = $this->getColumnModelList($columnId);
        if (sizeof($modelList) <= 0) {
            return [];
        }
        $tableList = [];
        foreach ($modelList as $col) {
            $column_model = $col['column_model'];
            //            $column_model = $model;
            $modelRecord = ModelRecord::field('reference_model')->where(['nid' => $column_model, 'status' => 1, 'is_delete' => 0])->find();
            if ($modelRecord['reference_model'] == 0) { //类似文章
                array_push($tableList, config("database.connections.mysql.prefix") . $column_model);
            }
        }
        $tableList = array_unique($tableList);
        if (sizeof($tableList) <= 0) {
            return [];
        }
        $mmfArr = xn_cfg("list");
        $commonFiled = implode(",", $mmfArr);
        if (sizeof($tableList) > 1) {
            $sqlArr = [];
            foreach ($tableList as $key => $table) {
                if ($key > 0) {
                    array_push($sqlArr, "SELECT {$commonFiled} FROM {$table}");
                }
            }
            $commonFiledFirst = $commonFiled;
            $tableName = $tableList[0]['table'];
            $dataSql = Db::table($tableName)->field($commonFiledFirst)->union($sqlArr)->buildSql();
            $sql = "({$dataSql})";
        } else {
            $dataSql = Db::table($tableList[0])->field($commonFiled)->buildSql();
            $sql = "({$dataSql})";
        }
        $rpage = Db::table($sql . " as a")->where($where)->whereFindInSet('article_field', $article_field)->paginate(['page' => $currentPage, 'list_rows' => $pageSize])->each(function ($item, $key) {
            if (!empty($item["breviary_pic_id"])) {
                $uf = UploadFiles::field("url")->find($item["breviary_pic_id"]);
                if ($uf) {
                    $item['img_url'] = $this->addPrefix($uf['url']);
                }
            }
            unset($item['breviary_pic_id']);
            return $item;
        });
        return $rpage;
    }

    protected function getColumnDatas($columnId, $pageSize, $currentPage = 1, $where = [])
    {
        $modelList = $this->getColumnModelList($columnId);
        if (sizeof($modelList) <= 0) {
            return [];
        }
        $tableList = [];
        foreach ($modelList as $col) {
            $column_model = $col['column_model'];
            //            $column_model = $model;
            $modelRecord = ModelRecord::field('reference_model')->where(['nid' => $column_model, 'status' => 1, 'is_delete' => 0])->find();
            if ($modelRecord['reference_model'] == 0) { //类似文章
                array_push($tableList, config("database.connections.mysql.prefix") . $column_model);
            }
        }
        $tableList = array_unique($tableList);
        if (sizeof($tableList) <= 0) {
            return [];
        }
        $mmfArr = xn_cfg("list");
        $commonFiled = implode(",", $mmfArr);
        if (sizeof($tableList) > 1) {
            $sqlArr = [];
            foreach ($tableList as $key => $table) {
                if ($key > 0) {
                    array_push($sqlArr, "SELECT {$commonFiled} FROM {$table}");
                }
            }
            $commonFiledFirst = $commonFiled;
            $tableName = $tableList[0]['table'];
            $dataSql = Db::table($tableName)->field($commonFiledFirst)->union($sqlArr)->buildSql();
            $sql = "({$dataSql})";
        } else {
            $dataSql = Db::table($tableList[0])->field($commonFiled)->buildSql();
            $sql = "({$dataSql})";
        }
        $rpage = Db::table($sql . " as a")->where($where)->paginate(['page' => $currentPage, 'list_rows' => $pageSize])->each(function ($item, $key) {
            if (!empty($item["breviary_pic_id"])) {
                $uf = UploadFiles::field("url")->find($item["breviary_pic_id"]);
                if ($uf) {
                    $item['img_url'] = $this->addPrefix($uf['url']);
                }
            }
            unset($item['breviary_pic_id']);
            return $item;
        });
        return $rpage;
    }
}