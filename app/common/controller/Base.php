<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   18:09
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   18:09
 */

declare(strict_types=1);

namespace app\common\controller;

error_reporting(E_ERROR);

use app\admin\util\TableUtil;
use app\common\model\Blacklist;
use think\App;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\facade\Db;
use think\facade\Session;
use think\facade\View;
use think\Response;
use think\Validate;

// 控制器基础类
abstract class Base
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
     * 后台路径
     * @var
     */
    public $adminPath;

    /**
     * 地址
     * @var
     */
    public $domain;

    /**
     * 没有http和https
     * @var
     */
    public $domainNo;

    /**
     * 后台语言标识
     * @var
     */
    public $lang;

    // 构造方法
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        $accIp = getAccessIP();
        $list = Blacklist::where(['ip' => $accIp])->select();
        if (sizeof($list) > 0) {
            $html = <<<php
            <div style="text-align: center;color: red;" class="system-message">
                    <h1>:(</h1>
            <p class="error">IP黑名单不允许访问</p>
                    <p class="detail"></p>
    </div>
php;
            die($html);
        }
        //删除安装文件
        $an = root_path() . "an.php";
        if (is_file($an)) {
            unlink($an);
        }

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {
        $staticPath = config("adminconfig.static_path");
        View::assign("staticPath", $staticPath); //全局后来路径变量

        $adminP = config("adminconfig.admin_path");
        View::assign("adminPath", $adminP); //全局后来路径变量
        $this->adminPath = $adminP;

        $this->domain = $this->request->domain() . "/";
        View::assign('domain', $this->domain);
        View::assign('nowtime', "到现在");

        $this->domainNo = $this->request->subDomain() . "." . $this->request->rootDomain();
        View::assign('domainNo', $this->domainNo);
        $this->lang = xn_cfg("base.lang");
    }

    //获取自己当前语言
    protected function getMyLang()
    {
        $my_lang = Session::get('my_lang');
        if (empty($my_lang)) {
            $my_lang = $this->lang;
            Session::set("my_lang", $my_lang);
        }
        return $my_lang;
    }

    //语言
    protected function lang()
    {

        $list = [];
        $curlang = null;
        try {
            $list = Db::name('lang')->field("lang,name")->where([['status', '=', 1]])->cache(true)->select()->toArray();
        } catch (\Exception $e) {
            $curlang = ['name' => "", 'lang' => xn_cfg("base.lang")];
        }
        $lang  = $this->getMyLang();
        $otherLangs = [];
        foreach ($list as $vo) {
            if ($vo['lang'] == $lang) {
                $curlang = $vo;
            } else {
                $otherLangs[] = $vo;
            }
        }
        View::assign('otherLangs', $otherLangs);
        View::assign('langs', $list);
        View::assign('curlang', $curlang);
    }

    //切换语言
    public function handoverLang()
    {
        $lang = $this->request->param('lang');
        $lang = trim($lang);
        if (empty($lang)) {
            $this->error("切换失败,缺少参数");
        }
        Session::set("my_lang", $lang);
        $isT = TableUtil::check_table("fox_lang");
        if ($isT) {
            $fLang = Db::name('lang')->field("lang,name,status")->where([['lang', '=', $lang]])->find();
            if ($fLang) {
                if ($fLang['status'] == 1) {
                    $this->success("切换成功", "", $fLang);
                } else {
                    $this->error("切换失败");
                }
            }
        }
        $this->success("切换成功");
    }

    // 验证数据
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }

    /**
     * 操作成功跳转的快捷方法
     * @param  mixed     $msg 提示信息
     * @param  string    $url 跳转的URL地址
     * @param  mixed     $data 返回的数据
     * @param  integer   $wait 跳转等待时间
     * @param  array     $header 发送的Header信息
     */
    protected function success($msg = '', string $url = null, $data = '', int $wait = 1, array $header = [])
    {
        if (is_null($url) && isset($_SERVER["HTTP_REFERER"])) {
            //$url = $_SERVER["HTTP_REFERER"];
            $url = '';
        } elseif ($url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : app('route')->buildUrl($url);
        }
        $result = [
            'code' => 1,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];

        $type = $this->getResponseType();
        if ($type == 'html') {
            $response = view(config('app.dispatch_success_tmpl'), $result);
        } else if ($type == 'json') {
            $response = json($result);
        }

        throw new HttpResponseException($response);
    }

    /**
     * 操作错误跳转的快捷方法
     * @param  mixed     $msg 提示信息
     * @param  string    $url 跳转的URL地址
     * @param  mixed     $data 返回的数据
     * @param  integer   $wait 跳转等待时间
     * @param  array     $header 发送的Header信息
     */
    protected function error($msg = '', string $url = null, $data = '', int $wait = 3, array $header = [])
    {
        if (is_null($url)) {
            $url = $this->request->isAjax() ? '' : 'javascript:history.back(-1);';
        } elseif ($url) {
            $url = (strpos($url, '://') || 0 === strpos($url, '/')) ? $url : $this->app->route->buildUrl($url);
        }

        $result = [
            'code' => 0,
            'msg'  => $msg,
            'data' => $data,
            'url'  => $url,
            'wait' => $wait,
        ];

        $type = $this->getResponseType();
        if ($type == 'html') {
            $response = view(config('app.dispatch_success_tmpl'), $result);
        } else if ($type == 'json') {
            $response = json($result);
        }

        throw new HttpResponseException($response);
    }

    /**
     * URL重定向  自带重定向无效
     * @param  string         $url 跳转的URL表达式
     * @param  array|integer  $params 其它URL参数
     * @param  integer        $code http code
     * @param  array          $with 隐式传参
     */
    protected function redirect($url, $params = [], $code = 302, $with = [])
    {
        $response = Response::create($url, 'redirect');

        if (is_integer($params)) {
            $code   = $params;
            $params = [];
        }

        $response->code($code)->with($params);

        throw new HttpResponseException($response);
    }

    // 获取当前的response 输出类型
    protected function getResponseType()
    {
        return $this->request->isJson() || $this->request->isAjax() ? 'json' : 'html';
    }
}