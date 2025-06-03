<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   18:01
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   18:01
 */

namespace app\common\controller;

use app\common\model\AuthRule;
use app\common\model\Basic;
use think\facade\Db;
use think\facade\Session;
use think\facade\View;
use utils\Auth;
use utils\Data;

class AdminApplyBase extends Base
{
    protected $noAuth = ["index", "home"]; //不用验证权限的操作
    protected $noLogin = []; //不用登录
    protected $appName;        // 当前应用名称
    protected $controllerName; // 当前控制器名
    protected $template;       // 模板目录
    protected $templateHtml;       // 模板HTML目录
    protected $templateType;       // 模板类型

    public function initialize()
    {
        // 查找所有系统设置表数据
        $template = \app\common\model\Template::where('run_status', 1)->find();
        $this->templateType = $template["type"];
        $this->appName        = app('http')->getName();
        $this->controllerName = $this->request->controller();
        $this->template       = $template;
        $templatePath = "templates" . DIRECTORY_SEPARATOR . $template['template'] . DIRECTORY_SEPARATOR . $template['html'] . DIRECTORY_SEPARATOR;
        $this->templateHtml   = replaceSymbol(root_path() . $templatePath);
        //模板数据-end
        parent::initialize();

        if (in_array($this->request->action(), $this->noLogin)) { //不用登录
            return true;
        } else {
            $admin_path = config("adminconfig.admin_path");
            if (!$this->isLogin()) $this->redirect(url("/{$admin_path}/login/index"));
            if (!$this->checkAuth()) {
                if ($this->request->isAjax()) {
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
        $menu_data = Data::channelLevel($menu_data, 0, '&nbsp;', 'id');
        View::assign('menu', $menu_data);
        $columnId = $this->request->param('columnId');
        View::assign('columnId', $columnId); //当前菜单id
        View::assign('pid', 42);
        $apply_plugin_id = $this->request->param('apply_plugin_id'); //插件应用id
        $type = $this->request->param('type');
        if (empty($type)) {
            View::assign('apply_plugin_id', $apply_plugin_id); //当前菜单id
        }

        $id = $this->request->param("id");
        $applyMenus = Db::name("apply_menu")->where(["apply_plugin_id" => $apply_plugin_id])->order("sort", "asc")->select();
        $chileMenus = self::getChildMenus($columnId, $applyMenus, 0, '&nbsp;', 'id');

        //子菜单
        View::assign('chileMenus', $chileMenus);
        View::assign('clickId', $id);

        //面包屑
        $bar = Db::name('apply_menu')->find($id);
        $ams = Db::name("apply_menu")->whereIn("id", explode(",", $bar['tier']))->select();
        $breadcrumb = array();
        array_push($breadcrumb, ['id' => '42', 'title' => '应用', 'name' => '', 'url' => url("{$this->adminPath}/Apply/index") . '?columnId=42']);
        array_push($breadcrumb, ['id' => '', 'title' => '应用中心', 'name' => '', 'url' => url("{$this->adminPath}/Apply/index") . '?columnId=42']);
        if (sizeof($ams) > 0) {
            foreach ($ams as $v) {
                $url = "javascript:void(0)";
                if ($v['type'] == "C") {
                    $url = url($v["mark"] . $v["name"]) . "?columnId=" . $columnId . "&apply_plugin_id=" . $v['apply_plugin_id'] . "&id=" . $v['id'];
                }
                array_push($breadcrumb, ['id' => $v["id"], 'title' => $v["title"], 'name' => '', 'url' => $url]);
            }
        }
        if (!($bar->type == "B") && empty($type)) { //按钮
            View::assign('clickName', $bar['title']);
            if (sizeof($ams) > 1) {
                View::assign('fox_menu', "display:block");
            } else {
                if (sizeof($chileMenus) > 1) {
                    View::assign('fox_menu', "display:block");
                } else {
                    View::assign('fox_menu', "display:none");
                }
            }
        } else {
            View::assign('fox_menu', "display:none");
        }
        View::assign("breadcrumb", $breadcrumb); //面包屑

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
        // 分配菜单数据
        if ($this->getAdminId() == 1) { //超级管理员
            $auth = new AuthRule();
            $menu_data = $auth->getMenu();
        } else { //其他用户
            $auth = new Auth();
            $menu_data = $auth->getAuthList($this->getAdminId());
        }
        $menu_data = Data::channelLevel($menu_data, 0, '&nbsp;', 'id');
        return $menu_data;
    }

    // 获取子菜单
    private function getChildMenus($columnId, $data, $pid = 0, $html = "&nbsp;", $fieldPri = 'cid', $fieldPid = 'pid', $level = 1)
    {
        if (empty($data)) {
            return array();
        }
        $arr = array();
        foreach ($data as $v) {
            if (!empty($v["name"])) {
                $url = url($v["mark"] . $v["name"]) . "?columnId=" . $columnId . "&apply_plugin_id=" . $v['apply_plugin_id'] . "&id=" . $v['id'];
                $v["name"] = $url;
            }
            if ($v[$fieldPid] == $pid) {
                $arr[$v[$fieldPri]] = $v;
                $arr[$v[$fieldPri]]['_level'] = $level;
                $arr[$v[$fieldPri]]['_html'] = str_repeat($html, $level - 1);
                $arr[$v[$fieldPri]]["_data"] = self::getChildMenus($columnId, $data, $v[$fieldPri], $html, $fieldPri, $fieldPid, $level + 1);
            }
        }
        return $arr;
    }

    // 检测操作权限
    protected function checkAuth($rule_name = '')
    {
        $auth = new Auth();
        if (empty($rule_name)) $rule_name = 'admin/' . $this->request->controller() . '/' . $this->request->action();
        $rule_name = xn_uncamelize($rule_name);

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
}