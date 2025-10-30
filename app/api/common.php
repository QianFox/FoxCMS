<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   17:55
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   17:55
 */

use app\api\exception\HttpRespException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use think\Response;

// 成功
function success($msg = '', string $url = null, $data = '')
{
    $result = [
        'code' => 1,
        'msg'  => $msg,
        'data' => $data
    ];
    throw new HttpRespException(Response::create($result, 'json'));
}

// 错误
function error($msg = '', $data = '', $code = 0)
{
    $result = [
        'code' => $code,
        'msg'  => $msg,
        'data' => $data,
    ];
    throw new HttpRespException(Response::create($result, 'json'));
}

// 生成验签
function signToken($data)
{
    $key = config("jwt.key");         //自定义的一个随机字串用户于加密中常用的 盐  salt
    $token = array(
        "iss" => config("jwt.iss"),      //签发者 可以为空
        "aud" => '',                     //面象的用户，可以为空
        "iat" => config("jwt.iat"),      //签发时间
        "nbf" => config("jwt.nbf"),      //在什么时候jwt开始生效
        "exp" => config("jwt.exp"),      //token 过期时间
        "data" => $data
    );
    $jwt = JWT::encode($token, $key, config("jwt.alg"));  //生成了 token
    return $jwt;
}

// 验证token
function checkToken($token)
{
    $key = config("jwt.key");         //自定义的一个随机字串用户于加密中常用的 盐  salt
    $res['status'] = false;
    try {
        JWT::$leeway    = 60; //当前时间减去60，把时间留点余地
        $decoded        = JWT::decode($token, new Key($key, 'HS256')); //HS256方式，这里要和签发的时候对应
        $arr            = (array)$decoded;
        $res['status']  = true;
        $res['info']    = "解析token成功";
        $res['data']    = (array)$arr['data'];
        return $res;
    } catch (\Firebase\JWT\SignatureInvalidException $e) { //签名不正确
        error("签名不正确", "", config("code.token_error"));
    } catch (\Firebase\JWT\BeforeValidException $e) { // 签名在某个时间点之后才能用
        error("token失效");
    } catch (\Firebase\JWT\ExpiredException $e) { // token过期
        error("token过期", "", config("code.token_error"));
    } catch (Exception $e) { //其他错误
        error("未知错误", "", config("code.token_error"));
    }
}