<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   18:17
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   18:17
 */

namespace app\common\lib;

use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;

// 七牛云
class Qiniu
{
    private $ak = '';
    private $sk = '';
    private $bucket = '';
    private $domain = '';
    public function __construct()
    {
        $config = xn_cfg('upload');
        empty($this->ak) && $this->ak = $config['qiniu_ak'];
        empty($this->sk) &&  $this->sk = $config['qiniu_sk'];
        empty($this->bucket) &&  $this->bucket = $config['qiniu_bucket'];
        empty($this->domain) &&  $this->domain = $config['qiniu_domain'];
    }

    // 上传文件
    public function putFile($key, $filePath)
    {
        $token = $this->getToken();
        $uploadMgr = new UploadManager();
        list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
        if ($err !== null) {
            return false;
        } else {
            return $this->domain . $ret['key'];
        }
    }

    // 删除远程文件
    public function delete($key)
    {
        $auth = new Auth($this->ak, $this->sk);
        $config = new Config();
        $bucketManager = new BucketManager($auth, $config);
        $bucketManager->delete($this->bucket, $key);
    }

    // 获取token
    protected function getToken()
    {
        $auth = new Auth($this->ak, $this->sk);
        $token = $auth->uploadToken($this->bucket);
        return $token;
    }
}