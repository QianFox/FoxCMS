<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   14:55
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   14:55
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\model\AccessStat;
use app\common\model\AuthRule;
use app\common\model\Basic as BasicModel;
use think\facade\Db;
use think\facade\View;

class Index extends AdminBase
{
    protected $noAuth = ['index', 'home', 'testHtml'];
    private $versionInfo = [];

    public function initialize()
    {
        parent::initialize();
        $versionPath = root_path() . "data/update/version/info.php";
        if (file_exists($versionPath)) {
            $this->versionInfo = require_once($versionPath);
        }
    }

    // 获取面包屑
    public function getBreadcrumb()
    {
        //面包屑-当前位置
        $bcid = $this->request->get('bcid');
        $breadcrumb = [];
        if (!empty($bcid)) {
            $breadcrumb = AuthRule::getBreadcrumb($bcid);
        }
        return $this->success('获取成功', null, $breadcrumb);
    }

    public function index()
    {
        //服务器信息
        $serverInfo = $this->getServerInfo();
        View::assign("serverInfo", $serverInfo);
        //网站基本信息
        $basicArr = BasicModel::select()->toArray();
        if (sizeof($basicArr) > 0) {
            $basic = $basicArr[0];
            View::assign("basic", $basic);
        }
        //时间信息
        $dateTimeText = $this->getDateTimeText();
        View::assign("dateTimeText", $dateTimeText);

        $foxcmsDomain = config("adminconfig.foxcms_domain"); //foxcms官网地址
        //首页连接
        $links = [
            "siteSet" => url("Config/base") . "?columnId=23",
            "column" => url("Column/index") . "?columnId=28",
            "baiduRecord" => "//www.baidu.com/s?wd=site:{$this->request->rootDomain()}",
            "htmlStatic" => url("Seo/index") . "?columnId=56",
            "formList" => url("form/FormList/index") . "?columnId=42&ruleIds=43,44",
            "countPath" => url("count/SiteProfile/index") . "?columnId=42&apply_plugin_id=4&id=2",
            "adminLog" => "{$foxcmsDomain}/system/update/",
            "helpCenter" => "{$foxcmsDomain}/help/",
            "sitemapUrl" => url("Sitemap/index") . "?columnId=74"
        ];
        View::assign("links", $links);

        //帮助中心
        $articleCount = \app\common\model\Article::count(); //文章数
        $imagesCount = \app\common\model\Images::count(); //图片集数
        $linkCount = \app\common\model\Link::where(['status' => 1])->count(); //友情链接数
        $adCount = \app\common\model\AdvertisingSpace::where(['status' => 1])->count(); //广告数量
        $productCount = \app\common\model\Product::count(); //产品数;
        $videoCount = \app\common\model\Video::count(); //视频数
        $helpCenters = [
            "articleCount" => $articleCount,
            "imagesCount" => $imagesCount,
            "linkCount" => $linkCount,
            "linkUrl" => url("Link/index") . "?columnId=54",
            "adCount" => $adCount,
            "adUrl" => url("AdvertisingSpace/index") . "?columnId=25",
            "productCount" => $productCount,
            "videoCount" => $videoCount,
            "similarArticleUrl" => url("SimilarArticle/index")
        ];
        View::assign("helpCenters", $helpCenters);

        //更新日志
        $updateLogList = $this->getUpdateLog();
        View::assign("updateLogList", $updateLogList);
        //服务支持
        $serviceSupport = $this->getServiceSupport();
        View::assign("serviceSupport", $serviceSupport);
        //帮助中心
        $helpCenterList = $this->getHelpCenter();
        View::assign("helpCenterList", $helpCenterList);
        //幻灯片
        $adList = $this->getAd();
        View::assign("adList", $adList);
        //授权
        $authorize = $this->getAuthorize();
        if ($authorize->is_auth == 0) {
            $authorize->software_version_name = "标准基础版";
            $authorize->code = "无";
            $authorize->end_time_text = "无";
        }
        if (!empty($this->versionInfo['version'])) {
            $authorize->version = "v" . $this->versionInfo['version'];
        } else {
            $authorize->version = "无";
        }
        View::assign("authorize", $authorize);
        //系统更新
        $latestVersion = $this->getLatestVersion();
        $latestVersion->url = url("VersionUpdate/index") . "?columnId=65";
        View::assign("latestVersion", $latestVersion);
        //查询当前版本信息
        View::assign("versionInfo", $this->versionInfo);
        //当前今日时间
        View::assign('today', date("Ymd"));
        return view('home');
    }

    // 检查系统版本
    public function checkVersion()
    {
        //查询当前版本信息
        $only = $this->versionInfo['only'];
        $foxcmsDomain = config("adminconfig.version_domain"); //foxcms官网地址

        $foxcmsPathUrl1 = $foxcmsDomain . url("api/Version/checkVersion") . "?only={$only}&domain={$this->domainNo}";
        $foxcmsPathUrl = replaceSymbol($foxcmsPathUrl1);
        $resJson = get_url_content($foxcmsPathUrl);
        if (empty($resJson)) {
            $this->error("未知版本，请联系客服");
        }
        $res = json_decode($resJson);
        if ($res->code == 1) { //查询成功
            $data = $res->data;
            $this->success($res->msg, "", $data);
        } else {
            $this->error($res->msg);
        }
    }

    // 更新版本
    public function updateVersion()
    {
        $id = $this->request->param('id', '');
        $only = $this->versionInfo['only'];
        $url = url("VersionUpdate" . "/updateFile", ["columnId" => 65, "id" => $id, 'type' => 1, 'domain' => $this->domainNo, "only" => $only]);
        $this->redirect($url);
        exit();
    }

    // 系统更新
    private function getLatestVersion()
    {
        $only = $this->versionInfo['only'];
        $foxcmsDomain = config("adminconfig.version_domain"); //foxcms官网地址
        $foxcmsPathUrl = $foxcmsDomain . url("api/Version/latestVersion" . "?domain={$this->domainNo}&only={$only}");
        $foxcmsPathUrl = replaceSymbol($foxcmsPathUrl);
        $resJson = get_url_content($foxcmsPathUrl);
        if (empty($resJson)) {
            return [];
        }
        $res = json_decode($resJson);
        $data = $res->data;
        return $data;
    }

    // 授权
    private function getAuthorize()
    {
        $foxcmsDomain = config("adminconfig.foxcms_domain"); //foxcms官网地址
        $foxcmsPathUrl = $foxcmsDomain . url("api/Home/authorizeInfo") . "?keyword={$this->domainNo}";
        $resJson = get_url_content($foxcmsPathUrl);
        if (empty($resJson)) {
            return [];
        }
        $res = json_decode($resJson);
        $data = $res->data;
        return $data;
    }

    // 幻灯片
    private function getAd()
    {
        $foxcmsDomain = config("adminconfig.foxcms_domain"); //foxcms官网地址
        $foxcmsPathUrl = $foxcmsDomain . url("api/Home/ad");
        $resJson = get_url_content($foxcmsPathUrl);
        if (empty($resJson)) {
            return [];
        }
        $res = json_decode($resJson);
        $data = $res->data;
        return $data;
    }

    // 帮助中心
    private function getHelpCenter()
    {
        $foxcmsDomain = config("adminconfig.foxcms_domain"); //foxcms官网地址
        $foxcmsPathUrl = $foxcmsDomain . url("api/Home/helpCenter");
        $resJson = get_url_content($foxcmsPathUrl);
        if (empty($resJson)) {
            return [];
        }
        $res = json_decode($resJson);
        $data = $res->data;
        return $data;
    }

    // 服务支持
    private function getServiceSupport()
    {
        $foxcmsDomain = config("adminconfig.foxcms_domain"); //foxcms官网地址
        $foxcmsPathUrl = $foxcmsDomain . url("api/Home/serviceSupport");
        $resJson = get_url_content($foxcmsPathUrl);
        if (empty($resJson)) {
            return [];
        }
        $res = json_decode($resJson);
        $data = $res->data;
        return $data;
    }

    // 更新日志
    private function getUpdateLog()
    {
        $foxcmsDomain = config("adminconfig.foxcms_domain"); //foxcms官网地址
        $foxcmsPathUrl = $foxcmsDomain . url("api/Home/updateLog");
        $resJson = get_url_content($foxcmsPathUrl);
        if (empty($resJson)) {
            return [];
        }
        $res = json_decode($resJson);
        $data = $res->data;
        return $data;
    }

    // 访问概括
    public function accessProfile()
    {
        $param = $this->request->param();
        $type = $param["type"]; //类型
        $labelList = [
            "0",
            "1",
            "2",
            "3",
            "4",
            "5",
            "6",
            "7",
            "8",
            "9",
            "10",
            "11",
            "12",
            "13",
            "14",
            "15",
            "16",
            "17",
            "18",
            "19",
            "20",
            "21",
            "22",
            "23"
        ];
        if ($type == 1) { //今天
            $today = date('Y-m-d', time());
            $rdata = $this->getTimeStat($today);
            $rdata["labelList"] = $labelList;
            $this->success("查询成功", "", $rdata);
        } elseif ($type == 2) { //昨天
            $yesterday = date("Y-m-d", strtotime("-1 day"));
            $rdata = $this->getTimeStat($yesterday);
            $rdata["labelList"] = $labelList;
            $this->success("查询成功", "", $rdata);
        } elseif ($type == 3) { //最近7天
            $date7 = get_dates();
            $labelDateList = [];
            $pvList = [];
            $uvList = [];
            $pvTotal = 0;
            $uvTotal = 0;
            foreach ($date7 as $date) {
                $dataStat = $this->getDateStat($date);
                array_push($labelDateList, $date);
                array_push($pvList, $dataStat["pvTotal"]);
                array_push($uvList, $dataStat["uvTotal"]);
                $pvTotal += $dataStat["pvTotal"];
                $uvTotal += $dataStat["uvTotal"];
            }
            $rdata = [];
            $rdata["labelList"] = $labelDateList;
            $rdata["pvList"] = $pvList;
            $rdata["uvList"] = $uvList;
            $rdata["pvTotal"] = $pvTotal;
            $rdata["uvTotal"] = $uvTotal;
            $this->success("查询成功", "", $rdata);
        } elseif ($type == 4) { //最近30天
            $date30 = get_dates(time(), 30);
            $labelDateList = [];
            $pvList = [];
            $uvList = [];
            $pvTotal = 0;
            $uvTotal = 0;
            foreach ($date30 as $key => $date) {
                $dataStat = $this->getDateStat($date);
                array_push($pvList, $dataStat["pvTotal"]);
                array_push($uvList, $dataStat["uvTotal"]);
                array_push($labelDateList, $date);
                $pvTotal += $dataStat["pvTotal"];
                $uvTotal += $dataStat["uvTotal"];
            }
            $rdata = [];
            $rdata["labelList"] = $labelDateList;
            $rdata["pvList"] = $pvList;
            $rdata["uvList"] = $uvList;
            $rdata["pvTotal"] = $pvTotal;
            $rdata["uvTotal"] = $uvTotal;
            $this->success("查询成功", "", $rdata);
        }
    }

    // 日期统计
    private function getDateStat($dateV)
    {
        $accessStatList = AccessStat::field("id,ip,browser,from_page,source_site,browser_type,create_time")
            ->whereTime("create_time", '>=', $dateV . " 00:00:00")
            ->whereTime("create_time", '<=', $dateV . " 23:59:59")
            ->order("create_time", "asc")->select();

        $pvTotal = sizeof($accessStatList);
        $ips = [];
        foreach ($accessStatList as $accessStat) {
            $ip = $accessStat['ip'];
            array_push($ips, $ip);
        }
        $ips = array_unique($ips);
        $uvTotal = sizeof($ips);
        $statData = ['pvTotal' => $pvTotal, "uvTotal" => $uvTotal];
        return $statData;
    }

    // 获取时间的统计
    private function getTimeStat($dateV)
    {
        $accessStatList = AccessStat::field("id,ip,browser,from_page,source_site,browser_type,create_time,cookie")
            ->whereTime("create_time", '>=', $dateV . " 00:00:00")
            ->whereTime("create_time", '<=', $dateV . " 23:59:59")
            ->order("create_time", "asc")->select();

        $pvArr = [
            "0" => 0,
            "1" => 0,
            "2" => 0,
            "3" => 0,
            "4" => 0,
            "5" => 0,
            "6" => 0,
            "7" => 0,
            "8" => 0,
            "9" => 0,
            "10" => 0,
            "11" => 0,
            "12" => 0,
            "13" => 0,
            "14" => 0,
            "15" => 0,
            "16" => 0,
            "17" => 0,
            "18" => 0,
            "19" => 0,
            "20" => 0,
            "21" => 0,
            "22" => 0,
            "23" => 0
        ];
        $uvArr = [
            "0" => 0,
            "1" => 0,
            "2" => 0,
            "3" => 0,
            "4" => 0,
            "5" => 0,
            "6" => 0,
            "7" => 0,
            "8" => 0,
            "9" => 0,
            "10" => 0,
            "11" => 0,
            "12" => 0,
            "13" => 0,
            "14" => 0,
            "15" => 0,
            "16" => 0,
            "17" => 0,
            "18" => 0,
            "19" => 0,
            "20" => 0,
            "21" => 0,
            "22" => 0,
            "23" => 0
        ];

        $hArr = [];
        foreach ($accessStatList as $accessStat) {
            //            $ip = $accessStat['ip'];
            $create_time = $accessStat['create_time'];
            $ctime = strtotime($create_time);
            $H = date("H", $ctime);
            if (str_starts_with($H, "0")) {
                $H = mb_substr($H, 1);
            }
            foreach ($pvArr as $key => $pv) {
                if ($key == $H) {
                    $pvArr[$key] = $pv + 1;
                    break;
                }
            }
            array_push($hArr, $H);
        }
        $hArr = array_unique($hArr);
        foreach ($uvArr as $ku => $uv) {
            $index = 0;
            foreach ($hArr as $ka => $va) {
                if ($ku == $va) {
                    $index++;
                }
            }
            $uvArr[$ku] = $index;
        }
        $statData = ["pvList" => $pvArr, "uvList" => $uvArr, "pvTotal" => sizeof($accessStatList), "uvTotal" => sizeof($hArr)];
        return $statData;
    }

    // 获取时间提示
    function getDateTimeText()
    {
        $Datetime = date('H');
        $text = "";
        if ($Datetime >= 0 && $Datetime < 7) {
            $text = "夜猫子,要注意身体呀!";
        } else if ($Datetime >= 7 && $Datetime < 12) {
            $text = "上午好!";
        } else if ($Datetime >= 12 && $Datetime < 14) {
            $text = "中午好!";
        } else if ($Datetime >= 14 && $Datetime < 18) {
            $text = "下午好!";
        } else if ($Datetime >= 18 && $Datetime < 22) {
            $text = "晚上好!";
        } else if ($Datetime >= 22 && $Datetime < 24) {
            $text = "夜深了,注意休息呀!";
        }
        return $text;
    }

    // 服务器信息
    private function getServerInfo()
    {
        $serverInfo['os']             = PHP_OS;
        $webServer                 = $_SERVER['SERVER_SOFTWARE'];
        if (stristr($webServer, 'apache')) {
            $webServer = 'apache';
        } else if (stristr($webServer, 'nginx')) {
            $webServer = 'nginx';
        }
        $serverInfo['webServer']      = $webServer;
        $serverInfo['phpVersion']     = phpversion();
        $serverInfo['ip']             = serverIP();
        $serverInfo['postSize']       = @ini_get('file_uploads') ? ini_get('post_max_size') : '未知';
        $serverInfo['fileUpload']     = @ini_get('file_uploads') ? ini_get('upload_max_filesize') : '未开启';
        $serverInfo['maxExTime']    = @ini_get("max_execution_time") . 's'; //脚本最大执行时间
        $serverInfo['domain']         = $_SERVER['HTTP_HOST'];
        $serverInfo['memoryLimit']   = ini_get('memory_limit');
        $mysqlinfo = Db::query("SELECT VERSION() as version");
        $serverInfo['mysqlVersion']  = $mysqlinfo[0]['version'];

        return $serverInfo;
    }

    public function about()
    {
        return view();
    }
}