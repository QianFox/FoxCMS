<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   18:03
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   18:03
 */

namespace app\common\controller;

use app\admin\util\Field;
use app\common\model\AuthRule;
use app\common\model\Basic;
use app\common\model\FieldType;
use think\facade\Db;
use think\facade\Session;
use think\facade\View;
use utils\Auth;
use utils\Data;

class AdminBase extends Base
{
    protected $noAuth = ["index", "home", "getField", "getVariate", "base"]; //不用验证权限的操作
    protected $noLogin = []; //不用登录
    protected $template; //当前模板
    protected $templateHtml;       // 模板HTML全目录
    protected $relativeTemplateHtml; //模板的相对路径
    protected $templateType;       // 模板类型
    protected $cid;

    public function initialize()
    {
        //模板数据-start
        $template = \app\common\model\Template::where('run_status', 1)->find();
        $this->templateType = $template["type"];
        $this->template = $template;

        $templatePath = "templates" . DIRECTORY_SEPARATOR . $template['template'] . DIRECTORY_SEPARATOR .
            $template['html'] . DIRECTORY_SEPARATOR;
        $this->templateHtml   = replaceSymbol(root_path() . $templatePath);
        $relativeTemplateHtmlPath = DIRECTORY_SEPARATOR . $template['template'] . DIRECTORY_SEPARATOR . $template['html'];
        $this->relativeTemplateHtml = replaceSymbol($relativeTemplateHtmlPath);
        //模板数据-end
        parent::initialize();

        if (in_array($this->request->action(), $this->noLogin)) { //不用登录
            return true;
        } else {
            if (!$this->isLogin()) $this->redirect(url('login/index'));
            if (!$this->checkAuth()) {
                if ($this->request->isAjax()) {
                    $action = $this->request->action();
                    if (str_starts_with($action, "delete") || str_starts_with($action, "save")) {
                        $this->error('抱歉,您没有权限!');
                    }
                } else {
                    $this->redirect(url('/auth/No/index'));
                }
            }
        }
        // 菜单数据
        $menu_data = [];
        if ($this->getAdminId() == 1) { //超级管理员
            $auth = new AuthRule();
            $menu_data = $auth->getMenu();
        } else { //其他用户
            $auth = new Auth();
            $menu_data = $auth->getAuthList($this->getAdminId());
        }

        $result = saveToCache($this->getAdminId() . '_menu', json_encode($menu_data));
        if (!$result) {
            \think\facade\Log::error("保存菜单1缓存异常");
        }
        $menu_data = Data::channelLevel($menu_data, 0, '&nbsp;', 'id');
        View::assign('menu', $menu_data);

        $columnId = $this->request->param('columnId');
        $type = $this->request->param('type');
        $isF = false; //点击最外层
        if (empty($columnId)) {
            //第一层菜单
            $md = array_shift($menu_data); //第一
            //            $md = array_pop($menu_data);//最后一个
            //            $bcidStr = $md['tier'];
            $columnId = $md['id'];
            $pid = $md['id'];
            $isF = true; //点击的最外层
        } else {
            $ar = AuthRule::find($columnId); //权限
            if ($ar->pid == 0) {
                $isF = true; //点击的最外层
            }
            $idArr = explode(",", $ar->tier);
            array_shift($idArr);
            $pid = array_shift($idArr);
            $pid = intval($pid);
            $columnId = intval($columnId);
        }
        $bid = 0; //面包屑id
        $chileMenus = $this->getChildMenus($pid);
        //过滤子菜单开始
        $ruleIdStr = $this->request->param('ruleIds');
        if ($ruleIdStr != null) {
            $ruleIds = explode(",", $ruleIdStr);
            if (sizeof($ruleIds) > 0) {
                $chileMenusNew = array();
                foreach ($chileMenus as $k => $v) {
                    if (in_array($k, $ruleIds)) {
                        array_push($chileMenusNew, $v);
                    }
                }
                $chileMenus = $chileMenusNew;
            }
        }
        //过滤子菜单结束
        if (sizeof($chileMenus) > 0) {
            $first = array_slice($chileMenus, 0, 1)[0];
            $clickId = $first['id'];
            if (sizeof($first['_data']) > 0) {
                $firstF = array_slice($first['_data'], 0, 1)[0];
                $clickId = $firstF['id'];
            }

            if ($isF || empty($columnId)) {
                View::assign('clickId', $clickId);
                $bid = $clickId; //面包屑id
            } else {
                View::assign('clickId', $columnId);
                $bid = $columnId; //面包屑id
            }
        }
        if ($bid == 0) {
            $bid = $columnId;
        }
        //        $url = $this->request->url();//请求路径地址
        //面包屑
        $bar = AuthRule::find($bid);
        if (!($bar->type == "B") && empty($type)) { //按钮
            View::assign('clickName', $bar->title);
            View::assign('fox_menu', "display:block");
        } else {
            View::assign('fox_menu', "display:none");
        }

        $bcidArr = explode(",", $bar->tier);
        array_shift($bcidArr);
        $bcid = implode("_", $bcidArr);
        View::assign('bcid', $bcid);
        $breadcrumb = AuthRule::getBreadcrumb($bcid);
        View::assign("breadcrumb", $breadcrumb); //面包屑

        //子菜单
        View::assign('chileMenus', $chileMenus);
        View::assign('cid', $columnId); //当前菜单id
        View::assign('pid', $pid); //父菜单id
        $this->cid = $columnId;
        //当前登录用户
        $admin_data = Session::get('admin_auth');
        View::assign('admin_data', $admin_data);
        //基本信息
        $basic = Basic::field('name,web_logo')->where(['status' => 1])->find();
        View::assign("basic", $basic);

        //语言
        $this->lang();
    }

    // 获取菜单
    private function getMenu()
    {
        $menu_data_str =  saveToCache($this->getAdminId() . '_menu');
        $menu_data = [];
        if (!$menu_data_str && ($menu_data_str != '[]')) {
            $menu_data = json_decode($menu_data_str, true);
        } else {
            // 分配菜单数据
            if ($this->getAdminId() == 1) { //超级管理员
                $auth = new AuthRule();
                $menu_data = $auth->getMenu();
            } else { //其他用户
                $auth = new Auth();
                $menu_data = $auth->getAuthList($this->getAdminId());
            }
            //            halt($menu_data);
            $result = saveToCache($this->getAdminId() . '_menu', json_encode($menu_data));
            $menu_data = Data::channelLevel($menu_data, 0, '&nbsp;', 'id');
            if (!$result) {
                \think\facade\Log::error("保存2菜单缓存异常");
            }
        }
        return $menu_data;
    }

    // 获取子菜单
    private function getChildMenus(int $id)
    {
        //        $menu_data_str = saveToCache($id.'_'.$this->getAdminId().'_menu');
        $menu_data_str = "";
        $menu_data = [];
        if (!$menu_data_str) {
            $menu_data = $this->getMenu();
            $ret = saveToCache($id . '_' . $this->getAdminId() . '_menu', json_encode($menu_data));
            if (!$ret) {
                \think\facade\Log::error("保存菜单缓存异常");
            }
        } else {
            $menu_data = json_decode($menu_data_str, true);
        }
        foreach ($menu_data as $k => $v) {
            if ($id === $k) {
                return $v['_data'];
            }
        }

        return  $menu_data;
    }

    // 检测操作权限
    protected function checkAuth($rule_name = '')
    {
        $auth = new Auth();
        if (empty($rule_name)) $rule_name = '/' . $this->request->controller() . '/' . $this->request->action();
        if ($this->getAdminId() != 1) {
            if (in_array($this->request->action(), $this->noAuth)) {
                return true;
            } else {
                if (!$auth->check($rule_name, $this->getAdminId())) {
                    return false;
                }
            }
        }
        return true;
    }

    // 检测菜单权限
    protected function checkMenuAuth($rule_name)
    {
        $auth = new Auth();
        $rule_name = xn_uncamelize($rule_name);
        if (!$auth->check($rule_name, $this->getAdminId()) && $this->getAdminId() != 1) {
            return false;
        }
        return true;
    }

    // 是否已经登录
    protected function isLogin()
    {
        return $this->getAdminId() ? true : false;
    }

    // 管理员登录ID
    protected function getAdminId()
    {
        $admin_id = intval(Session::get('admin_auth.id'));
        if (!($admin_id > 0)) {
            return 0;
        }
        return $admin_id;
    }

    // 生成栏目访问路径
    protected function getVPath($model)
    {
        $vpath = "/" . $model . "/" . "index";
        return $vpath;
    }

    // 获取栏目自定义属性
    public function getField()
    {
        $where = ['status' => 1, "is_system" => 0];
        $id = $this->request->param("id");
        $query = \app\common\model\ColumnField::field('dtype,name')->where($where);
        if (!empty($id)) {
            $query->where(function ($query) use ($id) {
                $query->whereOr([['', 'exp', \think\facade\Db::raw("FIND_IN_SET($id, column_ids)")]]);
            });
        }
        $columnList = $query->order(["sort_order" => "desc", "create_time" => "asc"])->select();
        $this->success("查询成功", '', $columnList);
    }

    // 查询字段类型
    public function fieldTypeList()
    {
        $dtype = $this->request->param("dtype"); //字段类型
        $disableDtypes = (new Field())->convertField($dtype); //不允许转换字段类型
        $fieldTypeList = FieldType::field("id,name,title,status")->where("status", 1)->select();
        foreach ($fieldTypeList as $key => $fieldType) {
            $fieldType['isDisable'] = in_array($fieldType["name"], $disableDtypes); //存在就禁用
        }
        $this->success("查询成功", null, $fieldTypeList);
    }
}
