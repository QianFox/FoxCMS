<?php

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\util\SitemapUtil;
use think\facade\View;

/**
 * sitemap地图
 */
class Sitemap extends AdminBase
{

    public function index()
    {

        $sitemap = xn_cfg("sitemap");
        if ($this->request->isAjax()) {
            $param = $this->request->param();

            // 定义允许的值列表
            $allowedFilters = ['hide_column', 'outer_model'];
            $allowedSitemapTypes = ['xml', 'txt', 'html'];
            $allowedFrequencies = ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'];
            $allowedLevels = ['0.1', '0.2', '0.3', '0.4', '0.5', '0.6', '0.7', '0.8', '0.9', '1.0'];

            // 验证并过滤输入
            if (key_exists("filter", $param)) {
                $filters = array_intersect(explode(",", $param['filter']), $allowedFilters);
                $sitemap['filter'] = implode(",", $filters);
            }
            if (key_exists("sitemap_type", $param)) {
                $sitemapTypes = array_intersect(explode(",", $param['sitemap_type']), $allowedSitemapTypes);
                $sitemap['sitemap_type'] = implode(",", $sitemapTypes);
            }
            if (key_exists("frequency", $param)) {
                $frequencies = array_intersect(explode(",", $param['frequency']), $allowedFrequencies);
                $sitemap['frequency'] = implode(",", $frequencies);
            }
            if (key_exists("level", $param)) {
                $levels = array_intersect(explode(",", $param['level']), $allowedLevels);
                $sitemap['level'] = implode(",", $levels);
            }

            set_php_arr(config_path('cfg'), 'sitemap.php', $sitemap);
            $this->success("操作成功");
        }
        View::assign("sitemap", $sitemap);
        //过滤
        $filters = [];
        if (!empty($sitemap['filter'])) {
            $filters = explode(",", $sitemap['filter']);
        }
        View::assign("filters", $filters);
        //sitemap类型
        $sitemapTypes = [];
        if (!empty($sitemap['sitemap_type'])) {
            $sitemapTypes = explode(",", $sitemap['sitemap_type']);
        }
        View::assign("sitemapTypes", $sitemapTypes);
        //更新频率
        $frequencys = [];
        if (!empty($sitemap['frequency'])) {
            $frequencys = explode(",", $sitemap['frequency']);
        }
        View::assign("frequencys", $frequencys);
        //优先级别
        $levels = [];
        if (!empty($sitemap['level'])) {
            $levels = explode(",", $sitemap['level']);
        }
        View::assign("levels", $levels);
        $frequencyList = [
            ['key' => 'always', 'text' => '经常'],
            ['key' => 'hourly', 'text' => '每小时'],
            ['key' => 'daily', 'text' => '每天'],
            ['key' => 'weekly', 'text' => '每周'],
            ['key' => 'monthly', 'text' => '每月'],
            ['key' => 'yearly', 'text' => '每年'],
            ['key' => 'never', 'text' => '从不']
        ]; //更新频率
        $levelList = ['0.1', '0.2', '0.3', '0.4', '0.5', '0.6', '0.7', '0.8', '0.9', '1.0']; //优先级别
        View::assign("frequencyList", $frequencyList);
        View::assign("levelList", $levelList);
        //sitemap 网站地图
        $view_suffix = config('view.view_suffix'); //模型文件后缀
        $sitemap = "/app/home/view/sitemap.{$view_suffix}";

        $autoSitemap = root_path() . 'templates/' . $this->template['template'] . "/" . $this->template['html'] . "/sitemap.{$view_suffix}";
        $autoSitemap = replaceSymbol($autoSitemap);
        if (file_exists($autoSitemap)) { //文件存在的
            $sitemap = 'templates/' . $this->template['template'] . "/" . $this->template['html'] . "/sitemap.{$view_suffix}";
            $sitemap = replaceSymbol($sitemap);
        }
        $cur_lang = $this->getMyLang();
        $home_lang = xn_cfg("base.home_lang"); //默认语言
        if ($cur_lang == $home_lang) {
            $html_url = $this->domain . "sitemap.html";
            $txt_url = $this->domain . "sitemap.txt";
            $xml_url = $this->domain . "sitemap.xml";
        } else {
            $html_url = $this->domain . $cur_lang . "/sitemap.html";
            $txt_url = $this->domain . $cur_lang . "/sitemap.txt";
            $xml_url = $this->domain . $cur_lang . "/sitemap.xml";
        }
        $sm['html_url'] = $html_url;
        $sm['txt_url'] = $txt_url;
        $sm['xml_url'] = $xml_url;
        View::assign("sm", $sm);
        View::assign("sitemap", $sitemap);
        return view();
    }

    /**
     * 更新sitemap网站地图
     */
    public function handUpdate()
    {
        $type = $this->request->param("type", "");
        if (empty($type)) {
            $this->error("缺少更新参数");
        }
        $sitemapTypes = ['xml', 'txt', 'html'];
        if (!in_array($type, $sitemapTypes)) {
            $this->error("更新网站地图参数错误");
        }
        (new SitemapUtil())->generateSitemap($type, $this->domain, $this->getMyLang());
        $this->success("更新成功");
    }
}
