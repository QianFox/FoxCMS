<?php

namespace app\taglib\fox;
use app\common\model\Column;
use app\common\model\Tag;
use think\facade\Db;

/**
 * 标签所有值
 */
class TagTag extends TagBase
{
    /**
     * 查询数据
     */
    public function getList($param)
    {
        $visit_lang = $this->getLang();//语言
        $showall = $param['showall'];//是否显示全部 1：显示；0：不显示
        $model = $param['model'];
        if(empty($model)){
            $model = $param['modelC'];
        }

        $typeid = $param['typeid'];//栏目id
        
        $sid = $param["sid"];//栏目标识
        if(empty($typeid) && !empty($sid)){
            $fColumns = Column::field("id,column_model")->whereIn("nid",$sid)->where(['lang'=>$visit_lang])->select();
            if(sizeof($fColumns)>0){
                $columnIds = [];
                foreach($fColumns as $fColumn){
                    array_push($columnIds,$fColumn['id']);
                }
                $typeid = implode($columnIds, ",");
                $model = $fColumn['column_model'];//模型
            }else{
                echo "tag标签栏目标识不存在";
                die();
            }
        }

        if(empty($typeid)){
            if(empty($model)){
                $model = strtolower(request()->controller());
            }
            $action = \request()->action();
            if($action == "detail"){
                $id = \request()->param("id");
                $fdata = Db::name($model)->find($id);
                if($fdata){
                    $typeid = $fdata['column_id'];
                }
            }else{
                $typeid = \request()->param("id");
            }
        }else{
            if(empty($model)){
                $column = Column::field('column_model')->find($typeid);
                if($column){
                    $model = $column['column_model'];//模型
                }
            }
        }

        if (empty($model)) {
            echo '标签tag报错：没有模型';
            return false;
        }
        $sort = $param['sort'];//方式 new,month,rand,week
        $getall = $param['getall'];//获取类型 0 为当前内容页TAG标记,1为获取全部TAG标记
        $type = $param['type'];//son'表示当前下级栏目以及同栏目下；top'表示当前栏目及父级栏目；
        $row = $param['row'];//调用条数
        $way = $param['way'];//方式 1：独立；2：合并
        if($row == -1){
            $row = null;
        }
        $notypeid = $param['notypeid'];//去掉栏目id
        $tagArr = [];
        if($getall == 0){//为当前内容页TAG标记
            $action = \request()->action();
            if($action == "detail"){
                $id = \request()->param("id");
                $data = Db::name($model)->field('tags')->find($id);
                if($data){
                    $tags = $data['tags'];
                    $tagArr = explode(',', $tags);
                }
            }else{
                $dataList = Db::name($model)->field('tags')->where(['column_id'=>$typeid])->select();
                foreach ($dataList as $data){
                    $tags = $data['tags'];
                    $tagArrCur = explode(',', $tags);
                    $tagArr = array_merge($tagArr, $tagArrCur);
                }
            }
        }elseif ($getall == 1) {//全部TAG标记
            if($type == 'self'){//同栏目下
                $dataList = Db::name($model)->field('tags')->where(['column_id'=>$typeid])->select();
                foreach ($dataList as $data){
                    $tags = $data['tags'];
                    $tagArrCur = explode(',', $tags);
                    $tagArr = array_merge($tagArr, $tagArrCur);
                }
            }elseif ($type == 'son'){
                $dataList = get_column_down($typeid);
                foreach ($dataList as $data){
                    $currTypeid = $data['id'];
                    if(!empty($notypeid)){
                        if($notypeid == 'self'){
                            if($typeid ==  $currTypeid){
                                continue;
                            }
                        }else{
                            $notypeidArr = explode(",", $notypeid);
                            if(in_array($currTypeid, $notypeidArr)){
                                continue;
                            }
                        }
                    }
                    $currModel = $data['column_model'];
                    $currDataList = Db::name($currModel)->field("tags")->where(['column_id'=>$currTypeid])->select();
                    foreach ($currDataList as $currData){
                        $tags = $currData['tags'];
                        $tagArrCur = explode(',', $tags);
                        $tagArr = array_merge($tagArr, $tagArrCur);
                    }
                }
            }elseif ($type == 'top'){
                $dataList = get_column_up($typeid);
                foreach ($dataList as $data){
                    $currTypeid = $data['id'];
                    if(!empty($notypeid)){
                        if($notypeid == 'self'){
                            if($typeid ==  $currTypeid){
                                continue;
                            }
                        }else{
                            $notypeidArr = explode(",", $notypeid);
                            if(in_array($currTypeid, $notypeidArr)){
                                continue;
                            }
                        }
                    }
                    $currModel = $data['column_model'];
                    $currDataList = Db::name($currModel)->field("tags")->where(['column_id'=>$currTypeid])->select();
                    foreach ($currDataList as $currData){
                        $tags = $currData['tags'];
                        $tagArrCur = explode(',', $tags);
                        $tagArr = array_merge($tagArr, $tagArrCur);
                    }
                }
            }
        }
        $tagArr = array_unique($tagArr);//去重
        if(sizeof($tagArr) <= 0){
            return [];
        }
        $tagNames = implode(',',$tagArr);
        $query = Tag::field('id,name')->where('name','in', $tagNames);
        if($sort == 'new'){//最新tag标签
            $tagList = $query->order(['create_time'=>'desc'])->page(1, intval($row))->select();
        }elseif ($sort == 'month'){//本月热门tag标签
            $data = get_month_first_last(time());
            $month_first = $data['month_first'];
            $month_last = $data['month_last'];
            $tagList = $query->where([['create_time', ">=", $month_first." 00:00:00"], ['create_time', "<=", $month_last." 23:59:59"]])->order(["create_time"=>'asc'])->page(1, intval($row))->select();
        }elseif ($sort == 'week'){//本周热门tag标签
            $data = get_week_first_last(time());
            $week_first = $data['week_first'];
            $week_last = $data['week_last'];
            $tagList = $query->where([['create_time', ">=", $week_first." 00:00:00"], ['create_time', "<=", $week_last." 23:59:59"]])->order(["create_time"=>'asc'])->page(1, intval($row))->select();
        }elseif ($sort == 'rand'){//随机tag标签
            $sql = "select name from fox_tag where name in {$tagNames} order by rand() limit {$row}";
            $tagList = Db::query($sql);
        }else{
            $tagList = $query->order(['create_time'=>'asc'])->page(1, intval($row))->select();
        }
        $rdataList = [];
        $params = request()->param();//参数
        $disstyle = $param['disstyle'];//当前选择样式
        $action = \request()->action();
        $currFieldVal = $params['tags'];
        $fields = $params['fields'];
        $fieldsArr = [];
        if(!empty($fields)){
            $currFieldsArr = explode(",", $fields);
            $fieldsArr =  array_merge($fieldsArr, $currFieldsArr);
        }

        $view_suffix = config('view.view_suffix');
        $baseurl = request()->baseUrl();//基本路径
        if(str_ends_with($baseurl,".".$view_suffix)){
            $lastL = strripos($baseurl, ".".$view_suffix);
            $baseurl = substr($baseurl, 0, $lastL);
        }

        $isCurr = false;
        foreach ($tagList as $k=>$tag){
            $link = "javascript:void(0)";
            if($action == "detail") {
                $url = "/Tags/index";
                array_push($fieldsArr, 'tags');
                $fieldsArr = array_unique($fieldsArr);
                $tagsStr = implode(",", $fieldsArr);
                $param = ['id'=>$tag['id'], 'fields'=>$tagsStr, 'tags'=>$tag['name']];
                $link = tagSetUrl($url, $param);
            }else{
//                $params['id'] = $params['id'];
                array_push($fieldsArr, 'tags');
                $fieldsArr = array_unique($fieldsArr);
                $tagsStr = implode(",", $fieldsArr);
                if($way == 2){//合并
                    $params['fields'] = $tagsStr;
                    $params['tags'] = $tag['name'];
                    $link = tagSetUrl($baseurl, $params);
                }elseif($way == 1){//独立
                    $link = tagSetUrl($baseurl, ["id"=>$params["id"], "tags"=>$tag['name'], "fields"=>$tagsStr]);
                }
            }
            $rdata = ['link'=>$link, 'name'=>$tag['name'], 'target'=>' target="_blank" '];
            if($currFieldVal == $tag['name']){
                $rdata['disstyle'] = $disstyle;
                $isCurr = true;
            }else{
                $rdata['disstyle'] = "";
            }
            $isExist = false;
            foreach ($rdataList as $rd){
                if($rdata['name'] == $rd['name']){
                    $isExist = true;
                    break;
                }
            }
            if(!$isExist){
                array_push($rdataList, $rdata);
            }
        }
        array_unique($rdataList);
        if($showall == 1){//显示
            $firstLink = "javascript:void(0)";
            if($way == 2){//合并
                $params = request()->param();//参数
                $pvArr = array_values($params);
                $pkArr = array_keys($params);
                $newParams = [];
                foreach ($pvArr as $k=>$pv){
                    $isDel = false;
                    foreach ($rdataList as $rd){
                        if($rd['name']  == $pv){
                            $isDel = true;
                            break;
                        }
                    }
                    if(!$isDel){
                        $pk = $pkArr[$k];
                        $newParams["{$pk}"] = $params[$pk];
                    }
                }
                $firstLink = tagSetUrl($baseurl, $newParams);
            }elseif ($way == 1){//独立
                $firstLink = tagSetUrl($baseurl, ["id"=>$params['id']]);
            }
            $firstData = ['name'=>'全部', 'link'=>$firstLink];
            if(!$isCurr){
                $firstData['disstyle'] = $disstyle;
            }
            array_unshift($rdataList, $firstData);
        }
        return $rdataList;
    }
}