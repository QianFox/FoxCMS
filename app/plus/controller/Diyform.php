<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   19:24
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   19:24
 */

namespace app\plus\controller;

use app\common\controller\ApiBase;
use app\common\model\FormField;
use app\common\model\FormList;
use PHPMailer\PHPMailer\PHPMailer;
use think\captcha\facade\Captcha;
use think\facade\Db;
use think\Response;

class Diyform extends ApiBase
{
    private  $limitTime = 5; //限制时间单位分钟

function receive()
{
    $param = $this->request->param();
    $locationHref = getFromPage();

    if ($this->request->isPost()) {
        $id = $param["id"];
        if (empty($id)) {
            $content = "<html><body><script>alert('提交失败'); window.location.href='" . htmlspecialchars($locationHref, ENT_QUOTES, 'UTF-8') . "';</script></body></html>";
            $type = "html";
            return Response::create($content, $type, 0);
        } else {
            $formList = FormList::find($id);
            if ($formList['verify'] == 1) { //开启
                if (empty($param['vercode'])) {
                    $content = "<html><body><script>alert('验证码不能空'); window.location.href='" . htmlspecialchars($locationHref, ENT_QUOTES, 'UTF-8') . "';</script></body></html>";
                    $type = "html";
                    return Response::create($content, $type, 0);
                }
                if (array_key_exists("vercode", $param) && !captcha_check($param['vercode'])) { //验证码
                    $respContent = "<html><body><script>alert('验证码错误');window.location.href='" . htmlspecialchars($locationHref, ENT_QUOTES, 'UTF-8') . "';</script></body></html>";
                    $type = "html";
                    return Response::create($respContent, $type, 0);
                }
            }

            $key = getAccessIP() . "_" . $id; //用于记录时间

            $commit_type = $formList["commit_type"];
            if ($commit_type == 1) { //同IP在5分钟内，只许提交1次，可免避恶意多次提交。
                $timestamp1 = saveToCache($key);
                if ($timestamp1 != null) {
                    $timestampArr = time_diff($timestamp1, time());
                    $hours = $timestampArr["hours"]; //小时
                    $minutes = $timestampArr["minutes"]; //分钟
                    if ($hours <= 0 && $minutes < $this->limitTime) {
                        $content = "<html><body><script>alert('频繁提交,请稍候再试'); window.location.href='" . htmlspecialchars($locationHref, ENT_QUOTES, 'UTF-8') . "';</script></body></html>";
                        $type = "html";
                        return Response::create($content, $type, 0);
                    }
                }
            }
            if (!$formList) {
                $content = "<html><body><script>alert('提交失败,没找到对应表单');window.location.href='" . htmlspecialchars($locationHref, ENT_QUOTES, 'UTF-8') . "';</script></body></html>";
                $type = "html";
                return Response::create($content, $type, 0);
            }
            unset($param["id"]); //移出表单id
            unset($param["vercode"]); //移出表单验证码

            // 获取所有表单字段
            $formFields = FormField::where(["form_list_id" => $id])->select()->toArray();
            $formData = [];
            $noExistParam = []; //没有必填字段
            $paramNull = []; //必填字段内容为空

            foreach ($formFields as $formField) {
                $isExist = false;
                if (key_exists($formField["name"], $param)) {
                    $isExist = true;
                }
                if ($isExist) { //存在判断值是否为空
                    if ($formField['is_require'] == 1 && (empty($param[$formField['name']]) || $param[$formField['name']] == null)) {
                        array_push($paramNull, $formField['name']);
                    } else { //不为空的时候处理一下内容
                        $fieldVal =  $param[$formField['name']];
                        $fieldVal = form_replace($fieldVal);
                        $param[$formField['name']] = $fieldVal;
                        array_push($formData, ['title' => $formField['title'], 'value' => $fieldVal]);
                    }
                } else {
                    if ($formField['is_require'] == 1) {
                        array_push($noExistParam, $formField['name']);
                    }
                }
            }

            if (sizeof($noExistParam) > 0) {
                $content = "<html><body><script>alert('提交失败,少了必填字段');window.location.href='" . htmlspecialchars($locationHref, ENT_QUOTES, 'UTF-8') . "';</script></body></html>";
                $type = "html";
                return Response::create($content, $type, 0);
            }
            if (sizeof($paramNull) > 0) {
                $content = "<html><body><script>alert('提交失败,必填字段值为空');window.location.href='" . htmlspecialchars($locationHref, ENT_QUOTES, 'UTF-8') . "';</script></body></html>";
                $type = "html";
                return Response::create($content, $type, 0);
            }

            // 获取当前时间
            $currentTime = date('Y-m-d H:i:s');
            // 将当前时间赋值给$param数组中的create_time字段
            $param['create_time'] = $currentTime;
            // 插入数据时，包含create_time字段
            $r = Db::table($formList['table_name'])->strict(false)->insert($param);
            if ($r) {
                if ($formList['email_setting'] == 1) { //开启邮件通知
                    $this->sendMail($formList['template_id'], $formData);
                }
                saveToCache($key, time()); //记录一下时间
                $content = "<html><body><script>alert('提交成功');window.location.href='" . htmlspecialchars($locationHref, ENT_QUOTES, 'UTF-8') . "';</script></body></html>";
                $type = "html";
                return Response::create($content, $type, 200);
            } else {
                $content = "<html><body><script>alert('提交失败');window.location.href='" . htmlspecialchars($locationHref, ENT_QUOTES, 'UTF-8') . "';</script></body></html>";
                $type = "html";
                return Response::create($content, $type, 0);
            }
        }
    }

    $content = "<html><body><script>alert('提交失败');window.location.href='" . htmlspecialchars($locationHref, ENT_QUOTES, 'UTF-8') . "';</script></body></html>";
    $type = "html";
    return Response::create($content, $type, 0);
}

    /**
     * 生成验证码
     */
    public function verify()
    {
        return Captcha::create();
    }

    /**
     * 发送邮件
     */
    private function sendMail($template_id, $formData)
    {
        $pmcArr = Db::name('plugin_mail_config')->select();
        if (sizeof($pmcArr) > 0) {
            $pluginMailConfig  = $pmcArr[0];
            $pmt = Db::name('plugin_mail_template')->find($template_id);
            $title = $pmt['title'];
            $to = $pluginMailConfig['test_account'];
            $contents = ["<!DOCTYPE html>
<html>
<head>
    <title>邮件通知</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin: auto; padding: 20px; border: 1px solid #ddd; }
        h1 {color: #333; }
        p { color: #666; }
		hr {border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>尊敬的FoxCMS用户</h1>
        <p style='font-size: 16px;'>这是一封来自您网站的通知邮件!</p>
        <p>您网站收到新的表单信息，可登录网后后台“应用-自定义表单”中查看。</p>
        <p style='font-weight: bold'>以下是消息内容</p>
		<hr>
"];
            $template_content = $pmt['content'];
            foreach ($formData as $key => $fd) {
                $tc = str_replace("__TITLE__", $fd['title'], $template_content);
                $tc = str_replace("__CONTENT__", $fd['value'], $tc);
                array_push($contents, $tc);
            }
            array_push($contents, '</div></body></html>');
            $mail = new PHPMailer();
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->SMTPAuth = true;
            $mail->Host = trim($pluginMailConfig['smtp_url']);
            $mail->SMTPSecure = 'ssl';
            $mail->Port = $pluginMailConfig['smtp_port'];
            $mail->Hostname = '';
            $mail->CharSet = 'UTF-8';
            $mail->FromName = 'FoxCMS网站消息';
            $mail->Username = trim($pluginMailConfig['send_account']);
            $mail->Password = trim($pluginMailConfig['auth_code']);
            $mail->From = trim($pluginMailConfig['send_account']);
            $mail->isHTML(true);
            $mail->addAddress($to, '');
            $mail->Subject = $title;
            $mail->Body = implode("", $contents);
            try {
                $mail->send();
            } catch (\Exception $e) {
            }
        }
    }
}