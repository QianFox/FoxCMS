<?php

namespace app\common\util;

class VeriCode
{
    private static function _getCodeConfig(){
        return[
//          '验证码字符集'
          'codeStr'  =>'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890',
            //验证码格式
            'codeCount'=>4,
            //字体大小
            'fontsize'=>16,
            //验证码宽度
            'width'=>100,
            //验证码高度
            'height'=>36,
            //是否有干扰点？true有，false没有
            'disturbPoint'=>true,
            //干扰点格式disturbPoint开启后生效
            'pointCount'=>200,
            //是否有干扰条？true有，false没有
            'disturbLine'=>ture,
            //干扰条个数，disturbLine开启后生效
            'lineCount'=>3
        ];
    }

    public static function create(){
        //配置
        $config = self::_getCodeConfig();

        //创建花呗
        $image= imagecreatetruecolor($config['width'], $config['height']);
        //背景颜色
        $bgcolor=imagecolorallocate($image, 255,255,255);
        imagefill($image, 0, 0, $bgcolor);
        $captch_code = '';
        $captchCodeArr = str_split($config['codeStr']);

        //随机获取n个候选字符
        for($i=0; $i<$config['codeCount']; $i++){
            $fontSize = $config['fontSize'];
            $fontColor = imagecolorallocate($image, rand(0,120), rand(0,120), rand(0,120));
            $fontContent = $captchCodeArr[rand(0, strlen($config['codeStr']) - 1)];
            $captch_code .= $fontContent;
            $_x = $config['width']/$config['codeCount'];
            $x = ($i*(int)$_x) + rand(5, 10);//随机坐标
            $y = rand(5, 10);
            imagestring($image, $fontSize, $x, $y, $fontContent, $fontColor);
        }
        session_start();
        $_SESSION['code'] = $captch_code;

        //增加干扰点
        if($config['disturbPoint']){
            for ($i=0; $i<$config['pointCount']; $i++){
                $pointColor = imagecolorallocate($image, rand(50, 200), rand(50, 200), rand(50, 200));
                imagesetpixel($image, rand(1, 99), rand(1, 99), $pointColor);
            }
        }

        //增加干扰线
        if ($config['disturbLine']){
            for ($i=0; $i<$config['lineCount']; $i++){
                $lineColor = imagecolorallocate($image, rand(80, 280), rand(80, 220), rand(80, 220));
                imageline($image, rand(1, 99), rand(1, 99), rand(1, 99),rand(1, 99), $lineColor);
            }
        }

        //输出格式
        header('content-type:image/png');
        imagepng($image);

        //销毁图片
        imagedestroy($image);
    }

}