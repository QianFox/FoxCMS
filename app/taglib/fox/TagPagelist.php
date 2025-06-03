<?php

namespace app\taglib\fox;

class TagPagelist extends TagBase
{


    /**
     * 获取列表分页代码 $listitem（index：首页，pre：上一页，pageno：页码，next：下一页，end：末页： info：共N页 N条）
     */
    public function getPagelist($pages = '', $param=array())
    {
        $visit_lang = $this->getLang();//语言

        $listitem = $param["listitem"]??'';
        $listsize = $param["listsize"]??'';
        $currentstyle = $param["currentstyle"]??'active';
        $diystyle = trim($param["diystyle"]);
        $disstyle = $param["disstyle"]??'disabled';

        $indexStyle="";//首页样式
        $preStyle="";//上一页样式
        $nextStyle="";//下一页样式
        $endStyle="";//末页样式
        $noStyle="";//页码样式
        if(!empty($diystyle)){
            $indexStyles=[];//首页样式
            $preStyles=[];//上一页样式
            $nextStyles=[];//下一页样式
            $endStyles=[];//末页样式
            $noStyles=[];//页码样式
            $diystyles = explode(",",$diystyle);
            foreach ($diystyles as $key=>$sty){
                if(str_starts_with($sty, "index_")){//首页样式
                   $newSty = str_replace("index_", "", $sty);
                   array_push($indexStyles, $newSty);
                }elseif (str_starts_with($sty, "pre_")){//上一页样式
                    $newSty = str_replace("pre_", "", $sty);
                    array_push($preStyles, $newSty);
                }elseif (str_starts_with($sty, "next_")){//下一页样式
                    $newSty = str_replace("next_", "", $sty);
                    array_push($nextStyles, $newSty);
                }elseif (str_starts_with($sty, "end_")){//末页样式
                    $newSty = str_replace("end_", "", $sty);
                    array_push($endStyles, $newSty);
                }elseif (str_starts_with($sty, "no_")){//末页样式
                    $newSty = str_replace("no_", "", $sty);
                    array_push($noStyles, $newSty);
                }else{
                    array_push($indexStyles, $sty);
                    array_push($preStyles, $sty);
                    array_push($nextStyles, $sty);
                    array_push($endStyles, $sty);
                    array_push($noStyles, $sty);
                }
            }
            if(sizeof($indexStyles) > 0){
                $indexStyle = implode(" ", $indexStyles);
            }
            if(sizeof($preStyles) > 0){
                $preStyle = implode(" ", $preStyles);
            }
            if(sizeof($nextStyles) > 0){
                $nextStyle = implode(" ", $nextStyles);
            }
            if(sizeof($endStyles) > 0){
                $endStyle = implode(" ", $endStyles);
            }
            if(sizeof($noStyles) > 0){
                $noStyle = implode(" ", $noStyles);
            }
        }

        $lang = $param["lang"];//语言
        if(!empty($lang)){
            $visit_lang = $lang;
        }
        //描述
        $langInfo = getLang($visit_lang);
        $indexdesc = $langInfo['indexdesc']?$langInfo['indexdesc']['value']:"";//首页
        $predesc = $langInfo['predesc']?$langInfo['predesc']['value']:"";//上一页
        $nextdesc = $langInfo['nextdesc']?$langInfo['nextdesc']['value']:"";//下一页
        $enddesc = $langInfo['enddesc']?$langInfo['enddesc']['value']:"";//末页

        if(!empty($param["indexdesc"])){
            $indexdesc = $param["indexdesc"];
        }
        if(empty($indexdesc) && empty($indexStyle)){
            $indexdesc = "首页";
        }
        if(!empty($param["predesc"])){
            $predesc = $param["predesc"];
        }
        if(!empty($param["nextdesc"])){
            $nextdesc = $param["nextdesc"];
        }
        if(empty($predesc) && empty($preStyle)){
            $predesc= "上一页";
        }
        if(empty($nextdesc) && empty($nextStyle)){
            $nextdesc = "下一页";
        }
        if(!empty($param["enddesc"])){
            $enddesc = $param["enddesc"];
        }
        if(empty($enddesc) && empty($endStyle)){
            $enddesc = "末页";
        }

        if (empty($pages)) {
            echo '标签pagelist报错：只适用在标签list之后。';
            return false;
        }

        $view_suffix = config('view.view_suffix');//后缀
        $isMobile = is_mobile();//是否手机访问
//        $current_page = $pages['current_page'];//当前页
        $last_page = $pages['last_page'];//最后页
        $per_page = $pages['per_page'];//每页数量
        $total = $pages['total'];//总数

        //生成路由 //1:动态url,2:伪静态化,3:静态页面
        $url_model = xn_cfg("seo.url_model");

        $params = request()->param();//参数

        $clickPageStr = $params['page'];//点击的页数

        $isStatic = false;
        $controller = strtolower(request()->controller());//控制器

        $baseurl2 = "";//静态页面的路径首页和第一页
        if($url_model == 3){//3:静态页面
            if($controller == "search" || $controller == "index" || $isMobile){//搜索中的分页
                $baseurl = request()->baseUrl();//基本路径
            }else{//其他栏目分页
                $id = request()->param("id");
                $columnUrl = resetUrl("", $id, $lang);
                $lastH =strripos($columnUrl,"/");
                $baseurl = mb_substr($columnUrl, 0, $lastH)."/{$id}";

                $isStatic = true;
                $baseurl2 =  mb_substr($columnUrl, 0, $lastH)."/";
            }
        }elseif ($url_model == 2){//伪静态化
            if($controller == "index" || $controller == "search" || $isMobile){
                $baseurl = request()->baseUrl();//基本路径
            }else{
                $baseurl = request()->baseUrl();//基本路径
                $clickPageStr = get_between($baseurl, "_", ".".$view_suffix);
                if(!empty($clickPageStr)){
                    if(!is_numeric($clickPageStr)){
                        $clickPageStr = "1";
                    }
                }else{
                    $clickPageStr = "1";
                }
                $id = request()->param("id");
                $lastH =strripos($baseurl,"/");
                $baseurl = mb_substr($baseurl, 0, $lastH)."/{$id}";
                $isStatic = true;
            }
        }else{
            $baseurl = request()->baseUrl();//基本路径
        }

        $listitemArr = explode(",", $listitem);//参数

        if(empty($clickPageStr) || $clickPageStr == null){
            $clickPageStr = "1";
        }
        $clickPage = intval($clickPageStr);

        //移除分页page
        unset($params['page']);

        if(empty($listsize) || $listsize == null){
            $listsize = "1";
        }
        $listsizeNum = intval($listsize);
        if($last_page < $listsizeNum){
            $listsizeNum = $last_page;
        }




        $liArr = [];
        if(in_array("index", $listitemArr)){//首页
            if($clickPage == 1 || $last_page == 1){
                if(!$isStatic) {
                    $li = '<li class="'. $disstyle . ' pageindex"><a class="'.$indexStyle.'" href="javascript:void(0)">'.$indexdesc.'</a></li>';
                }else{
                    if($url_model == 3) {
                        $pagePath = $baseurl2;
                    }else{
                        $pagePath = "javascript:void(0);";
                    }
                    $li = '<li class="'. $disstyle . ' pageindex"><a class="'.$indexStyle.'" href="'.$pagePath.'">'.$indexdesc.'</a></li>';
                }
            }else{
                if(!$isStatic || $isMobile){
                    $cParams = $params;
                    $cParams["page"] = 1;
                    $paramStr = func_param_pack($cParams, "&");//参数串
                    $pagePath = $baseurl."?".$paramStr;
                }else{
                    if($url_model == 3) {
                        $pagePath = $baseurl2;
                    }elseif ($url_model == 2){
                        $pagePath = $baseurl.".".$view_suffix;
                    }else{
                        $pagePath = $baseurl2;
                    }
                }
                $li = '<li class="pageindex"><a class="'.$indexStyle.'" href="'.$pagePath.'">'.$indexdesc.'</a></li>';
            }
            array_push($liArr, $li);
        }

        if(in_array("pre", $listitemArr)){//上一页
            $cParams = $params;
            if($clickPage > 1){
                $cParams["page"] = $clickPage-1;
                if(!$isStatic || $isMobile){
                    $paramStr = func_param_pack($cParams, "&");//参数串
                    $pagePath = $baseurl."?".$paramStr;
                }else{
                    if($cParams["page"] == 1){
                        $pagePath = $baseurl;
                    }else{
                        $pagePath = $baseurl."_".$cParams["page"].".".$view_suffix;
                    }
                }
                $li = '<li class="pagepre "><a class="'.$preStyle.'" href="'.$pagePath.'">'.$predesc.'</a></li>';
            }else{
                if(!$isStatic) {
                    $li = '<li class="'.$disstyle. ' pagepre"><a class="' .$preStyle.'" href="javascript:void(0)">'.$predesc.'</a></li>';
                }else{
                    if($url_model == 3) {
                        $pagePath = $baseurl2;
                    }elseif ($url_model == 2){
                        $pagePath = "javascript:void(0);";
                    }else{
                        $pagePath = $baseurl2;
                    }
                    $li = '<li class="'.$disstyle . ' pagepre"><a class="'. $preStyle.'" href="'.$pagePath.'">'.$predesc.'</a></li>';
                }

            }
            array_push($liArr, $li);
        }

        if(in_array("pageno", $listitemArr )){//页码

            if($clickPage <= $last_page){

                if($last_page == 1){
                    $i = 1;
                    if(!$isStatic) {
                        $li = '<li class="'.$disstyle . ' pageno"><a class="' . $noStyle.' " href="javascript:void(0)">' . $i . '</a></li>';
                    }else{
                        if($url_model == 3) {
                            $pagePath = $baseurl2;
                        }else{
                            $pagePath = "javascript:void(0);";
                        }
                        $li = '<li class="'.$disstyle . ' pageno"><a class="'. $noStyle.' " href="'.$pagePath.'">' . $i . '</a></li>';
                    }
                    array_push($liArr, $li);
                }else{
                    $pages = [];
                    $clickPageC = $clickPage;
                    $isOddNum = $listsizeNum%2;//奇数
                    $aCount = (int)($listsizeNum/2);//加的数
                    $jCount = $aCount;//减的数
                    if($isOddNum){//奇数的话，加的数多加一个
                        $aCount++;
                    }
                    //加的数
                    $aindex = 1;
                    for ($i = 0; $i < $aCount; $i++)
                    {
                        $aNum = $clickPageC + $i;
                        if($aNum <= $last_page){
                            array_push($pages, $aNum);
                        }else{
                            $jNum = $clickPage - $aindex;
                            $aindex ++;
                            if($jNum > 0){
                                array_push($pages, $jNum);
                            }else{
                                array_push($pages, 1);
                            }
                        }
                    }

                    //减的数
                    if($aindex > 1){//大于1
                        $jCount = $jCount + ($aindex-1);
                    }
                    $jindex = 0;
                    for ($aindex; $aindex <= $jCount; $aindex++)
                    {
                        $jNum = $clickPageC - $aindex;
                        if($jNum > 0){
                            array_push($pages, $jNum);
                        }else{
                            $jNum = $clickPage + $aCount + $jindex;
                            $jindex++;
                            if($jNum < $last_page){
                                array_push($pages, $jNum);
                            }else{
                                array_push($pages, $last_page);
                            }
                        }
                    }

                    sort($pages);//排序
                    foreach ($pages as $page){

                        if($page == $clickPage){
                            if(!$isStatic){
                                $li = '<li class="'. $currentstyle . ' pageno"><a class="'.$noStyle.'" href="javascript:void(0)">' . $page . '</a></li>';
                            }else{

                                if($url_model == 3) {
                                    if($page == 1){
                                        $pagePath = $baseurl2;
                                    }else {
                                        $pagePath = $baseurl . "_{$clickPage}." . $view_suffix;
                                    }
                                }else{
                                    $pagePath = "javascript:void(0);";
                                }

                                $li = '<li class="'. $currentstyle . ' pageno"><a class="'.$noStyle.'" href="'.$pagePath.'">' . $page . '</a></li>';
                            }
                        }else{

                            if(!$isStatic || $isMobile){
                                $params["page"] = $page;
                                $paramStr = func_param_pack($params, "&");//参数串
                                $pagePath = $baseurl."?".$paramStr;
                            }else{
                                if($page == 1){
                                    if($url_model == 3) {
                                        $pagePath = $baseurl2;
                                    }elseif ($url_model == 2){
                                        $pagePath = $baseurl.".".$view_suffix;
                                    }
                                }else{
                                    $pagePath = $baseurl."_".$page.".".$view_suffix;
                                }
                            }

                            $li = '<li class=" pageno"><a class="'.$noStyle.'" href="'.$pagePath.'">'.$page.'</a></li>';
                        }
                        array_push($liArr, $li);
                    }

                }
            }else{
                if(!$isStatic) {
                    $params["page"] = $clickPage;
                    $li = '<li class="'.$disstyle. ' pageno"><a class="'. $noStyle.'" href="javascript:void(0)">' . $clickPage . '</a></li>';
                }else{
                    $pagePath = $baseurl."_{$clickPage}.".$view_suffix;
                    $li = '<li class="'.$disstyle. ' pageno"><a class="' . $noStyle.'" href="'.$pagePath.'">' . $clickPage . '</a></li>';
                }

                array_push($liArr, $li);
            }
        }

        if(in_array("next", $listitemArr)){//下一页
            $cParams = $params;
            if($clickPage < $last_page){
                $cParams["page"] = $clickPage+1;
                if(!$isStatic || $isMobile){
                    $paramStr = func_param_pack($cParams, "&");//参数串
                    $pagePath = $baseurl."?".$paramStr;
                }else{
                    $pagePath = $baseurl."_".$cParams["page"].".".$view_suffix;
                }

                $li = '<li class="pagenext "><a class="'.$nextStyle.'" href="'.$pagePath.'">'.$nextdesc.'</a></li>';
            }else{
                if(!$isStatic) {
                    $li = '<li class="'.$disstyle. ' pagenext"><a class="' . $nextStyle.'" href="javascript:void(0)">'.$nextdesc.'</a></li>';
                }else{
                    $pagePath = $baseurl."_{$last_page}.".$view_suffix;
                    $li = '<li class="'.$disstyle. ' pagenext"><a class="' . $nextStyle.' " href="'.$pagePath.'">'.$nextdesc.'</a></li>';
                }
            }
            array_push($liArr, $li);
        }

        if(in_array("end", $listitemArr)){//末页

            if($last_page == 1 || $last_page == $clickPage){
                if($last_page == 1){
                    if(!$isStatic) {
                        $li = '<li class="'.$disstyle. ' pageend"><a class="' . $endStyle.' " href="javascript:void(0)">'.$enddesc.'</a></li>';
                    }else{
                        if ($url_model == 3) {
                            $pagePath = $baseurl2;
                        } else{
                            $pagePath = "javascript:void(0);";
                        }
                        $li = '<li class="'.$disstyle. ' pageend"><a class="'. $endStyle.' " href="'.$pagePath.'">'.$enddesc.'</a></li>';
                    }
                }else{
                    if(!$isStatic) {
                        $li = '<li class=" pageend"><a class="' . $endStyle . '" href="javascript:void(0)">'.$enddesc.'</a></li>';
                    }else{
                        if($url_model == 2) {
                            $pagePath = "javascript:void(0);";
                        }elseif($url_model == 3){
                            $pagePath = $baseurl."_{$last_page}.".$view_suffix;
                        }
                        $li = '<li class="'.$disstyle . ' pageend"><a class="' . $endStyle.' " href="'.$pagePath.'">'.$enddesc.'</a></li>';
                    }
                }
            }else{
                $cParams = $params;
                $cParams["page"] = $last_page;

                if(!$isStatic || $isMobile){
                    $paramStr = func_param_pack($cParams, "&");//参数串
                    $pagePath = $baseurl."?".$paramStr;
                }else{
                    $pagePath = $baseurl."_".$cParams["page"].".".$view_suffix;
                }

                $li = '<li class="pageend "><a class="'.$endStyle.'" href="'.$pagePath.'">'.$enddesc.'</a></li>';
            }
            array_push($liArr, $li);
        }

        if(in_array("info", $listitemArr)){//共N页 N条
            $li = "<li class='pageinfo'>共<strong>{$last_page}</strong>页 <strong>{$total}</strong>条</li>";
            if($lang != "cn"){
                $li = "<li>{$last_page} pages, {$total} articles</li>";
            }
            array_push($liArr, $li);
        }
        $lis = implode("", $liArr);

        if($last_page >= 2){
            $parse = <<<EOF
                        <ul class="pagination">
                        $lis
                        </ul>
EOF;
        }else{
            $parse = "";
        }
        return $parse;
    }

}