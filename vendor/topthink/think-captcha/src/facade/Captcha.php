<?php
namespace think\captcha\facade;
use think\Facade;
/**
 * Class Captcha
 */
class Captcha extends Facade
{
    protected static function getFacadeClass()
    {
        return \think\captcha\Captcha::class;
    }
}
