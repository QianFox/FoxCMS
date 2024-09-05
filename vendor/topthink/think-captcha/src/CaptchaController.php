<?php
namespace think\captcha;
class CaptchaController
{
    public function index(Captcha $captcha, $config = null)
    {
        return $captcha->create($config);
    }
}
