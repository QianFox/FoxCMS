<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   15:04
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   15:04
 */

namespace app\admin\controller;

use app\common\controller\Base;
use app\common\model\Admin;
use think\captcha\facade\Captcha;
use think\exception\ValidateException;
use think\facade\Session;
use think\facade\View;

class Login extends Base
{
    private $errNum = 5; //错误登录数
    private $lockTime = 2 * 60; //锁两个小时

    private function surplusTime($timeArr)
    {
        $surplusTime = "";
        if ($timeArr['hours'] > 0) {
            $surplusTime .= "{$timeArr['hours']}小时";
        }
        if ($timeArr['minutes'] > 0) {
            $surplusTime .= "{$timeArr['minutes']}分钟";
        }
        if ($timeArr['seconds'] > 0) {
            $surplusTime .= "{$timeArr['seconds']}秒";
        }
        return $surplusTime;
    }

    public function index()
    {
        if ($this->request->isPost()) {
            $param = $this->request->param();
            try {
                $this->validate($param, 'login');
            } catch (ValidateException $e) {
                $this->error($e->getError());
            }

            //是否开启验证码
            if (xn_cfg('base.login_vercode') == 1) {
                if (!captcha_check($param['vercode'])) {
                    $this->error('验证码错误');
                }
            }
            //拦截锁定ip和账号
            $ip = request()->ip();
            $path = root_path() . "data";
            $nowTime = time();
            $unLock = false; //释放
            if (file_exists("{$path}/login.lock")) { //拦截ip
                $loginLockStr = file_get_contents("{$path}/login.lock");
                $llContentArr = json_decode($loginLockStr, true);
                if (sizeof($llContentArr) > 0) {
                    $llContentArrNew = []; //重新新保
                    $timeArr = [];
                    foreach ($llContentArr as $k => $llContent) {
                        if ($llContent['ip'] == $ip) {
                            $lockTimeStr = $llContent['lockTime'];
                            $lockTime = strtotime($lockTimeStr); //锁时间
                            if ($nowTime > $lockTime) { //过了锁的时间
                                $unLock = true;
                            } else {
                                $timeArr = time_diff($nowTime, $lockTime);
                            }
                            break;
                        } else {
                            array_push($llContentArrNew, $llContent);
                        }
                    }
                    if ($unLock) {
                        file_put_contents("{$path}/login.lock", json_encode($llContentArrNew));
                        unlink("{$path}/{$ip}.lock"); //删除
                    } else {
                        $surplusTime = $this->surplusTime($timeArr);
                        $this->error("您多次登录失败,暂时锁定{$this->lockTime}分钟,<br/>请等待{$surplusTime}后重试!");
                    }
                }
            }
            //拦截账号
            $findAdmin = Admin::field('lock_time')->where(['username' => $param['username']])->find();
            if ($findAdmin) {
                $lockTime = strtotime($findAdmin['lock_time']); //锁时间
                if (!($nowTime > $lockTime)) { //过了锁的时间
                    $timeArr = time_diff($nowTime, $lockTime);
                    $surplusTime = $this->surplusTime($timeArr);
                    $this->error("您多次登录失败,暂时锁定{$this->lockTime}分钟,<br/>请等待{$surplusTime}后重试!");
                }
                $admin_data = Admin::where([
                    'username' => $param['username'],
                    'password' => xn_encrypt($param['password']),
                ])->field('id,username,status,last_login_ip,last_login_time,nickname,lock_time')->find();
            } else {
                $admin_data = null;
            }
            if (empty($admin_data)) {
                //登录错误处理
                $lockFile = "{$path}/{$ip}.lock";
                if (file_exists($lockFile)) { //锁文件存在
                    $content = file_get_contents($lockFile);
                    $contentArr = json_decode($content, true);
                    $errCount = $contentArr['errCount'];
                    if ($errCount < $this->errNum) { //运行错误次数氛围内
                        $errCount++;
                        $contentArr['errCount'] = $errCount;
                        $accounts = $contentArr['accounts'];
                        if (!in_array($param['username'], $accounts)) {
                            array_push($accounts, $param['username']);
                            $contentArr['accounts'] = $accounts;
                        }
                    }
                } else {
                    $errCount = 1;
                    $accounts = ["{$param['username']}"];
                    $contentArr = [
                        'errCount' => $errCount,
                        'accounts' => $accounts,
                    ];
                }
                $content = json_encode($contentArr);
                file_put_contents($lockFile, $content);
                $y = ($this->errNum - $errCount);
                if ($y < 0) $y = 0;
                if ($y == 0) { //锁ip、锁账号
                    //锁ip
                    $loginLock = "{$path}/login.lock";
                    $llContentArr = [];
                    if (file_exists($loginLock)) {
                        $loginLockStr = file_get_contents($loginLock);
                        $llContentArr = json_decode($loginLockStr, true);
                    }
                    $lockTime = date("Y-m-d H:i:s", time()  + $this->lockTime * 60);
                    array_push($llContentArr, ['ip' => $ip, "lockTime" => $lockTime]);
                    $llContent = json_encode($llContentArr);
                    file_put_contents($loginLock, $llContent);
                    //锁账号
                    $accountsL = $contentArr['accounts'];
                    Admin::whereIn("username", implode(",", $accountsL))->update(['lock_time' => $lockTime]);
                    unlink("{$path}/{$ip}.lock"); //删除
                    $this->error("您多次登录失败,暂时锁定{$this->lockTime}分钟");
                } else {
                    $this->error("用户名或密码错误,您还可以尝试[{$y}]次!");
                }
            } else {
                //释放
                Admin::update(['id' => $admin_data['id'], 'lock_time' => null]);
                unlink("{$path}/{$ip}.lock"); //删除
                if (file_exists("{$path}/login.lock")) { //拦截ip
                    $loginLockStr = file_get_contents("{$path}/login.lock");
                    $llContentArr = json_decode($loginLockStr, true);
                    if (sizeof($llContentArr) > 0) {
                        $unLock = false;
                        $llContentArrNew = [];
                        foreach ($llContentArr as $k => $llContent) {
                            if ($llContent['ip'] == $ip) {
                                $unLock = true;
                                break;
                            } else {
                                array_push($llContentArrNew, $llContent);
                            }
                        }
                        if ($unLock) {
                            file_put_contents("{$path}/login.lock", json_encode($llContentArrNew));
                        }
                    } else {
                        unlink("{$path}/login.lock"); //删除
                    }
                }
            }
            if ($admin_data['status'] != 1) {
                $this->error('您的账户已被禁用');
            }
            Session::set('admin_auth', $admin_data);
            xn_add_admin_log('后台登录', "login");
            Session::set('my_lang', $this->lang);
            $this->success('登录成功', '', $admin_data);
        }
        //        $basic = Basic::field('name,web_logo,login_logo,name,description')->where(['status'=>1])->find();
        //        View::assign("basic", $basic);
        $inp = dataD(config('adminconfig.install'), "foxcms");
        View::assign("inp", $inp);
        return view('login');
    }

    public function logout()
    {
        Session::set('admin_auth', null);
        //清除缓存
        $dir = $this->app->getRuntimePath();
        delDir($dir);
        $url = url("/" . $this->adminPath . "/login");
        $this->redirect($url);
    }

    // 清除缓存
    public function clearCache()
    {
        //删除目录
        $dir =  $this->app->getRuntimePath();;
        delDir($dir);
        $this->success("清除成功");
    }

    // 生成验证码
    public function verify()
    {
        return Captcha::create();
    }

    // 重命名安装目录，提高网站安全性
    public function renameInstall()
    {
        $oldInstallPath = root_path() . "install"; // 需要被重命名的文件夹路径
        $newInstallPath = "{$oldInstallPath}_foxcms" . strtolower(createNonceStr(4)); // 重命名后的文件夹路径
        if (is_dir($oldInstallPath)) {
            rename($oldInstallPath, $newInstallPath);
            $this->success("成功");
        } else {
            $this->error("安装目录不存在");
        }
    }
}