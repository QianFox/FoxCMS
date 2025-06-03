<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   14:42
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   14:42
 */

namespace app\admin\controller;

use app\common\controller\AdminContentBase;
use app\common\model\Column;
use think\facade\View;
use utils\Data;

// 内容
class Content extends AdminContentBase
{
    //    protected $noAuth = ['index','home','testHtml'];

    public function index()
    {
        $param = $this->request->param();
        $bcid = $param['bcid'];
        $columnId = $this->cid;
        if (!empty($bcid)) {
            View::assign('bcid', $param['bcid']);
            $ids = explode('_', $param['bcid']);
            if (sizeof($ids) > 0) {
                $columnId = $ids[sizeof($ids) - 1]; //栏目id
            }
        }
        $column = new Column();
        $columnObj = $column->find($columnId);
        $model = $columnObj->column_model; //模型
        if ($model == 'single') {
            $model = 'single_c';
        }
        if (empty($model)) {
            return view('index');
            exit();
        }
        if (empty($bcid)) {
            $bcid = str_replace(',', '_', $columnObj->tier);
        }
        $url = url("$model" . "/index", ["columnId" => $columnId, "bcid" => $bcid]);
        $this->redirect($url);
        exit();
    }

    // 获取面包屑
    public function getBreadcrumb()
    {
        //面包屑-当前位置
        $bcid = $this->request->get('bcid');
        $breadcrumb = [];
        if (!empty($bcid)) {
            $breadcrumb = Column::getBreadcrumb($bcid);
        }
        return $this->success('获取成功', null, $breadcrumb);
    }

    // 获取菜单
    private function getMenu()
    {
        $menu_data = [];
        // 分配菜单数据
        $column = new Column();
        $menu_data = $column->order('level asc')->order('sort asc')->select();
        $menu_data = Data::channelLevel($menu_data, 0, '&nbsp;', 'id');
        return $menu_data;
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

    // 获取子菜单
    public function getChildMenus(int $id)
    {
        $menu_data = [];
        $menu_data = $this->getMenu();
        return $this->success('获取成功', null, $menu_data);
    }
}