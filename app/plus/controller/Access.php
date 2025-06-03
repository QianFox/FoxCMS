<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   19:22
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   19:22
 */

namespace app\plus\controller;

use app\common\model\AccessStat;
use think\Response;

// 统计
class Access
{

    private $limitTime = 5; // 基础限制时间（秒）
    private $maxPenalty = 86400; // 最大惩罚时间（24小时）

    public function stat()
    {
        // 获取客户端指纹（IP+UA+协议头混合特征）
        $clientFingerprint = md5(
            getAccessIP() .
                ($_SERVER['HTTP_USER_AGENT'] ?? 'unknown') .
                ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'en')
        );

        // 生成复合缓存键
        $rateKey = "access_rate:" . $clientFingerprint;
        $penaltyKey = "access_penalty:" . $clientFingerprint;

        // 检查当前是否处于惩罚期
        if ($penaltyTime = saveToCache($penaltyKey)) {
            $remaining = $penaltyTime - time();
            return Response::create("请求受限，请{$remaining}秒后重试", "html", 429);
        }

        // 智能限流逻辑
        $lastAccess = saveToCache($rateKey);
        $currentTime = time();

        if ($lastAccess && ($currentTime - $lastAccess) < $this->limitTime) {
            // 动态计算惩罚时间（指数退避）
            $violations = saveToCache($rateKey . '_count') ?: 0;
            $penaltyDuration = min(
                $this->limitTime * pow(2, $violations),
                $this->maxPenalty
            );

            // 记录惩罚状态
            saveToCache($penaltyKey, $currentTime + $penaltyDuration, $penaltyDuration);
            saveToCache($rateKey . '_count', $violations + 1, 86400); // 24小时计数

            // 返回标准化响应
            header('Retry-After: ' . $penaltyDuration);
            return Response::create("请求过于频繁，请{$penaltyDuration}秒后重试", "html", 429);
        }

        // 正常请求处理
        saveToCache($rateKey, $currentTime, 300); // 5分钟时间窗
        saveToCache($rateKey . '_count', 0, 86400); // 重置违规计数

        // 以下是原有的业务逻辑
        $ip = getAccessIP();
        $k = "access_" . str_replace(".", "_", $ip);

        // 请求过滤拦截
        // 从缓存中获取上次访问的时间戳
        $timestamp1 = saveToCache($k);
        if ($timestamp1 != null) {
            // 计算当前时间与上次访问时间的时间差
            $timestampArr = time_diff($timestamp1, time());
            $hours = $timestampArr["hours"]; // 小时
            $minutes = $timestampArr["minutes"]; // 分钟
            $seconds = $timestampArr["seconds"]; // 秒
            // 如果时间差小于限制时间（默认5秒），则认为是频繁提交
            if ($hours <= 0 && $minutes <= 0 && $seconds < $this->limitTime) {
                $content =  "频繁提交,请稍候再试";
                $type = "html";
                header("Content-type: text/html; charset=utf-8");
                // 返回响应，提示用户稍后再试
                return Response::create($content, $type, 0);
            }
        }

        // 从cookie中获取访问信息
        $cookie = cookie($k);
        if (empty($cookie)) {
            // 如果cookie为空，则设置一个新的cookie，有效期到当天晚上12点
            $saveTime = strtotime(date("Y-m-d", time()) . " 23:59:59");
            setcookie($k, (string)time(), $saveTime, "/");
            $cookie = "first";
        } else {
            $cookie = "continue";
        }
        // 检查GET参数中是否存在'fp'，如果存在则获取其值
        if (key_exists('fp', $_GET)) {
            $from_page = $_GET['fp'];
        } else {
            // 优先使用HTTP_REFERER自动获取来源页
            $from_page = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : getFromPage();
        }
        // 判断访问设备类型
        $mobileText = "计算机端浏览器";
        if (is_mobile()) {
            $mobileText = "移动端浏览器";
        }
        // 获取搜索引擎关键词及来源信息
        $word = search_word_from();
        if (!empty($word['keyword'])) {
            // 如果存在关键词，则输出相关信息
            echo '关键字：' . $word['keyword'] . ' 来自：' . $word['from'];
        }
        // 获取来源页面，优先使用GET参数中的'fp'，否则调用getFromPage函数获取
        if (key_exists('fp', $_GET)) {
            $from_page = $_GET['fp'];
        } else {
            $from_page = getFromPage();
        }
        // 获取来源页面的标题
        // 优先级：GET参数 > 页面抓取 > 默认值
        $page_title = "未获取到标题";
        if (isset($_GET['title']) && !empty($_GET['title'])) {
            // 双重解码防护 + 安全过滤
            $page_title = htmlspecialchars(
                urldecode($_GET['title']),
                ENT_QUOTES,
                'UTF-8'
            );
            // 限制标题长度（根据数据库字段长度调整）
            $page_title = mb_substr(trim($page_title), 0, 255);
        } else {
            // 保留原有抓取逻辑作为备用
            $page_title = getPageTitle($from_page);
            if (empty($page_title)) {
                $page_title = "未获取到标题";
            }
        }
        // 设置查询时间范围为当天的00:00:00到23:59:59
        $startTime = date("Y-m-d") . " 00:00:00";
        $endTime = date("Y-m-d") . " 23:59:59";
        // 获取浏览器信息
        $browser = getBrowser();
        // 查询数据库中是否存在相同IP、来源页面和浏览器的访问记录
        $as = AccessStat::where([['ip', '=', $ip], ['from_page', '=', $from_page], ['browser', '=', $browser]])->whereBetweenTime("create_time", $startTime, $endTime)->find();
        if ($as) {
            $type = "html";
            $content = "'存在" . $cookie . "'";
            header("Content-type: text/html; charset=utf-8");
            // 如果存在记录，则返回响应，提示记录已存在
            return Response::create($content, $type, 200);
        }
        // 构建新的访问记录数据
        $accessStat = ["cookie" => $cookie, "ip" => $ip, "browser" => $browser, "from_page" => $from_page, "source_site" => $word['from'], "browser_type" => $mobileText, 'page_title' => $page_title];
        try {
            // 将新的访问记录保存到数据库
            AccessStat::create($accessStat);
            // 记录当前时间到缓存中
            saveToCache($k, time());
        } catch (\Exception $e) {
            $type = "html";
            $content = "'" . $e->getMessage() . "'";
            // 如果保存过程中发生异常，则返回响应，提示错误信息
            return Response::create($content, $type, 200);
        }
        $type = "html";
        $content = "'成功" . $cookie . "'";
        header("Content-type: text/html; charset=utf-8");
        // 返回响应，提示记录保存成功
        return Response::create($content, $type, 200);
    }
}
