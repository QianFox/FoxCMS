<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   18:11
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   18:11
 */

namespace app\common\controller;

use think\facade\Db;
use think\facade\View;

class IndexBase extends Base
{
    protected $appName;        // 当前应用名称
    protected $controllerName; // 当前控制器名
    protected $template;       // 模板目录
    protected $templateHtml;       // 模板HTML目录
    protected $templateType;       // 模板类型
    private  $limitTime = 60; //限制时间单位秒
    private $limitCount = 10; //一分钟内最对请求10次
    private $home_lang;
    // 初始化
    protected function initialize()
    {
        $accessIp = getAccessIP();
        $t = time();
        $curT =  date("Y-m-d H:i:s", $t);
        $beforeT =  date("Y-m-d H:i:s", $t - ($this->limitTime));
        $count = Db::name('access_stat')->where(['ip' => $accessIp])->whereTime("create_time", '>=', $beforeT)->whereTime("create_time", '<=', $curT)->count();
        if ($count > $this->limitCount) { //限制次数
            require_once('./static/405/405.html');
            exit();
        }
        // 查找所有系统设置表数据
        $template = \app\common\model\Template::where('run_status', 1)->find();
        $this->templateType = $template["type"];
        $this->appName        = app('http')->getName();
        $this->controllerName = $this->request->controller();
        $this->template       = $template;
        $templatePath = "templates" . DIRECTORY_SEPARATOR . $template['template'] . DIRECTORY_SEPARATOR . $template['html'] . DIRECTORY_SEPARATOR;
        $this->templateHtml   = replaceSymbol(root_path() . $templatePath);
        $url_model = xn_cfg("seo.url_model");
        View::assign("url_model", $url_model);
        $this->domainNo = $this->domainNo ?? ($this->request->server('HTTP_HOST'));
        $this->home_lang = xn_cfg("base.home_lang");
    }

    // 获取手机模型
    public function mobileHtml($tempHtml, $view_suffix)
    {
        $num = stripos($tempHtml, ".");
        $tempHtml = mb_substr($tempHtml, 0, $num) . "_m." . $view_suffix;
        return $tempHtml;
    }

    //栏目处理
    protected function columnHandle($column)
    {
        //增加栏目链接
        $url = "javascript:void(0)";
        if ($column["column_attr"] == 0) {
            $url =  resetUrl($column['v_path'], $column["id"]);
        } elseif ($column["column_attr"] == 1) { //外部链接
            $url = $column['out_link_head'] . $column['out_link'];
        } elseif ($column["column_attr"] == 2) { //内链栏目
            $inner_column = $column['inner_column'];
            if (!empty($column['inner_column'])) {
                $columnInner = \app\common\model\Column::find($inner_column);
                if ($columnInner) {
                    $vPath = $columnInner["v_path"];
                    $url = resetUrl($vPath, $inner_column);
                }
            }
        }
        $url = replaceSymbol($url);
        $column['link'] = $url;
        return $column;
    }
}