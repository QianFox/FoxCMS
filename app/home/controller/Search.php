<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   19:20
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   19:20
 */

namespace app\home\controller;

use app\common\controller\IndexBase;
use app\taglib\fox\TagBase;
use think\facade\View;

// 搜索控制器
class Search extends IndexBase
{
    protected $view_suffix; // 文件后缀

    // 初始化
    protected function initialize()
    {
        parent::initialize();
        $this->view_suffix = config('view.view_suffix');
    }

    public function index()
    {
        $param = $this->request->param();

        // 清理并转义用户输入
        foreach ($param as $key => $value) {
            // 移除 <, >, ", ; 这些可能用于注入恶意脚本的字符
            $value = str_replace(['<', '>', '"', ';'], '', $value);
            // 使用 addslashes 对单引号、双引号、反斜杠和 NULL 字符进行转义
            $value = addslashes($value);
            // 使用 htmlspecialchars 对特殊字符进行 HTML 转义
            $param[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
        View::assign("search", $param);

        $id = $param["id"] ?? "";
        $lang = $param["lang"] ?? "";
        $model = $param["model"] ?? "";
        $sfeilds = ["lang" => $lang, "model" => $model];

        // 获取搜索词
        $pv = (new TagBase())->getSearch($this->request, 1);
        if (sizeof($pv) > 0) {
            $sfeilds["keyword"] = implode(",", $pv);
        }

        $templet = $param["templet"] ?? "";
        $templetName = "search";
        if (!empty($templet)) {
            $templetName = $templet;
        }
        $template = $this->templateHtml . $templetName . "." . $this->view_suffix;
        $template = replaceSymbol(replaceSymbol($template));
        $content = View::fetch($template);
        return access_stat_js($content, $this->domainNo);
    }
}
