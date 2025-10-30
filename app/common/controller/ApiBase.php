<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   18:07
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   18:07
 */

declare(strict_types=1);

namespace app\common\controller;

error_reporting(E_ERROR);

use think\App;
use think\exception\HttpResponseException;
use think\Validate;

// 控制器基础类
abstract class ApiBase
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

    // 操作成功
    protected function success($msg = '', string $url = null, $data = '')
    {
        $result = [
            'code' => 1,
            'msg'  => $msg,
            'data' => $data
        ];
        $response = json($result);
        throw new HttpResponseException($response);
    }

    // 操作错误
    protected function error($msg = '', $data = '', $code = 0)
    {
        $result = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ];
        $response = json($result);
        throw new HttpResponseException($response);
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
}