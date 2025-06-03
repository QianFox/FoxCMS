<?php
/*
 * @Descripttion : QianFox让数字化营销更简单
 * @Author       : QianFox Team
 * @Date         : 2024-12-05 11:01:21
 * @Version      : V1.24
 * @Copyright    : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditors  : QianFox Team
 * @LastEditTime : 2024-12-10 10:21:39
 */

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
use think\facade\View;

class Tags extends IndexBase
{
    protected $view_suffix; // 文件后缀

    // 初始化
    protected function initialize()
    {
        parent::initialize();
        $id = $this->request->param("id");
        if (is_numeric($id)) { // 确保ID是数字
            $Tag = \app\common\model\Tag::find($id);
            View::assign("tag", $Tag);
        }
        $this->view_suffix = config('view.view_suffix');
    }

    public function index()
    {
        // 获取请求参数并进行转义处理
        $param = $this->request->param();

        // 清理并转义用户输入
        foreach ($param as $key => $value) {
            // 移除 <, >, ", ; 这些可能用于注入恶意脚本的字符
            $value = str_replace(['<', '>', '"', ';'], '', $value);
            // 使用 addslashes 对单引号、双引号、反斜杠和 NULL 字符进行转义
            $value = addslashes($value);
            // 使用 htmlspecialchars 对特殊字符进行 HTML 转义
            $param[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8', false);
        }

        // 将经过处理的参数赋值给视图
        View::assign("taglist", $param);

        $tempHtml = "taglist.{$this->view_suffix}";
        if (($this->templateType == 2 || $this->templateType == 3) && is_mobile()) { // 判断是否手机访问
            $tempHtmlMobile = $this->mobileHtml($tempHtml, $this->view_suffix);
            $tempHtmlMobilePath =  $this->templateHtml . $tempHtmlMobile;
            if (file_exists($tempHtmlMobilePath)) { // 判断文件是否存在
                $tempHtml = $tempHtmlMobile;
            }
        }
        $template = $this->templateHtml . $tempHtml;
        $content = View::fetch($template);
        return access_stat_js($content, $this->domainNo);
    }

    // 标签详情
    public function list()
    {
        $model_template = "list_tags.{$this->view_suffix}";
        if (($this->templateType == 2 || $this->templateType == 3) && is_mobile()) { // 判断是否手机访问
            $model_templateMobile = $this->mobileHtml($model_template, $this->view_suffix);
            $model_templateMobilePath =  $this->templateHtml . $model_templateMobile;
            if (file_exists($model_templateMobilePath)) { // 判断文件是否存在
                $model_template = $model_templateMobile;
            }
        }
        $template = $this->templateHtml . $model_template;
        $content = View::fetch($template);
        return access_stat_js($content, $this->domainNo);
    }
}