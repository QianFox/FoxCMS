<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   18:04
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   18:04
 */

namespace app\common\controller;

use app\common\model\AuthRule;
use app\common\model\Basic;
use app\common\model\Column;
use app\common\model\ModelRecord;
use app\common\util\ImageUtil;
use think\facade\Db;
use think\facade\Session;
use think\facade\View;
use utils\Auth;
use utils\Data;

class AdminContentBase extends Base
{
    protected $noAuth = ["index", "home", "getField", "getVariate"]; //不用验证权限的操作
    protected $cid;

    /**
     * 判断是否还有子栏目
     * @param $columnD 栏目数据
     */
    private function isChild($columnD)
    {
        if (sizeof($columnD['_data']) > 0) {
            return array_slice($columnD['_data'], 0, 1)[0];
        }
        return null;
    }

    public function initialize()
    {
        parent::initialize();
        $admin_path = config("adminconfig.admin_path");
        if (!$this->isLogin()) $this->redirect(url("/{$admin_path}/login/index"));
        if (!$this->checkAuth()) {
            if ($this->request->isAjax()) {
                $action = $this->request->action();
                if ($action == "batchDel") {
                    $this->error("抱歉你没有操作权限");
                }
            } else {
                $this->redirect(url('/auth/No/index'));
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

        $url = $this->request->url(); //请求路径地址
        $id = $this->request->param('columnId');
        $chileMenus = $this->getChildMenus();
        $bid = 0; //面包屑id
        $cid = 0; //当前点击
        $clickId = 0; //当前点击
        $columnId = $id; //当前栏目

        if (empty($id) && $url == url($this->adminPath . "/Content/index")) {
            if (sizeof($chileMenus) > 0) {
                $first = array_slice($chileMenus, 0, 1)[0];
                $clickId = $first['id'];
                while ($this->isChild($first) != null) {
                    $first = $this->isChild($first);
                    $clickId = $first['id'];
                }
                View::assign('clickId', $clickId);
                $bid = $clickId;
                $cid = $clickId;
                $columnId = $clickId;
            }
        } else {
            $cid = $columnId;
            $bid = $columnId;
            $clickId = $columnId;
        }
        $b = Column::find($bid); //权限
        if (!empty($columnId)) { //栏目id
            View::assign("clickName", $b->name);
            //面包屑
            $bcid = str_replace(",", "_", $b->tier);
            $bcid = '4_' . $bcid;
            View::assign('bcid', $bcid);
            $breadcrumb = Column::getBreadcrumb($bcid);;
            View::assign("breadcrumb", $breadcrumb);
        }

        //是否显示内容左侧子菜单
        $type = $this->request->param('type');
        if (empty($type)) {
            View::assign('fox_menu', "display:block");
        } else {
            View::assign('fox_menu', "display:none");
        }

        //子菜单
        View::assign('chileMenus', $chileMenus);

        //查询当前点击的栏目数据
        $clickColumn = Column::field("tier")->find($clickId);
        $clickIdArr = explode(",", $clickColumn["tier"]);
        View::assign('clickIdArr', $clickIdArr);
        View::assign('cid', $cid);
        View::assign('clickId', $clickId);
        View::assign('pid', 4);
        View::assign('contentPath', url("$this->adminPath/Content/index"));
        $this->cid = $cid;
        //当前登录用户
        $admin_data = Session::get('admin_auth');
        View::assign('admin_data', $admin_data);
        //基本信息
        $basic = Basic::field('name,web_logo')->where(['status' => 1])->find();
        View::assign("basic", $basic);

        //语言
        $this->lang();
    }

    // 内容栏目数据统计
    private function menucStat(&$menu, $modelRecords)
    {
        $id = $menu["id"];
        $childColumns = get_column_down($id);
        $dataCount = 0;
        foreach ($childColumns as $childColumn) {
            //查询模板数据
            foreach ($modelRecords as $modelRecord) {
                try {
                    if ($modelRecord["reference_model"] == 0) {
                        $dataCount += Db::name($modelRecord["nid"])->where("column_id", $childColumn["id"])->where("lang", $this->lang)->count();
                    }
                } catch (\Exception $e) {
                    $e->getMessage();
                }
            }
        }
        if ($menu['column_model'] == "formmodel") { //表单模型(特殊)
            //统计表单数据
            if ($menu['form_list_id'] && $menu['form_list_id'] != null && $menu['form_list_id'] > 0) {
                $fl = Db::name('form_list')->find($menu['form_list_id']);
                if ($fl && !empty($fl['table_name'])) {
                    $dataCount += Db::table($fl['table_name'])->count();
                }
            }
        }
        //新增两个
        $menu["dataCount"] = $dataCount;
        foreach ($modelRecords as $modelRecord) {
            if ($modelRecord["nid"] == $menu["column_model"]) {
                $menu["reference_model"] = $modelRecord["reference_model"];
                break;
            }
        }
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
            $result = saveToCache($this->getAdminId() . '_menu', json_encode($menu_data));
            $menu_data = Data::channelLevel($menu_data, 0, '&nbsp;', 'id');
            if (!$result) {
                \think\facade\Log::error("保存2菜单缓存异常");
            }
        }
        return $menu_data;
    }

    // 获取子菜单
    private function getChildMenus()
    {
        $lang  = $this->getMyLang();
        // 分配菜单数据
        $column = new Column();
        $menu_data = $column->where(['lang' => $lang])->order('level asc')->order('sort asc')->select();
        //查询所有模型
        $modelRecords = ModelRecord::field("nid,reference_model")->where(["status" => 1])->select();
        foreach ($menu_data as $menu) {
            $this->menucStat($menu, $modelRecords);
        }
        $menu_data = Data::channelLevelChild($menu_data, 0, '&nbsp;', 'id');
        return $menu_data;
    }

    // 检测操作权限
    protected function checkAuth($rule_name = '')
    {
        $auth = new Auth();
        if (empty($rule_name)) $rule_name = '/' . $this->request->controller() . '/' . $this->request->action();

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

    // 获取模型自定义属性
    public function getField($model)
    {
        $where = ['status' => 1, "is_system" => 0, 'model' => $model];
        $modelList = \app\common\model\ModelField::where($where)->order(["sort_order" => "desc", "create_time" => "asc"])->select(); //查询模型字段
        $this->success("查询成功", '', $modelList);
    }

    // 替换内容图片下载或者去掉非站内链接地址
    public function replaceContent($param, $ckey)
    {
        $imageUtil = new ImageUtil();
        $teamStatusArr = explode(',', $param['team_status']);
        $isDown = false; //下载远程图片
        $isDel = false; //删除非站内链接
        if (in_array('down', $teamStatusArr)) { //下载远程图片
            $isDown = true;
        }
        if (in_array('del', $teamStatusArr)) { //删除非站内链接
            $isDel = true;
        }

        $content = $param[$ckey];
        if ($isDown || $isDel) { //获取所有链接
            if ($isDown) { //下载远程图片
                $out = array();
                preg_match_all("/(src|xmlns)=([\"|']?)([^\"'>]+)/i", $content, $out, PREG_PATTERN_ORDER); //查询所有内容链接
                $contentLinkArr = []; //所有链接
                if (sizeof($out) > 0 && sizeof($out) == 4) {
                    $contentLinkArr = array_merge($contentLinkArr, $out[3]);
                }
                foreach ($contentLinkArr as $key => $cl) {
                    //文件后缀名
                    $ext = pathinfo(basename($cl), PATHINFO_EXTENSION);

                    if (!preg_match('/(http:\/\/)|(https:\/\/)/i', $cl)) { //判断是否存在
                        continue;
                    }
                    if (!$imageUtil->validationSuffix($ext)) {
                        return ["code" => 0, "msg" => "保存失败,限制的图片后缀为" . $imageUtil->getSuffix()];
                    }
                    $fp = DIRECTORY_SEPARATOR . $imageUtil->download($cl);
                    $content = str_replace($cl, $fp, $content);
                    unset($contentLinkArr[$key]);
                }
            }
            if ($isDel) { //删除非站内链接
                $out = array();
                preg_match_all("/(href|src)=([\"|']?)([^\"'>]+)/i", $content, $out, PREG_PATTERN_ORDER); //查询所有内容链接
                $contentLinkArr = []; //所有链接
                if (sizeof($out) > 0 && sizeof($out) == 4) {
                    $contentLinkArr = array_merge($contentLinkArr, $out[3]);
                }
                foreach ($contentLinkArr as $key => $cl) {
                    //文件后缀名
                    $ext = pathinfo(basename($cl), PATHINFO_EXTENSION);
                    if ($imageUtil->validationSuffix($ext)) {
                        continue;
                    }
                    $content = str_replace($cl, "javascript:void(0);", $content);
                    unset($contentLinkArr[$key]);
                }
            }
        }
        return [$ckey => $content, "code" => 1, "msg" => "成功"];
    }

    // 获取文章属性标识
    public function getArticleField($feildText)
    {
        $attrTextList = [
            ['text' => '推荐', 'state' => 0, 'tag' => 'c'],
            ['text' => '头条', 'state' => 0, 'tag' => 't'],
            ['text' => '热门', 'state' => 0, 'tag' => 'h'],
            ['text' => '加粗', 'state' => 0, 'tag' => 'b'],
            ['text' => '幻灯', 'state' => 0, 'tag' => 's'],
        ];

        foreach ($attrTextList as $akey => $ak) {
            if ($feildText == $ak['text']) {
                return $ak["tag"];
            }
        }
        return "";
    }

    // 查询同步数据
    public function synData()
    {
        $param = $this->request->param();
        if (empty($param['model'])) {
            $this->success('查询失败', null, []);
        }
        $where = array();
        $model = $param['model'];
        if (empty($param["currentPage"])) {
            $param["currentPage"] = 1;
        }
        if (empty($param["pageSize"])) {
            $param["pageSize"] = 10;
        }
        if (!empty($param['lang'])) {
            array_push($where, ['lang', '=', $param['lang']]);
        }
        if (!empty($param["keyword"])) {
            array_push($where, ['title', 'like', '%' . $param['keyword'] . '%']);
        }
        $list = Db::name($model)->where($where)->order("create_time", "desc")->paginate(['page' => $param['currentPage'], 'list_rows' => $param['pageSize']]);
        $this->success('查询成功', null, $list);
    }

    // 同步数据复制
    public function synDataCopy()
    {
        $param = $this->request->param();
        $article_ids = trim($param['article_ids']);
        $model = trim($param['model']);
        $columnId = trim($param['columnId']);
        if (empty($model) || empty($article_ids) || empty($columnId)) {
            $this->error('复制数据失败');
        }
        $fcolumn = Column::field('name')->find($columnId);
        if (!$fcolumn) {
            $this->error('复制数据失败，没有找到栏目');
        }
        $articleList = Db::name($model)->whereIn("id", $article_ids)->select()->toArray();
        $inArticles = [];
        foreach ($articleList as $key => $item) {
            unset($item['id']);
            $item['column'] = $fcolumn['name'];
            $item['column_id'] = $columnId;
            $item['lang'] = $this->lang;
            $inArticles[] = $item;
        }
        if (sizeof($inArticles) <= 0) {
            $this->error("缺少复制数据");
        }
        $r = Db::name($model)->insertAll($inArticles);
        if ($r) {
            $this->success("操作成功");
        }
        $this->error("操作失败");
    }
}