<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   19:13
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   19:13
 */

namespace app\count\controller;

use app\common\controller\AdminApplyBase;
use app\common\model\AccessStat;
use think\Db;
use think\facade\View;

class SiteProfile extends AdminApplyBase
{
    // 今日流量
    private function traffic($dateV)
    {
        $accessStatList = AccessStat::field("id,ip,browser,from_page,source_site,browser_type,create_time,cookie")
            ->whereTime("create_time", '>=', $dateV . " 00:00:00")
            ->whereTime("create_time", '<=', $dateV . " 23:59:59")
            ->order("create_time", "asc")->select();

        $uvArr = [];
        $ipArr = [];
        foreach ($accessStatList as $accessStat) {
            if ($accessStat['cookie'] == "first") {
                array_push($uvArr, ["ip" => $accessStat["ip"], "cookie" => $accessStat["cookie"]]);
            }
            array_push($ipArr, $accessStat["ip"]);
        }
        $ipArr = array_unique($ipArr); //去重ip

        $totalDiffTime = 0;
        foreach ($ipArr as $ip) { //遍历ip
            $minTime = strtotime($accessStatList[0]['create_time']); //最小时间
            $maxTime = $minTime; //最大时间
            foreach ($accessStatList as $as) {
                if ($ip == $as['ip']) {
                    $createTime = strtotime($as['create_time']);
                    if ($createTime >= $maxTime) {
                        $maxTime = $createTime;
                    } elseif ($createTime < $minTime) {
                        $minTime = $createTime;
                    }
                }
            }
            $timeDiff = $maxTime - $minTime;
            $totalDiffTime += $timeDiff;
        }

        $avgTime = (int)($totalDiffTime / sizeof($ipArr));
        $diffAvgTimeArr = diff_time($avgTime);
        $avgTimeStr = "";
        if ($diffAvgTimeArr["hours"] < 10) {
            $avgTimeStr .= "0" . $diffAvgTimeArr["hours"];
        } else {
            $avgTimeStr .= $diffAvgTimeArr["hours"];
        }
        $avgTimeStr .= ":";
        if ($diffAvgTimeArr["minutes"] < 10) {
            $avgTimeStr .= "0" . $diffAvgTimeArr["minutes"];
        } else {
            $avgTimeStr .= $diffAvgTimeArr["minutes"];
        }
        $avgTimeStr .= ":";
        if ($diffAvgTimeArr["seconds"] < 10) {
            $avgTimeStr .= "0" . $diffAvgTimeArr["seconds"];
        } else {
            $avgTimeStr .= $diffAvgTimeArr["seconds"];
        }

        $pv = sizeof($accessStatList);
        $uv = sizeof($uvArr);
        $ip = sizeof($ipArr);
        $day = ['pv' => $pv, 'uv' => $uv, 'ip' => $ip, 'avgTime' => $avgTimeStr]; //今日
        return $day;
    }

    public function index()
    {
        //今日流量
        //今天
        $today = date('Y-m-d', time());
        $todayDatas = $this->traffic($today);
        $todayDatas['text'] = "今日";
        //昨天
        $traffics = [];
        $yesterday = date("Y-m-d", strtotime("-1 day"));
        $yesterdayDatas = $this->traffic($yesterday);
        $yesterdayDatas['text'] = "昨天";
        array_push($traffics, $todayDatas);
        array_push($traffics, $yesterdayDatas);
        View::assign("traffics", $traffics);

        //Top10入口页面
        $cursorList = AccessStat::where(['cookie' => 'first'])->cursor();
        $totalCount = 0; //总数
        $entranceList = [];
        foreach ($cursorList as $accessStat) {
            $from_page = $accessStat['from_page'];
            if (empty($from_page)) {
                continue;
            }
            $ent = null;
            $key = 0;
            foreach ($entranceList as $k => $entrance) {
                if ($from_page == $entrance['from_page']) {
                    $ent = $entrance;
                    $key = $k;
                    break;
                }
            }
            if ($ent == null) {
                array_push($entranceList, ['from_page' => $from_page, 'count' => 1]);
            } else {
                $count = $ent['count'] + 1;
                $updateEn = ['from_page' => $from_page, 'count' => $count];
                $entranceList[$key] = $updateEn;
            }
            $totalCount++;
        }
        //排序从大到小
        uasort($entranceList, function ($a, $b) {
            return $b['count'] > $a['count'];
        });
        //计算百分比
        $entranceListR = [];
        foreach ($entranceList as $k => $entranceR) {
            if ($k < 10) {
                $count = $entranceR['count'];
                $percent = (number_format(($count / $totalCount), 4) * 100) . "%";
                $entranceR['percent'] = $percent;
                array_push($entranceListR, $entranceR);
            }
        }
        View::assign("entranceList", $entranceListR); //Top10入口页面

        //Top10受访页面
        $cursorListA = AccessStat::cursor();
        $totalCountA = 0; //总数
        $entranceListA = [];
        foreach ($cursorListA as $accessStatA) {
            $from_pageA = $accessStatA['from_page'];
            if (empty($from_pageA)) {
                continue;
            }
            $entA = null;
            $keyA = 0;
            foreach ($entranceListA as $k => $entranceA) {
                if ($from_pageA == $entranceA['from_page']) {
                    $entA = $entranceA;
                    $keyA = $k;
                    break;
                }
            }
            if ($entA == null) {
                array_push($entranceListA, ['from_page' => $from_pageA, 'count' => 1]);
            } else {
                $countA = $entA['count'] + 1;
                $updateEnA = ['from_page' => $from_pageA, 'count' => $countA];
                $entranceListA[$keyA] = $updateEnA;
            }
            $totalCountA++;
        }
        //排序从大到小
        uasort($entranceListA, function ($a, $b) {
            return $b['count'] > $a['count'];
        });
        //计算百分比
        $entranceListRA = [];
        foreach ($entranceListA as $kA => $entranceRA) {
            if ($kA < 10) {
                $count = $entranceRA['count'];
                $percent = (number_format(($count / $totalCountA), 4) * 100) . "%";
                $entranceRA['percent'] = $percent;
                array_push($entranceListRA, $entranceRA);
            }
        }
        View::assign("visitedList", $entranceListRA); //Top10受访页面

        //受访页面初始化
        View::assign("today", $today); //今天
        View::assign("yesterday", $yesterday); //昨天
        //最近7天
        $date7 = get_dates();
        $firt7Date1 = $date7['1'];
        $last7Date7 = $date7[(sizeof($date7))];
        View::assign("firt7Date1", $firt7Date1);
        View::assign("last7Date7", $last7Date7);
        //最近30天
        $date30 = get_dates(time(), 30);
        $firt30Date1 = $date30['1'];
        $last30Date30 = $date30[(sizeof($date30))];

        View::assign("firt30Date1", $firt30Date1);
        View::assign("last30Date30", $last30Date30);
        return view('index');
    }

    // 删除三十天之前的统计数据
    public function del30AccessStat()
    {
        $date30 = get_dates(time(), 30);
        $firt30Date1 = $date30['1'];
        $endTime = $firt30Date1 . " 00:00:00";
        $where = [];
        array_push($where, ["create_time", '<', $endTime]);
        $ras = AccessStat::where($where)->delete();
        $this->success("删除成功", "", $ras);
    }

    // 过滤本地ip
    private function filterIp($ip)
    {
        if ($ip == "127.0.0.1") {
            return "1.48.242.105"; //外网ip
        } else {
            return $ip;
        }
    }

    // 地区分布
    public function region()
    {
        $domain = $this->domain;
        $paramStr = func_param_pack(['keyword' => $domain], "&"); //参数串
        $foxcmsDomain = config("adminconfig.foxcms_domain"); //foxcms官网地址
        $foxcmsPathUrl = $foxcmsDomain . url("api/Home/authorizeInfo") . "?{$paramStr}";
        $resJson = get_url_content($foxcmsPathUrl);
        $res = json_decode($resJson);
        $data = $res->data; //授权信息
        if ($data->is_auth == 0) {
            $this->error("抱歉，你的域名未授权或授权过期了", "", $data);
        }
        $type = $this->request->param("type", 1);
        if ($type == 1) { //今天
            $today = date('Y-m-d', time());
            $startTime = $today . " 00:00:00";
            $endTime = $today . " 23:59:59";
        } elseif ($type == 2) { //昨天
            $yesterday = date("Y-m-d", strtotime("-1 day"));
            $startTime = $yesterday . " 00:00:00";
            $endTime = $yesterday . " 23:59:59";
        } else {
            //最近7天
            $date7 = get_dates();
            $firt7Date1 = $date7['1'];
            $last7Date7 = $date7[(sizeof($date7))];
            $startTime = $firt7Date1 . " 00:00:00";
            $endTime = $last7Date7 . " 23:59:59";
        }
        $where = [];
        array_push($where, ["create_time", '>=', $startTime]);
        array_push($where, ["create_time", '<=', $endTime]);
        $cursorList = AccessStat::where($where)->select();
        $provinceIpList = [];
        foreach ($cursorList as $accessStat) {
            $ip = $this->filterIp($accessStat['ip']);
            $pro = null;
            $key = 0;
            foreach ($provinceIpList as $k => $provinceIp) {
                if ($ip == $provinceIp['ip']) {
                    $pro = $provinceIp;
                    $key = $k;
                    break;
                }
            }
            if ($pro == null) {
                array_push($provinceIpList, ['ip' => $ip, 'count' => 1]);
            } else {
                $count = $pro['count'] + 1;
                $updatePro = ['ip' => $ip, 'count' => $count];
                $provinceIpList[$key] = $updatePro;
            }
        }

        //解析ip列表
        $provinceIpListR = [];
        $domain = $this->domain;
        foreach ($provinceIpList as $proIp) {
            $param = ['keyword' => $domain, 'ip' => $proIp['ip']];
            $data = $this->ipAnalyse($param);
            $fdata = $data;
            if ($fdata == null || $fdata == "") {
                continue;
            }
            $proIp['prov'] = $fdata['prov'];
            array_push($provinceIpListR, $proIp);
        }

        $provinceList = [
            '北京',
            '天津',
            '上海',
            '重庆',
            '河北',
            '河南',
            '云南',
            '辽宁',
            '黑龙江',
            '湖南',
            '安徽',
            '山东',
            '新疆',
            '江苏',
            '浙江',
            '江西',
            '湖北',
            '广西',
            '甘肃',
            '山西',
            '内蒙古',
            '陕西',
            '吉林',
            '福建',
            '贵州',
            '广东',
            '青海',
            '西藏',
            '四川',
            '宁夏',
            '海南',
            '台湾',
            '香港',
            '澳门'
        ]; //省份
        $provinceMap = [];
        $specialMap = [];
        foreach ($provinceList as $province) {
            $value = 0;
            foreach ($provinceIpListR as $provinceIp) {
                if ($province == $provinceIp['prov']) {
                    $value = $provinceIp['count'];
                    array_push($specialMap, $province);
                    break;
                }
            }
            $prov = ['name' => $province, 'value' => $value];
            array_push($provinceMap, $prov);
        }
        $rdata = ['provinceMap' => $provinceMap, 'specialMap' => $specialMap, 'is_auth' => 1];
        $this->success("查询成功", "", $rdata);
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

    // 受访页面
    public function accessPage()
    {
        $currentPage = $this->request->param("currentPage", 1);
        $pageSize = $this->request->param("pageSize", 10);
        $index = $this->request->param("index", "");
        $keyword = $this->request->param("keyword");
        $where = [];
        if (!empty($keyword)) {
            array_push($where, ['page_title', 'like', '%' . trim($keyword) . '%']);
        }
        if ($index != "") {
            if ($index == 0) { //今天
                $today = date('Y-m-d', time());
                $startTime = $today . " 00:00:00";
                $endTime = $today . " 23:59:59";
            } elseif ($index == 1) { //昨天
                $yesterday = date("Y-m-d", strtotime("-1 day"));
                $startTime = $yesterday . " 00:00:00";
                $endTime = $yesterday . " 23:59:59";
            } elseif ($index == 2) {
                //最近7天
                $date7 = get_dates();
                $firt7Date1 = $date7['1'];
                $last7Date7 = $date7[(sizeof($date7))];
                $startTime = $firt7Date1 . " 00:00:00";
                $endTime = $last7Date7 . " 23:59:59";
            } else {
                //最近30天
                $date30 = get_dates(time(), 30);
                $firt30Date1 = $date30['1'];
                $last30Date30 = $date30[(sizeof($date30))];
                $startTime = $firt30Date1 . " 00:00:00";
                $endTime = $last30Date30 . " 23:59:59";
            }
            array_push($where, ["create_time", '>=', $startTime]);
            array_push($where, ["create_time", '<=', $endTime]);
        }

        $dataSql = AccessStat::where($where)->buildSql();
        $sql = "({$dataSql})";
        $rpage = \think\facade\Db::table($sql . " as a")->field('page_title, from_page, count(from_page) as pvcount')
            ->where([['page_title', '<>', '']])
            ->group('page_title,from_page')->paginate(['page' => $currentPage, 'list_rows' => $pageSize])->each(function ($item, $key) use ($sql) {
                $newWhere = [];
                array_push($newWhere, ["from_page", '=', $item['from_page']]);
                $rlist = \think\facade\Db::table($sql . " as b")->where($newWhere)->select();
                $uvArr = [];
                foreach ($rlist as $aStat) {
                    $create_time = date('Y-m-d', strtotime($aStat['create_time']));
                    $uvCurr = null;
                    foreach ($uvArr as $uvA) {
                        if ($aStat['cookie'] == $uvA['cookie'] && $create_time == $uvA['create_time']) {
                            if ($aStat['cookie'] == 'continue') {
                                $uvCurr = $uvA;
                            }
                        }
                    }
                    if ($uvCurr == null) {
                        array_push($uvArr, ['cookie' => $aStat['cookie'], 'create_time' => $create_time]);
                    }
                }
                $item['uvcount'] = sizeof($uvArr);
                return $item;
            });
        $total = $rpage->total();
        $per_page = $rpage->listRows();
        $current_page = $rpage->currentPage();
        $last_page = $rpage->lastPage();
        $dataList = $rpage->items();
        //排序从大到小
        uasort($dataList, function ($a, $b) {
            return $b['pvcount'] > $a['pvcount'];
        });

        $rdataList = [];
        foreach ($dataList as $d) {
            array_push($rdataList, $d);
        }
        $rdata = [];
        $rdata['total'] = $total;
        $rdata['per_page'] = $per_page;
        $rdata['current_page'] = $current_page;
        $rdata['last_page'] = $last_page;
        $rdata['data'] = $rdataList;
        $this->success("查询成功", "", $rdata);
    }

    // 入口页面
    public function enterPage()
    {
        $currentPage = $this->request->param("currentPage", 1);
        $pageSize = $this->request->param("pageSize", 10);
        $index = $this->request->param("index", "");
        $keyword = $this->request->param("keyword");
        $where = [];
        if (!empty($keyword)) {
            array_push($where, ['page_title', 'like', '%' . trim($keyword) . '%']);
        }
        if ($index != "") {
            if ($index == 0) { //今天
                $today = date('Y-m-d', time());
                $startTime = $today . " 00:00:00";
                $endTime = $today . " 23:59:59";
            } elseif ($index == 1) { //昨天
                $yesterday = date("Y-m-d", strtotime("-1 day"));
                $startTime = $yesterday . " 00:00:00";
                $endTime = $yesterday . " 23:59:59";
            } elseif ($index == 2) {
                //最近7天
                $date7 = get_dates();
                $firt7Date1 = $date7['1'];
                $last7Date7 = $date7[(sizeof($date7))];
                $startTime = $firt7Date1 . " 00:00:00";
                $endTime = $last7Date7 . " 23:59:59";
            } else {
                //最近30天
                $date30 = get_dates(time(), 30);
                $firt30Date1 = $date30['1'];
                $last30Date30 = $date30[(sizeof($date30))];
                $startTime = $firt30Date1 . " 00:00:00";
                $endTime = $last30Date30 . " 23:59:59";
            }
            array_push($where, ["create_time", '>=', $startTime]);
            array_push($where, ["create_time", '<=', $endTime]);
        }
        array_push($where, ["cookie", '=', 'first']);

        $dataSql = AccessStat::where($where)->buildSql();
        $sql = "({$dataSql})";
        $rpage =  \think\facade\Db::table($sql . " as a")->field('page_title, from_page, count(from_page) as uvcount')
            ->group('page_title,from_page')->paginate(['page' => $currentPage, 'list_rows' => $pageSize])->each(function ($item, $key) use ($sql) {
                $newWhere = [];
                array_push($newWhere, ["from_page", '=', $item['from_page']]);
                $rlist = \think\facade\Db::table($sql . " as b")->where($newWhere)->select();
                $ipArr = [];
                foreach ($rlist as $aStat) {
                    $create_time = date('Y-m-d', strtotime($aStat['create_time']));
                    $ipCurr = null;
                    foreach ($ipArr as $ipA) {
                        if ($aStat['ip'] == $ipA['ip'] && $create_time == $ipA['create_time']) {
                            $ipCurr = $ipA;
                        }
                    }
                    if ($ipCurr == null) {
                        array_push($ipArr, ['ip' => $aStat['ip'], 'create_time' => $create_time]);
                    }
                }
                $item['ipcount'] = sizeof($ipArr);
                return $item;
            });

        $total = $rpage->total();
        $per_page = $rpage->listRows();
        $current_page = $rpage->currentPage();
        $last_page = $rpage->lastPage();
        $dataList = $rpage->items();
        //排序从大到小
        uasort($dataList, function ($a, $b) {
            return $b['pvcount'] > $a['pvcount'];
        });

        $rdataList = [];
        foreach ($dataList as $d) {
            array_push($rdataList, $d);
        }
        $rdata = [];
        $rdata['total'] = $total;
        $rdata['per_page'] = $per_page;
        $rdata['current_page'] = $current_page;
        $rdata['last_page'] = $last_page;
        $rdata['data'] = $rdataList;
        $this->success("查询成功", "", $rdata);
    }

    // 访客分析
    public function visitorAnalyse()
    {
        $ip = $this->request->param("ip", "");
        $currentPage = $this->request->param("currentPage", 1);
        $pageSize = $this->request->param("pageSize", 10);
        $type = $this->request->param("type");
        $where = [];
        array_push($where, ["cookie", '=', 'first']);
        if (!empty($ip)) {
            array_push($where, ["ip", '=', trim($ip)]);
        }
        if (!empty($type)) {
            if ($type == 1) { //今天
                $today = date('Y-m-d', time());
                $startTime = $today . " 00:00:00";
                $endTime = $today . " 23:59:59";
            } elseif ($type == 2) { //昨天
                $yesterday = date("Y-m-d", strtotime("-1 day"));
                $startTime = $yesterday . " 00:00:00";
                $endTime = $yesterday . " 23:59:59";
            } else {
                //最近7天
                $date7 = get_dates();
                $firt7Date1 = $date7['1'];
                $last7Date7 = $date7[(sizeof($date7))];
                $startTime = $firt7Date1 . " 00:00:00";
                $endTime = $last7Date7 . " 23:59:59";
            }
            array_push($where, ["create_time", '>=', $startTime]);
            array_push($where, ["create_time", '<=', $endTime]);
        }
        $domain = $this->domain;

        $rpage =  AccessStat::field('id, browser, page_title, from_page, ip, create_time')->where($where)->order(["create_time" => "desc"])->paginate(['page' => $currentPage, 'list_rows' => $pageSize])->each(function ($item, $key) use ($domain) {
            $ip = $this->filterIp($item['ip']); //ip
            $param = ['keyword' => $domain, 'ip' => $ip];
            $fdata = $this->ipAnalyse($param);
            //地域
            if (!($fdata == null || $fdata == "")) {
                $area = $fdata['prov'] . "-" . $fdata['city'] . "-" . $fdata['area'] . " " . $fdata['isp'];
            } else {
                $area = "--";
            }
            $item['area'] = $area;
            return $item;
        });
        $total = $rpage->total();
        $per_page = $rpage->listRows();
        $current_page = $rpage->currentPage();
        $last_page = $rpage->lastPage();
        $dataList = $rpage->items();
        $rdata = [];
        $rdata['total'] = $total;
        $rdata['per_page'] = $per_page;
        $rdata['current_page'] = $current_page;
        $rdata['last_page'] = $last_page;
        $rdata['data'] = $dataList;
        $this->success("查询成功", "", $rdata);
    }

    // ip解析
    private function ipAnalyse($param)
    {
        $paramStr = func_param_pack($param, "&"); //参数串
        $foxcmsDomain = config("adminconfig.foxcms_domain"); //foxcms官网地址
        $foxcmsPathUrl = $foxcmsDomain . url("api/siteProfile/ipAnalyse") . "?{$paramStr}";
        $resJson = get_url_content($foxcmsPathUrl);
        $data = json_decode($resJson, true);
        if ($data['code'] == 1) {
            return $data['data'];
        }
        return $resJson;
    }
}