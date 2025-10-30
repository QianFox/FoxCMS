<?php

namespace app\command;

use app\common\util\SitemapUtil;
use think\console\Command;
use think\console\Input;
use think\console\Output;

class Sitemap extends Command
{
    protected function configure()
    {
        $this->setName('sitemap')->setDescription("Sitemap 网站地址");
    }

    protected function execute(Input $input, Output $output)
    {
        //获取静态文件存放目录
//        $basic = getBasic();
//        $domain = "";
//        if($basic){
//            $domain = $basic['url_prefix'].$basic['url'];
//        }
//        (new SitemapUtil())->generateSitemap('all',$domain."/");

//        print_r(root_path()."\r\n".php_path());

    }
}