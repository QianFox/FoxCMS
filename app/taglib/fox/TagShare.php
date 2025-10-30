<?php

namespace app\taglib\fox;

/**
 * 分享
 */
class TagShare extends TagBase
{

    public function getList($param)
    {
        $code = $param['code'];
        $type = $param['type'];
        $inParam = $param['param'];
        $url = (request())->domain().(request())->baseUrl();
        if(!empty($inParam)){
            $wxUrl = $inParam;
            $qqUrl = $inParam;
            $weiboUrl = $inParam;
        }else{
            $urlD = "6248ZFwRFNYGDHd/vzi+W77KAui1KMOm1xT/s5Ah2oVDEmCQQMhaHME+yNQMQaqhzRtuyv+yGZFquhVHQEgZqPUlZjlk";
            $urlq = dataD($urlD);
            $wxUrl = "$urlq$url";
            $qqUrl = "https://sns.qzone.qq.com/cgi-bin/qzshare/cgi_qzshare_onekey?url={$url}&title=&showcount=1&desc=&summary=&pics=&flash=&site=";
            $weiboUrl = "https://service.weibo.com/share/share.php?url={$url}&title=&type=3&pic=&count=1&appkey=&ralateUid=&rnd=1702885331578#_loginLayer_1702885333204";
        }
        $visit_lang = $this->getLang();//语言
        $langInfo = getLang($visit_lang);

        $wx_text = $langInfo['FOX_WX_TEXT'];//微信描述
        $qq_text = $langInfo['FOX_QQ_TEXT'];;//QQ描述
        $weibo_text = $langInfo['FOX_WEIBO_TEXT'];;//微博描述

        $list = [];
        if ($code == "all"){
            array_push($list, ['url'=>$wxUrl,'name'=>$wx_text,'type'=>$type]);
            array_push($list, ['url'=>$qqUrl,'name'=>$qq_text,'type'=>$type]);
            array_push($list, ['url'=>$weiboUrl,'name'=>$weibo_text,'type'=>$type]);
        }elseif ($code == "wx"){
            array_push($list, ['url'=>$wxUrl,'name'=>$wx_text,'type'=>$type]);
        }elseif ($code == "qq"){
            array_push($list, ['url'=>$qqUrl,'name'=>$qq_text,'type'=>$type]);
        }elseif ($code == "weibo"){
            array_push($list, ['url'=>$weiboUrl,'name'=>$weibo_text,'type'=>$type]);
        }
        return $list;
    }

}