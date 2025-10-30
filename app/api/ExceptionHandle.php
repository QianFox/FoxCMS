<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   17:56
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   17:56
 */

namespace app\api;

use app\api\exception\HttpRespException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;
use Throwable;

// 应用异常处理类
class ExceptionHandle extends Handle
{
    // 不需要记录信息（日志）的异常类列表
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];

    // 记录异常信息（包括日志或者其它方式记录）
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    public function render($request, Throwable $e): Response
    {
        // 添加自定义异常处理机制
        if ($e instanceof HttpRespException) { //自定义响应返回
        } else { //系统所有异常返回
            //            return json(['msg'=>'系统错误', "code"=>0]);//暂时注释掉
            return json(['msg' => $e->getMessage(), "code" => 0]); //暂时注释掉
        }
        // 其他错误交给系统处理
        return parent::render($request, $e);
    }
}