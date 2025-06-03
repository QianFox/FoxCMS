<?php

namespace app\taglib\fox;
use app\common\model\Column;
use think\facade\Db;

/**
 * 面包屑
 */
class TagPosition extends TagBase
{

    /**
     * 查询数据
     */
    public function getList($param)
    {
        $typeid = $param["typeid"];//栏目id
        $model = $param["model"];//模型
        $style = $param["style"];//样式
        $lang = $param["lang"];//语言
        $visit_lang = $this->getLang();

        $sid = $param["sid"];//栏目标识
        if(empty($typeid) && !empty($sid)){
            $fColumns = Column::field("id")->whereIn("nid",$sid)->where(['lang'=>$visit_lang])->select();
            if(sizeof($fColumns)>0){
                $columnIds = [];
                foreach($fColumns as $fColumn){
                    array_push($columnIds,$fColumn['id']);
                }
                $typeid = implode($columnIds, ",");
            }else{
                echo "position标签栏目标识不存在";
                die();
            }
        }

        if(empty($typeid)){
            $id = request()->param("id");
            $action = request()->action();
            if($action == "detail"){//详情
                $columnModel = strtolower(request()->controller());
                $data = Db::name($columnModel)->field("column_id")->find($id);
                if(!$data){
                    return [];
                }
                $id = $data['column_id'];
            }
            $typeid = (String)$id;
        }

        if(!empty($lang)){
            $visit_lang = $lang;
        }
        $index_name = getLangContentByMark($visit_lang, "home")['value'];
        if(empty($index_name)){
            $index_name = "首页";
        }
        $fname = "";
        $home_lang = xn_cfg("base.home_lang");//默认语言
        if(!($home_lang == $visit_lang)){
            $fname = "_".$visit_lang;
        }

        $allColumn = get_column_up($typeid, $model, $visit_lang);

        //生成路由 //1:动态url,2:伪静态化,3:静态页面
        $url_model = xn_cfg("seo.url_model");
        $url_html_suffix = config("route.url_html_suffix");

        if ($url_model == 3){
            $link = "/index{$fname}.".$url_html_suffix;
        }else{
            $baseurl = request()->domain();//基本路径
            $link = "/";
            if($url_model == 1){
                $fname2 = "";
                if($visit_lang != $home_lang){
                    $fname2 = "?lang={$visit_lang}";
                }
                $link = "/Index/index{$fname2}";
                if(!check_url($baseurl.$link)){
                    $link = "/index.php/{$link}";
                }
            }elseif ($url_model == 2){//2:伪静态化
                $link = "/Index/index{$fname}.{$url_html_suffix}";
                if(!check_url($baseurl.$link)){
                    $link = "/index.php/$link";
                }
            }
        }

        $rlist = ['<a class="'.$style.'" href="'.$link.'">'.$index_name.'</a>'];

        foreach ($allColumn as $column){
            if($column["column_attr"] == 1){//外部链接
                $url = $column["out_link_head"].$column["out_link"];
            }elseif ($column["column_attr"] == 2){//内链栏目
                $innerColumn = \app\common\model\Column::find($column["inner_column"]);
                $vPath = $innerColumn["v_path"];
                $url = resetUrl($vPath, $innerColumn["id"], $visit_lang);
            }else{
                $vPath = $column["v_path"];
                $url = resetUrl($vPath, $column["id"], $visit_lang);
            }
            array_push($rlist,'<a class="'.$style.'" href="'.$url.'">'.$column['name'].'</a>');
        }
        return implode("", $rlist);
    }

}