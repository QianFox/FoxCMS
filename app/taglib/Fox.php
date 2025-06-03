<?php

namespace app\taglib;

use app\common\model\DictData;
use think\facade\Db;
use think\template\TagLib;

class Fox extends Taglib
{
    // 标签定义
    protected $tags = [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'font1'     => ['attr' => 'size,color'],
        'basic'=>['attr'=>'name','close'=>0],//基本设置
        'contact'=>['attr'=>'name','close'=>0],//联系方式
        'nav'=>['attr' => 'name, type, typeid, titlelen, offset, row, limit, pname, calltype, notypeid, at, key, orderbyid, currentstyle, sortorder, sid', 'close' => 1],// 通用导航信息
        'sub'      => ['attr' => 'name,type,offset,row,columnId', 'close' => 1], // 子导航信息
        'link'       => ['attr' => 'name, key, orderby, sortorder', 'close' => 1],// 获取友情链接
        'static'     => ['attr' => 'file,lang,href,code', 'close' => 0],//引入静态文件
        'column'=>['attr'=>'id,name','close'=>0],//当前栏目
        'arclist'=>['attr'=>'name, orderby, flag ,titlelen, sortorder, typeid, offset, row, limit, type, pname, calltype, model, key, currentstyle, apply, notypeid, addfields, channel, sid', 'close'=>1],//类似文章列表
        'article'=>['attr'=>'name, orderby, flag ,titlelen, sortorder, typeid, offset, row, limit, type, pname, calltype, key, notypeid, sid', 'close'=>1],//栏目文章列表
        'product'=>['attr'=>'name, orderby, flag, titlelen, sortorder, typeid, offset, row, limit, type, pname, calltype, key, notypeid, sid', 'close'=>1],//栏目商品列表
        'images'=>['attr'=>'name, orderby, flag, titlelen, sortorder, typeid , offset, row, limit, type, pname, calltype, filter, key, notypeid, sid', 'close'=>1],//栏目图片集列表
        'single'=>['attr'=>'name,typeid, pname, calltype, filter, model, sid', 'close'=>0],//单页模式
        'searchform'=>['attr'=>'name, typeid,key', 'close'=>1],//搜索
        'gtz'=>['attr'=>'name,groupName,groupType', 'close'=>1],//合作公司
        'list'=>['attr'=>'orderby, orderway, pagesize, titlelen, typeid, name, flag, notypeid, model, key, addfields, channel, sid', 'close'=>1],//数据列表
        'pagelist'=>['attr'=>'listsize,listitem,currentstyle,disstyle,lang,indexdesc,predesc,nextdesc,enddesc', 'close'=>0],//数据列表
        'channel'=>['attr'=>'name, type, typeid, titlelen, offset, row, limit, pname, calltype, notypeid, at, key, orderbyid, currentstyle, sortorder, sid', 'close'=>1],//栏目通道
        'upward'=>['attr'=>'name, type, typeid, titlelen, offset, row, limit, key, sid', 'close'=>1],//栏目通道向上找
        'channeldata'=>['attr'=>'name,typeid,flag, type', 'close'=>1],//栏目通道数据
        'adv'       => ['attr' => 'pid, name, orderby, sortorder, type, key, sid', 'close' => 1],// 广告位图片
        'at'       => ['attr' => 'value, type, currentstyle, name', 'close' => 1],//判断是否当前
        'prenext'       => ['attr' => 'name,get, orderby, sortorder, disstyle, titlelen,key', 'close' => 1],//上一页下一页
        'imageslist'=>['attr'=>'name, typeid, model, calltype, pname, key, sid', 'close'=>1],//图片集
        'imagegroup'=>['attr'=>'name, field, pname, model, calltype, sortorder, key, typeid, sid', 'close'=>1],//图片组合
        'videogroup'=>['attr'=>'name, field, pname, model, calltype, type, typeid,key, sid', 'close'=>1],//视频组合
        'channelartlist'=>['attr' =>'name, orderby, titlelen, sortorder, typeid, offset, row, limit, notypeid, type,key, orderbyid, sid','close'=>1],//栏目集合
        'arcclick'   => ['attr' => 'typeid, model, type, calltype, pname', 'close' => 0],//资源点击数
        'position'=>['attr' =>'name, typeid, model, style, lang, sid', 'close' => 0],//面包屑
        'positionshut'=>['attr' =>'name, typeid, model, key, lang, sid', 'close' => 1],//面包屑闭合
        'diyform'=>['attr'=>'name,field,tablename,disstyle,way,showall,key', 'close'=>1],//枚举字段所有值
        'tag'=>['attr'=>'getall,calltype,name,sort,type,row,pname, model,notypeid,disstyle,way,showall,key,sid','close'=>1],//tag标签使用方法
        'is'=>['attr'=>'name,disstyle,key','close'=>1],//is标签判断参数
        'taggrounp'=>['attr'=>'pid,name,row,currentstyle,way,showall,key,param,target','close'=>1],//tag标签组使用方法
        'isdata'=>['attr'=>'name, orderby, flag , sortorder, typeid, offset, row, limit, type, pname, calltype, model, sid', 'close'=>0],//是否有数据
        'productparam'=>['attr'=>'name, orderby, sortorder, typeid, offset, row, limit, pname, calltype,key', 'close'=>1],//栏目商品属性列表
        'split'=>['attr'=>'id,name,field,tablename,key,type', 'close'=>1],//拆分标签
        'asklist'=>['attr'=>'name, orderby, sortorder, typeid, offset, row, limit, pname, calltype, key', 'close'=>1],//问答模板列表
        'searchgrounp'=>['attr'=>'pid,name,row,currentstyle,way,showall,key,param,orderby,sortorder','close'=>1],//搜索标签组使用方法
        'index'=>['attr'=>'name,path','close'=>0],//首页地址
        'language'=>['attr'=>'name,path,currentstyle,key,toggle','close'=>1],//语言
        'share'=>['attr'=>'key,name,code,param,type','close'=>1],//分享
        'download'=>['attr'=>'name, orderby, flag ,titlelen, sortorder, typeid, offset, row, limit, type, pname, calltype, key, notypeid, sid', 'close'=>1],//下载列表
        'sitemap'=>['attr'=>'title,type,target','close'=>0],//网站地图
        'lang'=>['attr'=>'name','close'=>0],//语言翻译编译
    ];

    //语言标识翻译
    public function tagLang($attr){
        $name = $attr['name'];
        $parse = '<?php ';
        $parse .= '$param = [\'name\'=>\''.$name.'\'];';
        $parse .= '$tagLang = new app\taglib\fox\TagLang;';
        $parse .= '$tagLang->getList($param);';
        $parse .= ' ?>';
        return $parse;
    }

    //网站地图
    public function tagSitemap($attr){
        $title = $attr['title']??"";
        $type = $attr['type']??"html";
        $target = $attr['target']??"_blank";
        $parse = '<?php ';
        $parse .= '$param = [\'title\'=>\''.$title.'\',\'type\'=>\''.$type.'\',\'target\'=>\''.$target.'\'];';
        $parse .= '$tagSitemap = new app\taglib\fox\TagSitemap;';
        $parse .= '$tagSitemap->getList($param);';
        $parse .= ' ?>';
        return $parse;
    }

    //下载列表
    public function tagDownload($tag, $content){
        $orderby = $tag['orderby']??'release_time';//默认发布时间排序
        $sortorder = $tag['sortorder']??'desc';//默认排序方式 倒序
        $ob = $orderby.' '.$sortorder;//排序
        $flag = $tag['flag']??"";//分类属性
        $titlelen = $tag['titlelen']??-1;//标题长度，单位字节 -1:表示显示全部
        $name    = $tag['name'] ?? 'field';
        $offset = !empty($tag['offset']) && is_numeric($tag['offset']) ? intval($tag['offset']) : 0;
        $row = !empty($tag['row']) && is_numeric($tag['row']) ? intval($tag['row']) : 99999;
        $typeid = $tag['typeid'];
        $limit = $tag['limit'];
        $type = $tag['type']??'self';
        $pname = $tag["pname"]??"field";//父标签名
        $calltype = $tag["calltype"]??"self";//标签调用
        $notypeid = $tag["notypeid"]??"";//排除id
        $index = $tag["key"]??"index";
        $parse = '<?php ';
        $parse .= '$param = [\'notypeid\'=>\''.$notypeid.'\', \'typeid\'=>\''.$typeid.'\', \'limit\'=>\''.$limit.'\',
         \'type\'=>\''.$type.'\', \'typeidP\'=>$'.$pname.'["id"]'.',\'calltype\'=>\''.$calltype.'\'];';
        $parse .= '$model = "article";';
        $parse .= '$tagDownload = new app\taglib\fox\TagDownload;';
        $parse .= '$__LIST__ = $tagDownload->getList($param,\''.$flag.'\',\''.$ob.'\',\''.$offset.'\',\''.$row.'\');';
        $parse .= '$__LIST__ = alterList($__LIST__,\'download\', '.$titlelen.');';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$index.'"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }

    public function tagShare($attr, $content){
        $name = $tag['name']??"field";//变量名
        $index = $tag["key"]??"index";
        $code = $attr['code']??"all";
        $param = $attr['param']??"";
        $type = $attr['type']??"font";
        $parse = '<?php ';
        $parse .= '$param = [\'code\'=>\''.$code.'\',\'param\'=>\''.$param.'\',\'type\'=>\''.$type.'\'];';
        $parse .= '$tagShare = new app\taglib\fox\TagShare;';
        $parse .= '$__LIST__ = $tagShare->getList($param);';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$index.'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    //首页地址
    public function tagIndex($attr){
        $name = $attr['name'];
        $path = $attr['path']??"";
        $parse = '<?php ';
        $parse .= '$param = [\'name\'=>\''.$name.'\',\'path\'=>\''.$path.'\'];';
        $parse .= '$tagIndex = new app\taglib\fox\TagIndex;';
        $parse .= '$tagIndex->getList($param);';
        $parse .= ' ?>';
        return $parse;
    }

    //语言
    public function tagLanguage($attr, $content){
        $name = $attr['name']??"field";//变量名
        $path = $attr['path']??"";
        $toggle = $attr['toggle']??"off";
        $currentstyle = $attr['currentstyle']??"";
        $index = $tag["key"]??"index";
        $parse = '<?php ';
        $parse .= '$param = [\'path\'=>\''.$path.'\',\'currentstyle\'=>\''.$currentstyle.'\',\'toggle\'=>\''.$toggle.'\'];';
        $parse .= '$tagLanguage = new app\taglib\fox\TagLanguage;';
        $parse .= '$__LIST__ = $tagLanguage->getList($param);';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$index.'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    //搜索组标签
    public function tagSearchgrounp($tag, $content)
    {
        $orderby = $tag['orderby']??'sort';//默认发布时间排序
        $sortorder = $tag['sortorder']??'desc';//默认排序方式 倒序
        $ob = $orderby.' '.$sortorder;//排序
        $name = $tag['name']??"field";//变量名
        $row = $tag['row']??"-1";//调用条数
        $currentstyle = $tag['currentstyle'];//当前选择样式
        $way = $tag['way']??"1";//方式 1：独立；2：合并
        $showall = $tag['showall']??"1";//是否显示全部 1：显示；0：不显示
        $index = $tag["key"]??"index";
        $param = $tag["param"]??"";
        $pid = $tag["pid"]??"-1";//默认查全部
        $parse = '<?php ';
        $parse .= '$param = [\'way\'=>\''.$way.'\',\'param\'=>\''.$param.'\',\'showall\'=>\''.$showall.'\', \'row\'=>\''.$row.'\', \'pid\'=>\''.$pid.'\', \'currentstyle\'=>\''.$currentstyle.'\'];';
        $parse .= '$tagSearchgrounp = new app\taglib\fox\TagSearchgrounp;';
        $parse .= '$__LIST__ = $tagSearchgrounp->getList($param,\''.$ob.'\');';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$index.'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    //问答模板列表
    public function tagAsklist($tag, $content){
        $orderby = $tag['orderby']??'sort';//默认发布时间排序
        $sortorder = $tag['sortorder']??'desc';//默认排序方式 倒序
        $ob = $orderby.' '.$sortorder;//排序
        $name    = $tag['name'] ?? 'field';
        $offset = !empty($tag['offset']) && is_numeric($tag['offset']) ? intval($tag['offset']) : 0;
        $row = !empty($tag['row']) && is_numeric($tag['row']) ? intval($tag['row']) : 99999;
        $typeid = $tag['typeid'];
        $limit = $tag['limit'];
        $pname = $tag["pname"]??"field";//父标签名
        $calltype = $tag["calltype"]??"self";//标签调用
        $index = $tag["key"]??"index";
        $parse = '<?php ';
        $parse .= '$param = [\'typeid\'=>\''.$typeid.'\', \'limit\'=>\''.$limit.'\', \'typeidP\'=>$'.$pname.'["id"]'.',\'calltype\'=>\''.$calltype.'\'];';
        $parse .= '$model = "ask";';
        $parse .= '$tagAsklist = new app\taglib\fox\TagAsklist;';
        $parse .= '$__LIST__ = $tagAsklist->getList($param,\''.$ob.'\',\''.$offset.'\',\''.$row.'\');';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$index.'"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }

    //拆分
    public function tagSplit($tag, $content)
    {
        $field = $tag['field'];//字段
        $tablename = $tag['tablename'];//表名
        $name = $tag['name']??"field";//变量名
        $index = $tag["key"]??"index";
        $type = $tag["type"]??"varchar";//字段类型 默认varchar  enum
        $id = $tag["id"]??"";
        $parse = '<?php ';
        $parse .= '$param = [\'type\'=>\''.$type.'\',\'id\'=>\''.$id.'\',\'field\'=>\''.$field.'\', \'tablename\'=>\''.$tablename.'\'];';
        $parse .= '$tagSplit = new app\taglib\fox\TagSplit;';
        $parse .= '$__LIST__ = $tagSplit->getList($param);';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$index.'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    //栏目商品属性列表
    public function tagProductparam($tag, $content){

        $orderby = $tag['orderby']??'create_time';//默认发布时间排序
        $sortorder = $tag['sortorder']??'asc';//默认排序方式 倒序
        $ob = $orderby.' '.$sortorder;//排序
        $name    = $tag['name'] ?? 'field';
        $offset = !empty($tag['offset']) && is_numeric($tag['offset']) ? intval($tag['offset']) : 0;
        $row = !empty($tag['row']) && is_numeric($tag['row']) ? intval($tag['row']) : 99999;
        $typeid = $tag['typeid'];
        $pname = $tag["pname"]??"field";//父标签名
        $calltype = $tag["calltype"]??"self";//标签调用
        $index = $tag["key"]??"index";

        $parse = '<?php ';
        $parse .= '$param = [\'typeid\'=>\''.$typeid.'\', \'typeidP\'=>$'.$pname.'["id"]'.',\'calltype\'=>\''.$calltype.'\'];';
        $parse .= '$tagProductparam = new app\taglib\fox\TagProductparam;';
        $parse .= '$__LIST__ = $tagProductparam->getList($param,\''.$ob.'\',\''.$offset.'\',\''.$row.'\');';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$index.'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    //是否有数据
    public function tagIsdata($tag, $content){
        $orderby = $tag['orderby']??'create_time';//默认发布时间排序
        $sortorder = $tag['sortorder']??'desc';//默认排序方式 倒序
        $ob = $orderby.' '.$sortorder;//排序
        $flag = $tag['flag']??"";//分类属性
        $name    = $tag['name'] ?? 'isfield';
        $offset = !empty($tag['offset']) && is_numeric($tag['offset']) ? intval($tag['offset']) : 0;
        $row = !empty($tag['row']) && is_numeric($tag['row']) ? intval($tag['row']) : 10;
        $typeid = $tag['typeid'];
        $sid = $tag['sid'];
        $limit = $tag['limit'];
        $type = $tag['type']??'';//默认根据后台栏目设置查询数据
        $pname = $tag["pname"]??"field";//父标签名
        $columnModel = $tag["model"]??"";//模型
        $calltype = $tag["calltype"]??"self";//标签调用

        $parse = '<?php ';
        $parse .= '$tagIsdata = new app\taglib\fox\TagIsdata;';
        $parse .= '$param = [\'typeid\'=>\''.$typeid.'\', \'limit\'=>\''.$limit.'\', \'columnModel\'=>\''.$columnModel.'\', \'type\'=>\''.$type.'\'
        , \'sid\'=>\''.$sid.'\', \'typeidP\'=>$'.$pname.'["id"]'.',\'calltype\'=>\''.$calltype.'\'];';
        $parse .= '$__LIST__ = $tagIsdata->getList($param,\''.$flag.'\',\''.$ob.'\',\''.$offset.'\',\''.$row.'\');';
        $parse .= '$'.$name.' = sizeof($__LIST__)?1:0;';
        $parse .= '?>';

        return $parse;
    }

    //tag标签
    public function tagTag($tag, $content)
    {
        $name = $tag['name']??"field";//变量名
        $sort = $tag['sort'];//方式 new,month,rand,week
        $getall = $tag['getall']??0;//获取类型 0 为当前内容页TAG标记,1为获取全部TAG标记
        $type = $tag['type']??'self';//son'表示下级栏目；top'表示顶级栏目；//self同栏目下
        $row = $tag['row']??"-1";//调用条数
        $typeid = $tag['typeid'];
        $sid = $tag['sid'];
        $pname = $tag['pname'];//父栏目变量
        $model = $tag['model']??"";//模型
        $notypeid = $tag['notypeid'];//去掉栏目id self//表示去掉自己 如果是其它表示去掉其它
        $calltype = $tag['calltype']??"self";
        $disstyle = $tag['disstyle'];//当前选择样式
        $way = $tag['way']??"1";//方式 1：独立；2：合并
        $showall = $tag['showall']??"1";//是否显示全部 1：显示；0：不显示
        $index = $tag["key"]??"index";
        $parse = '<?php ';
        if($calltype == "self"){
            $parse .= '$param = [\'sid\'=>\''.$sid.'\',\'showall\'=>\''.$showall.'\',\'way\'=>\''.$way.'\',\'getall\'=>\''.$getall.'\',\'typeid\'=>\''.$typeid.'\',\'sort\'=>\''.$sort.'\', \'type\'=>\''.$type.'\', \'row\'=>\''.$row.'\', \'model\'=>\''.$model.'\', \'notypeid\'=>\''.$notypeid.'\', \'disstyle\'=>\''.$disstyle.'\'];';
        }else{
            $parse .= '$param = [\'sid\'=>\''.$sid.'\',\'showall\'=>\''.$showall.'\',\'way\'=>\''.$way.'\',\'getall\'=>\''.$getall.'\',\'typeid\'=>\'$'.$pname.'["id"]'.'\',\'sort\'=>\''.$sort.'\', \'type\'=>\''.$type.'\', \'row\'=>\''.$row.'\', \'model\'=>\''.$model.'\', \'modelC\'=>$model, \'notypeid\'=>\''.$notypeid.'\', \'disstyle\'=>\''.$disstyle.'\'];';
        }
        $parse .= '$tagTag = new app\taglib\fox\TagTag;';
        $parse .= '$__LIST__ = $tagTag->getList($param);';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$index.'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    //tag组标签
    public function tagTaggrounp($tag, $content)
    {
        $name = $tag['name']??"field";//变量名
        $target = $tag['target']??"blank";//是否在本页
        $row = $tag['row']??"-1";//调用条数
        $currentstyle = $tag['currentstyle'];//当前选择样式
        $way = $tag['way']??"1";//方式 1：独立；2：合并
        $showall = $tag['showall']??"1";//是否显示全部 1：显示；0：不显示
        $index = $tag["key"]??"index";
        $param = $tag["param"]??"";
        $pid = $tag["pid"]??"-1";//默认查全部
        $parse = '<?php ';
        $parse .= '$param = [\'way\'=>\''.$way.'\',\'param\'=>\''.$param.'\',\'showall\'=>\''.$showall.'\', \'row\'=>\''.$row.'\'
        , \'pid\'=>\''.$pid.'\', \'currentstyle\'=>\''.$currentstyle.'\', \'target\'=>\''.$target.'\'];';
        $parse .= '$tagTaggrounp = new app\taglib\fox\TagTaggrounp;';
        $parse .= '$__LIST__ = $tagTaggrounp->getList($param);';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$index.'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    //判断参数是否存在
    public function tagIs($tag, $content)
    {
        $name = $tag['name']??"field";//变量名
        $disstyle = $tag['disstyle'];//当前选择样式
        $index = $tag["key"]??"index";
        $parse = '<?php ';
        $parse .= '$param = [\'disstyle\'=>\''.$disstyle.'\'];';
        $parse .= '$tagIs = new app\taglib\fox\TagIs;';
        $parse .= '$__LIST__ = $tagIs->getList($param);';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$index.'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    //枚举字段所有值
    public function tagDiyform($tag, $content)
    {
        $field = $tag['field'];//字段
        $tablename = $tag['tablename'];//表名
        $disstyle = $tag['disstyle'];//当前选择样式
        $name = $tag['name']??"field";//变量名
        $way = $tag['way']??"1";//方式 1：独立；2：合并
        $showall = $tag['showall']??"1";//是否显示全部 1：显示；0：不显示
        $index = $tag["key"]??"index";
        $parse = '<?php ';
        $parse .= '$param = [\'showall\'=>\''.$showall.'\',\'way\'=>\''.$way.'\',\'field\'=>\''.$field.'\', \'tablename\'=>\''.$tablename.'\', \'disstyle\'=>\''.$disstyle.'\'];';
        $parse .= '$tagDiyform = new app\taglib\fox\TagDiyform;';
        $parse .= '$__LIST__ = $tagDiyform->getList($param);';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$index.'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    //面包屑
    public function tagPosition($tag, $content){
        $name    = $tag['name']?? '';
        $typeid    = $tag['typeid']?? '';
        $id    = $tag['sid']?? '';
        $model    = $tag['model']?? '';
        $style    = $tag['style']?? '';
        $lang    = $tag['lang']?? '';
        $parse = '<?php ';
        $parse .= '$param = [\'typeid\'=>\''.$typeid.'\', \'model\'=>\''.$model.'\', \'style\'=>\''.$style.'\', \'lang\'=>\''.$lang.'\', \'id\'=>\''.$id.'\'];';
        $parse .= '$tagPosition = new app\taglib\fox\TagPosition;';
        $parse .= '$__VAL__ = $tagPosition->getList($param);';
        $parse .= 'echo $__VAL__;';
        $parse .= ' ?>';
        return $parse;
    }

    //面包屑闭合
    public function tagPositionshut($tag, $content){
        $name    = $tag['name']?? 'field';
        $typeid    = $tag['typeid']?? '';
        $sid    = $tag['sid']?? '';
        $model    = $tag['model']?? '';
        $style    = $tag['style']?? '';
        $index    = $tag['key']?? 'index';
        $lang    = $tag['lang']?? '';
        $parse = '<?php ';
        $parse .= '$param = [\'typeid\'=>\''.$typeid.'\', \'model\'=>\''.$model.'\', \'style\'=>\''.$style.'\', \'lang\'=>\''.$lang.'\', \'sid\'=>\''.$sid.'\'];';
        $parse .= '$tagPositionshut = new app\taglib\fox\TagPositionshut;';
        $parse .= '$__LIST__ = $tagPositionshut->getList($param);';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$index.'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    //资源点击数
    public function tagArcclick($tag, $content)
    {
        $typeid  = $tag["typeid"]??"";
        $model  = $tag["model"]??"";
        $type  = $tag["type"]??"view";
        $calltype = $tag['calltype']??"self";
        $pname = $tag['pname']??"field";
        $parse = '<?php ';
        // 查询数据库获取的数据集
        $parse .= '$param = [\'typeid\'=>\''.$typeid.'\', \'model\'=>\''.$model.'\', \'type\'=>\''.$type.'\', \'typeidP\'=>$'.$pname.'["id"]'
            .',\'calltype\'=>\''.$calltype.'\', \'column_id\'=>$'.$pname.'["column_id"]'.'];';
        $parse .= ' $tagArcclick = new app\taglib\fox\TagArcclick;';
        $parse .= ' $__VALUE__ = $tagArcclick->getList($param);';

        $parse .= ' echo $__VALUE__;';
        $parse .= ' ?>';
        return $parse;
    }

    //视频组
    public function tagVideogroup($tag, $content)
    {
        $field = $tag['field'];//字段
        $modelC = $tag['model']??"";//字段
        $name = $tag['name']??"video";//别名
        $pname = $tag['pname']??"field";
        $typeid = $tag['typeid']??"";
        $calltype = $tag['calltype']??"self";
        $type = $tag['type']??"";
        $index = $tag["key"]??"index";
        $parse = '<?php ';
        if($calltype == "self"){
            $parse .= '$param = [\'typeid\'=> \''.$typeid.'\', \'field\'=>\''.$field.'\', \'model\'=>\''.$modelC.'\', \'type\'=>\''.$type.'\'];';
        }else{
            $parse .= '$param = [\'typeid\'=>$'.$pname.'["id"]'.', \'field\'=>\''.$field.'\', \'modelC\'=>\''.$modelC.'\', \'model\'=>$model, \'type\'=>\''.$type.'\'];';
        }
        $parse .= '$tagVideogroup = new app\taglib\fox\TagVideogroup;';
        $parse .= '$__LIST__ = $tagVideogroup->getList($param);';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$index.'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    //图片组合
    public function tagImagegroup($tag, $content)
    {
        $field = $tag['field'];//字段
        $modelC = $tag['model']??"";//字段
        $name = $tag['name']??"img";//别名
        $pname = $tag['pname']??"field";
        $calltype = $tag['calltype']??"self";
        $sortorder = $tag['sortorder']??"asc";
        $index = $tag["key"]??"index";
        $typeid = $tag['typeid']??"";
        $sid = $tag['sid']??"";
        $parse = '<?php ';
        if($calltype == "self"){
            $parse .= '$param = [\'typeid\'=> \''.$typeid.'\', \'field\'=>\''.$field.'\', \'modelC\'=>\''.$modelC.'\', \'model\'=>$model, \'sortorder\'=>\''.$sortorder.'\'];';
        }else{
            $parse .= '$param = [\'typeid\'=> \''.$typeid.'\',\'typeidP\'=>$'.$pname.'["id"]'.', \'field\'=>\''.$field.'\', \'modelC\'=>\''.$modelC.'\', \'model\'=>$model, \'sortorder\'=>\''.$sortorder.'\'];';
        }
        $parse .= '$tagImagegroup = new app\taglib\fox\TagImagegroup;';
        $parse .= '$__LIST__ = $tagImagegroup->getList($param);';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"  key="'.$index.'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    //图片集
    public function tagImageslist(array $tag, string $content){
        $name    = $tag['name'] ?? 'field';
        $modelC    = $tag['model']??"";
        $typeid = $tag['typeid'];
        $index = $tag["key"]??"index";
        $pname = $tag['pname']??"field";
        $calltype = $tag['calltype']??"self";
        $parse = '<?php ';
        if($calltype == "self"){
            $parse .= '$param = [\'calltype\'=> \''.$calltype.'\', \'typeid\'=> \''.$typeid.'\', \'modelC\'=>\''.$modelC.'\', \'model\'=>$model];';
        }else{
            $parse .= '$param = [\'calltype\'=> \''.$calltype.'\', \'typeid\'=> \''.$typeid.'\',\'typeidP\'=>$'.$pname.'["id"]'.', \'modelC\'=>\''.$modelC.'\', \'model\'=>$model];';
        }

        $parse .= '$tagImageslist = new app\taglib\fox\TagImageslist;';
        $parse .= '$__LIST__ = $tagImageslist->getList($param);';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$index.'"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }

    //上一页下一页
    public function tagPrenext(array $tag, string $content){

        $name    = $tag['name'] ?? 'field';
        $disstyle    = $tag['disstyle']??"";
        $get    = $tag['get'];
        $orderby = $tag['orderby']??'create_time';//默认发布时间排序
        $sortorder = $tag['sortorder']??'desc';//默认排序方式 倒序
        $titlelen = $tag['titlelen']??'-1';//字数限制默认全部
        $ob = $orderby.' '.$sortorder;//排序
        $id = \request()->param("id");
        $columnModel = strtolower(request()->controller());
        $index = $tag["key"]??"index";
        $parse = '<?php ';
        $parse .= '$param = [\'id\'=>\''.$id.'\', \'get\'=>\''.$get.'\', \'disstyle\'=>\''.$disstyle.'\'];';
        $parse .= '$tagPrenext = new app\taglib\fox\TagPrenext;';
        $parse .= '$__LIST__ = $tagPrenext->getList($param,\''.$ob.'\');';
        $parse .= '$__LIST__ = alterList($__LIST__,\''.$columnModel.'\', '.$titlelen.');';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$index.'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    //判断是否当前
    public function tagAt(array $tag, string $content): string
    {
        $val = $tag["value"]??"index";
        $type = $tag["type"]??"yes";
        $name = $tag["name"]??"field";
        $currentstyle = $tag["currentstyle"];
        $controller = \request()->controller();
        if($type == "yes"){
            if(strcasecmp($val, $controller) != 0){
                $currentstyle = "";
            }
        }else{
            if(strcasecmp($val, $controller) == 0){
                $currentstyle = "";
            }
        }
        $parse = '<?php ';
        $parse .= '$'.$name.' = ["currentstyle"=>\''.$currentstyle.'\'];';
        $parse .= ' ?>';
        $parse .= $content;
        return $parse;

    }

    //font标签解析
    public function tagFont1(array $tag, string $content): string
    {
        $parseStr = '<span style="font-size: 20px; color: red">'.$content.'</span>';
        return $parseStr;
    }

    //单页模型或类似单页模型
    public function tagSingle($attr){

        $filed = $attr['name'];
        $typeid = $attr['typeid'];//获取当前栏目id
        $sid = $attr['sid'];//获取当前栏目标识
        $pname = $attr['pname']??"field";//获取当前栏目id
        $calltype = $attr['calltype']??"self";//标签调用方式
        $filter = $attr['filter']??"";//过滤函数
        $model = $attr['model']??"";//模型
        $parse = '<?php ';
        $parse .= '$param = [\'sid\'=>\''.$sid.'\',\'typeid\'=>\''.$typeid.'\',\'filed\'=>\''.$filed.'\', \'typeidP\'=>$'.$pname.'["id"]'.',\'calltype\'=>\''.$calltype.'\',\'filter\'=>\''.$filter.'\',\'model\'=>\''.$model.'\'];';
        $parse .= '$tagSingle = new app\taglib\fox\TagSingle;';
        $parse .= '$tagSingle->getList($param);';
        $parse .= ' ?>';

        return $parse;
    }

    //基本信息表中的字段的值
    public function tagBasic($attr){

        $field = $attr['name'];
        $parse = '<?php ';
        $parse .= '$param = [\'field\'=>\''.$field.'\', \'add\'=>$add];';
        $parse .= '$tagBasic = new app\taglib\fox\TagBasic;';
        $parse .= '$tagBasic->getList($param);';
        if($field == "aq" || $field == "copyright"){
            $parse .= '$add = 1;';
        }
        $parse .= ' ?>';
        return $parse;
    }

    //联系方式表中的字段的值
    public function tagContact($attr){
        $field = $attr['name'];
        $parse = '<?php ';
        $parse .= '$param = [\'field\'=>\''.$field.'\'];';
        $parse .= '$tagContact = new app\taglib\fox\TagContact;';
        $parse .= '$tagContact->getList($param);';
        $parse .= ' ?>';
        return $parse;
    }

    // 通用导航信息
    public function tagNav($tag, $content)
    {
        $titlelen = $tag['titlelen']??-1;//标题长度，单位字节 -1:表示显示全部
        $name    = $tag['name'] ?? 'field';
        $offset = !empty($tag['offset']) && is_numeric($tag['offset']) ? intval($tag['offset']) : 0;
        $row = !empty($tag['row']) && is_numeric($tag['row']) ? intval($tag['row']) : 999;
        $typeid = trim($tag['typeid']);//获取当前栏目id
        $sid = trim($tag['sid']);//获取当前栏目标识
        $limit = $tag['limit'];
        $type = $tag['type']??"self";
        $orderby = $tag['orderby']??'admin';//默认发布时间排序
        $sortorder = $tag['sortorder']??'asc';//默认排序方式
        $ob = $orderby.' '.$sortorder;//排序
        $currentstyle = $tag["currentstyle"];//样式
        $at = $tag["at"]??"yes";//是否只高亮所有
        $pname = $tag["pname"]??"field";//父标签名
        $calltype = $tag["calltype"]??"self";//标签调用
        $notypeid = $tag["notypeid"]??"contain";//是否包含自己 self排查 默认包含
        $orderbyid = $tag['orderbyid']??'sort';//默认有排序

        $id = \request()->param("id");
        $action = \request()->action();
        if($action == "detail"){//详情
            $columnModel = strtolower(request()->controller());
            $detailData = Db::name($columnModel)->field("column_id")->find($id);
            $id = $detailData["column_id"];
        }
        if($pname != "field"){//父栏目名称变了默认就是继承
            $calltype="parent";
        }
        $index = $tag["key"]??"index";
        $parse = '<?php ';
        $parse .= '$param = [\'typeid\'=>\''.$typeid.'\', \'orderbyid\'=>\''.$orderbyid.'\', \'limit\'=>\''.$limit.'\', \'sid\'=>\''.$sid.'\'
        , \'typeidP\'=>$'.$pname.'["id"]'.',\'calltype\'=>\''.$calltype.'\',\'notypeid\'=>\''.$notypeid.'\',\'orderby\'=>\''.$orderby.'\',\'sortorder\'=>\''.$sortorder.'\'];';
        $parse .= '$model = "column";';
        $parse .= '$tagNav = new app\taglib\fox\TagNav;';
        $parse .= '$__LIST__ = $tagNav->getList($param,\''.$type.'\',\''.$ob.'\',\''.$offset.'\',\''.$row.'\');';
        $parse .= '$__LIST__ = columnList($__LIST__, ' . $titlelen . ');';
        if(!empty($currentstyle) && !empty($id)){
            $parse .= 'navAddField($__LIST__, '.$id.', \''.$currentstyle.'\', \''.$at.'\');';
        }
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$index.'"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }

    // 通用导航子信息
    public function tagSub($tag, $content)
    {
        $name    = $tag['name'] ?? 'v';
        $type    = $tag['type'] ?? 'son';
        $offset = !empty($tag['offset']) && is_numeric($tag['offset']) ? intval($tag['offset']) : 0;
        $row = !empty($tag['row']) && is_numeric($tag['row']) ? intval($tag['row']) : 10;
        if($type == 'son'){
            $parase  = '<?php if(is_array($field["sub"]) || $field["sub"] instanceof \think\Collection || $field["sub"] instanceof \think\Paginator): $i = 0;';
            $parase .= '$__LIST__ = $field["sub"];';
            $parase .= '$__LIST__ = is_array($__LIST__) ? array_slice($__LIST__,' . $offset . ', $row, true) : $__LIST__->slice(' . $offset . ', '.$row.', true); ';
            $parase .= 'if( count($__LIST__)==0 ) : echo "" ;';
            $parase .= 'else: foreach($__LIST__ as $key=>$'.$name.'): $mod = ($i % 2 );++$i;?>';
            $parase .=$content;
            $parase .= '<?php endforeach; endif; else: echo "" ;endif; ?>';
            return $parase;
        }elseif ($type == 'top'){
            $columnId = $tag['columnId']??request()->param('id');//获取当前栏目id
            $parase = '<?php $__columns = \app\common\model\Column::where(pid,'.$columnId.')->select();';
            $parase .= '$__columns = is_array($__columns) ? array_slice($__columns,' . $offset . ', $row, true) : $__columns->slice(' . $offset . ', '.$row.', true); ';
            $parase .= '$__columns = alterChildColumn($__columns);';
            $parase.='if( count($__columns)==0 ) : echo "" ;';
            $parase .= 'else: foreach($__columns as $key=>$'.$name.'): ?>';
            $parase .=$content;
            $parase.='<?php endforeach; endif; ?>';
            return $parase;
        }

        return '';
    }

    //搜索
    public function tagSearchform($tag, $content)
    {

        $typeid = $tag['typeid'];
        $name    = $tag['name'] ?? 'field';
        $model    = $tag['model'];
        $index = $tag["key"]??"index";
        $parse = '<?php ';
        $parse .= '$param = [\'typeid\'=>\''.$typeid.'\',\'model\'=>\''.$model.'\'];';
        $parse .= '$tagSearchform = new app\taglib\fox\TagSearchform;';
        $parse .= '$__LIST__ = $tagSearchform->getList($param);';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$index.'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    // 当前栏目column
    public function tagColumn($tag)
    {
        $field = $tag['name'];
        $id   =  request()->param($tag['id']);//获取当前请求id
//        dump($id);
//        $column = \app\common\model\Column::field($field)->find($id);
//        return $column->$field;

        $p = '<?php echo htmlentities($single["column_id"]); ?>';
        return $p;

    }

    //合作公司
    public function tagGtz($tag, $content){

        $name    = $tag['name'] ?? 'v';
        $groupName    = $tag['groupName'] ?? '合作公司';
        $groupType    = $tag['groupType'] ?? '';
        if(empty($groupType) || $groupType == "undefined"){
            $dd = DictData::where(['dict_type'=>'fox_pic_group_type', 'dict_label'=>$groupName])->find();
            if($dd){
                $groupType = $dd['dict_value'];
            }
        }
        if(empty($groupType) || $groupType == "undefined"){
            return '暂无';
        }

        $parse = '<?php ';
        $parse .= '$__WHERE__ = [];';
        $parse .= '$__WHERE__[] = [\'file_group_type\', \'=\', '.$groupType.'];';
        $parse .='$__LIST__ = \app\common\model\UploadFiles::field(\'url\')->where($__WHERE__)->order(\'id desc\')->select();';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    //栏目文章列表
    public function tagArticle($tag, $content){
        $orderby = $tag['orderby']??'release_time';//默认发布时间排序
        $sortorder = $tag['sortorder']??'desc';//默认排序方式 倒序
        $ob = $orderby.' '.$sortorder;//排序
        $flag = $tag['flag']??"";//分类属性
        $titlelen = $tag['titlelen']??-1;//标题长度，单位字节 -1:表示显示全部
        $name    = $tag['name'] ?? 'field';
        $offset = !empty($tag['offset']) && is_numeric($tag['offset']) ? intval($tag['offset']) : 0;
        $row = !empty($tag['row']) && is_numeric($tag['row']) ? intval($tag['row']) : 99999;
        $typeid = $tag['typeid'];
        $sid = $tag['sid'];
        $limit = $tag['limit'];
        $type = $tag['type']??'self';
        $pname = $tag["pname"]??"field";//父标签名
        $calltype = $tag["calltype"]??"self";//标签调用
        $notypeid = $tag["notypeid"]??"";//排除id
        $index = $tag["key"]??"index";
        $parse = '<?php ';
        $parse .= '$param = [\'notypeid\'=>\''.$notypeid.'\', \'typeid\'=>\''.$typeid.'\', \'limit\'=>\''.$limit.'\', \'type\'=>\''.$type.'\'
        , \'sid\'=>\''.$sid.'\', \'typeidP\'=>$'.$pname.'["id"]'.',\'calltype\'=>\''.$calltype.'\'];';
        $parse .= '$model = "article";';
        $parse .= '$tagArticle = new app\taglib\fox\TagArticle;';
        $parse .= '$__LIST__ = $tagArticle->getList($param,\''.$flag.'\',\''.$ob.'\',\''.$offset.'\',\''.$row.'\');';
        $parse .= '$__LIST__ = alterList($__LIST__,\'article\', '.$titlelen.');';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$index.'"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }

    //栏目集合
    public function tagChannelartlist($tag, $content){

        $orderby = $tag['orderby']??'level';//默认发布时间排序
        $sortorder = $tag['sortorder']??'asc';//默认排序方式 倒序
        $orderbyid = $tag['orderbyid']??'sort';//默认有排序
        $ob = $orderby.' '.$sortorder;//排序
        $titlelen = $tag['titlelen']??-1;//标题长度，单位字节 -1:表示显示全部
        $name    = $tag['name'] ?? 'field';
        $offset = !empty($tag['offset']) && is_numeric($tag['offset']) ? intval($tag['offset']) : 0;
        $row = !empty($tag['row']) && is_numeric($tag['row']) ? intval($tag['row']) : 999;
        $type = $tag['type']??'top';
        $typeid = $tag['typeid'];
        $sid = ($tag['sid']);//获取当前栏目标识
        $notypeid = $tag['notypeid'];
        $limit = $tag['limit'];
        $index = $tag["key"]??"index";
        $parse = '<?php ';
        $parse .= '$param = [\'typeid\'=>\''.$typeid.'\', \'limit\'=>\''.$limit.'\', \'notypeid\'=>\''.$notypeid.'\'
        , \'sid\'=>\''.$sid.'\', \'type\'=>\''.$type.'\', \'orderbyid\'=>\''.$orderbyid.'\'];';
        $parse .= '$tagChannelartlist = new app\taglib\fox\TagChannelartlist;';
        $parse .= '$__LIST__ = $tagChannelartlist->getList($param,\''.$ob.'\',\''.$offset.'\',\''.$row.'\');';
        $parse .= '$__LIST__ = columnList($__LIST__, ' . $titlelen . ');';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$index.'"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }

    //栏目产品列表
    public function tagProduct($tag, $content){

        $orderby = $tag['orderby']??'release_time';//默认发布时间排序
        $sortorder = $tag['sortorder']??'desc';//默认排序方式 倒序
        $ob = $orderby.' '.$sortorder;//排序
        $flag = $tag['flag']??"";//分类属性
        $titlelen = $tag['titlelen']??-1;//标题长度，单位字节 -1:表示显示全部
        $name    = $tag['name'] ?? 'field';
        $offset = !empty($tag['offset']) && is_numeric($tag['offset']) ? intval($tag['offset']) : 0;
        $row = !empty($tag['row']) && is_numeric($tag['row']) ? intval($tag['row']) : 99999;
        $typeid = $tag['typeid'];
        $sid = $tag['sid'];
        $limit = $tag['limit'];
        $type = $tag['type']??'top';
        $pname = $tag["pname"]??"field";//父标签名
        $calltype = $tag["calltype"]??"self";//标签调用
        $index = $tag["key"]??"index";
        $notypeid = $tag["notypeid"]??"";//排除id
        $parse = '<?php ';
        $parse .= '$param = [\'notypeid\'=>\''.$notypeid.'\',\'typeid\'=>\''.$typeid.'\', \'limit\'=>\''.$limit.'\'
        , \'sid\'=>\''.$sid.'\',\'type\'=>\''.$type.'\',  \'typeidP\'=>$'.$pname.'["id"]'.',\'calltype\'=>\''.$calltype.'\'];';
        $parse .= '$model = "product";';
        $parse .= '$tagProduct = new app\taglib\fox\TagProduct;';
        $parse .= '$__LIST__ = $tagProduct->getList($param,\''.$flag.'\',\''.$ob.'\',\''.$offset.'\',\''.$row.'\');';
        $parse .= '$__LIST__ = alterList($__LIST__,\'product\', '.$titlelen.');';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$index.'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    //栏目图片列表
    public function tagImages($tag, $content){
        $orderby = $tag['orderby']??'release_time';//默认发布时间排序
        $sortorder = $tag['sortorder']??'desc';//默认排序方式 倒序
        $ob = $orderby.' '.$sortorder;//排序
        $flag = $tag['flag']??"";//分类属性
        $titlelen = $tag['titlelen']??-1;//标题长度，单位字节 -1:表示显示全部
        $name    = $tag['name'] ?? 'field';
        $offset = !empty($tag['offset']) && is_numeric($tag['offset']) ? intval($tag['offset']) : 0;
        $row = !empty($tag['row']) && is_numeric($tag['row']) ? intval($tag['row']) : 99999;
        $typeid = $tag['typeid'];
        $sid = $tag['sid'];
        $limit = $tag['limit'];
        $type = $tag['type']??'top';
        $pname = $tag["pname"]??"field";//父标签名
        $calltype = $tag["calltype"]??"self";//标签调用
        $index = $tag["key"]??"index";
        $notypeid = $tag["notypeid"]??"";//排除id
        $parse = '<?php ';
        $parse .= '$param = [\'notypeid\'=>\''.$notypeid.'\',\'typeid\'=>\''.$typeid.'\', \'limit\'=>\''.$limit.'\'
        , \'sid\'=>\''.$sid.'\', \'type\'=>\''.$type.'\', \'typeidP\'=>$'.$pname.'["id"]'.',\'calltype\'=>\''.$calltype.'\'];';
        $parse .= '$tagImages = new app\taglib\fox\TagImages;';
        $parse .= '$model = "images";';
        $parse .= '$__LIST__ = $tagImages->getList($param,\''.$flag.'\',\''.$ob.'\',\''.$offset.'\',\''.$row.'\');';
        $parse .= '$__LIST__ = alterList($__LIST__,\'images\', '.$titlelen.');';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$index.'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    //栏目通道向下找
    public function tagChannel($tag, $content){

        $titlelen = $tag['titlelen']??-1;//标题长度，单位字节 -1:表示显示全部
        $name    = $tag['name'] ?? 'field';
        $offset = !empty($tag['offset']) && is_numeric($tag['offset']) ? intval($tag['offset']) : 0;
        $row = !empty($tag['row']) && is_numeric($tag['row']) ? intval($tag['row']) : 999;
        $typeid = ($tag['typeid']);//获取当前栏目id
        $sid = ($tag['sid']);//获取当前栏目标识
        $limit = $tag['limit'];
        $type = $tag['type']??"top";
        $orderby = $tag['orderby']??'admin';//默认发布时间排序
        $sortorder = $tag['sortorder']??'asc';//默认排序方式
        $ob = $orderby.' '.$sortorder;//排序
        $currentstyle = $tag["currentstyle"];//样式
        $at = $tag["at"]??"yes";//是否只高亮所有
        $pname = $tag["pname"]??"field";//父标签名
        $calltype = $tag["calltype"]??"self";//标签调用
        $notypeid = $tag["notypeid"]??"contain";//是否包含自己 self排查 默认包含
        $orderbyid = $tag['orderbyid']??'sort';//默认有排序
        $id = \request()->param("id");

        $action = \request()->action();
        if($action == "detail"){//详情
            $columnModel = strtolower(request()->controller());
            $detailData = Db::name($columnModel)->field("column_id")->find($id);
            $id = $detailData["column_id"];
        }
        if(empty($id)){
            $id = $typeid;
        }
        $index = $tag["key"]??"index";
        $parse = '<?php ';
        $parse .= '$param = [\'typeid\'=>\''.$typeid.'\', \'orderbyid\'=>\''.$orderbyid.'\', \'limit\'=>\''.$limit.'\', \'limit\'=>\''.$limit.'\', \'sid\'=>\''.$sid.'\'
        , \'typeidP\'=>$'.$pname.'["id"]'.',\'calltype\'=>\''.$calltype.'\',\'notypeid\'=>\''.$notypeid.'\',\'orderby\'=>\''.$orderby.'\',\'sortorder\'=>\''.$sortorder.'\'];';
        $parse .= '$model = "column";';
        $parse .= '$tagChannel = new app\taglib\fox\TagChannel;';
        $parse .= '$__LIST__ = $tagChannel->getList($param,\''.$type.'\',\''.$ob.'\',\''.$offset.'\',\''.$row.'\');';
        $parse .= '$__LIST__ = columnList($__LIST__, ' . $titlelen . ');';
        if(!empty($currentstyle) && !empty($id)){
            $parse .= 'navAddField($__LIST__, '.$id.', \''.$currentstyle.'\', \''.$at.'\');';
        }
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$index.'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    //栏目通道向上找
    public function tagUpward($tag, $content){

        $titlelen = $tag['titlelen']??-1;//标题长度，单位字节 -1:表示显示全部
        $name    = $tag['name'] ?? 'field';
        $offset = !empty($tag['offset']) && is_numeric($tag['offset']) ? intval($tag['offset']) : 0;
        $row = !empty($tag['row']) && is_numeric($tag['row']) ? intval($tag['row']) : 999;
        $typeid = ($tag['typeid']);//获取当前栏目id
        $sid = ($tag['sid']);//获取当前栏目标识
        $limit = $tag['limit'];
        $type = $tag['type']??"top";
        $orderby = $tag['orderby']??'create_time';//默认发布时间排序
        $sortorder = $tag['sortorder']??'asc';//默认排序方式 倒序
        $ob = $orderby.' '.$sortorder;//排序
        $currentstyle = $tag["currentstyle"];//样式
        $id = \request()->param("id");
        $action = \request()->action();
        if($action == "detail"){//详情
            $columnModel = strtolower(request()->controller());
            $detailData = Db::name($columnModel)->field("column_id")->find($id);
            $id = $detailData["column_id"];
        }
        if(empty($id)){
            $id = $typeid;
        }
        $index = $tag["key"]??"index";
        $parse = '<?php ';
        $parse .= '$param = [\'typeid\'=>\''.$typeid.'\', \'limit\'=>\''.$limit.'\', \'sid\'=>\''.$sid.'\'];';
        $parse .= '$tagUpward = new app\taglib\fox\TagUpward;';
        $parse .= '$__LIST__ = $tagUpward->getList($param,\''.$type.'\',\''.$ob.'\',\''.$offset.'\',\''.$row.'\');';
        $parse .= '$__LIST__ = columnList($__LIST__, ' . $titlelen . ');';
        if(!empty($currentstyle) && !empty($id)){
            $parse .= 'navAddField($__LIST__, '.$id.', \''.$currentstyle.'\');';
        }
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$index.'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    //栏目通道数据
    public function tagChanneldata($tag, $content){

        $name    = $tag['name'] ?? 'field';
        $columnId = ($tag['typeid']??request()->param('id'))??request()->param('columnId');//获取当前栏目id
        $flag = $tag['flag']??"";//分类属性
        $parse = '<?php ';
        $parse .= '
            $__id__=$field[\'id\'];
            $__WHERE__ = [\'column_id\'=>$__id__];
            $__columnModel__=$field[\'column_model\'];
            $__LIST__ = [];
            ';
        $parse .='think\facade\Db::name($__columnModel__)->where($__WHERE__)->chunk(5,function($items) use(&$__LIST__){
        
            foreach ($items as $item){
                $uf = think\facade\Db::name("upload_files")->find($item["breviary_pic_id"]);
                $imgUrl = "";
                if($uf){
                     $imgUrl = $uf[\'url\'];
                }
                $item["imgUrl"] = $imgUrl;
                if(!empty($item["picset_ids"])){
                    $ufs = think\facade\Db::name("upload_files")->field("url")->whereIn("id",$item["picset_ids"])->select();
                    $picsetArr = [];
                    if(sizeof($ufs)> 0){
                        foreach($ufs as $us){
                            array_push($picsetArr, $us[\'url\']);
                        }
                    }
                    $item["picset"] =implode(\',\', $picsetArr);
                }
                array_push($__LIST__, $item);
            }
       });';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    // 获取友情链接信息
    public function tagLink($tag, $content)
    {
        $orderby = $tag['orderby']??'sort';//默认发布时间排序
        $sortorder = $tag['sortorder']??'desc';//默认排序方式 倒序
        $ob = $orderby.' '.$sortorder;//排序
        $name  = $tag['name'] ?? 'field';
        $index = $tag["key"]??"index";
        $parse = '<?php ';
        $parse .= '$param = [];';
        $parse .= '$tagLink = new app\taglib\fox\TagLink;';
        $parse .= '$__LIST__ = $tagLink->getList($param,\''.$ob.'\');';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$index.'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;


    }

    private function getLang(){
        $lang = request()->param("lang");
        if(empty($lang)){
            $lang = xn_cfg("base.home_lang");
        }
        return $lang;
    }

    // 广告位-广告
    public function tagAdv($tag, $content)
    {
        $name  = $tag['name'] ?? 'ad';
        $pid  = $tag['pid'] ?? 0;
        $sid  = $tag['sid'] ?? "";
        $type  = $tag['type'] ?? 'son';
        $visit_lang = request()->param("lang");
        if(empty($lang)){
            $visit_lang = $this->getLang();
        }
        if(empty($pid)){
            if(empty($sid)){
                echo "adv标签,广告位标识不能为空";
                die();
            }
            $advS = \app\common\model\AdvertisingSpace::where(['lang'=>$visit_lang, 'sid'=>$sid])->find();
            $pid = $advS['id'];
        }else{
            $advS = \app\common\model\AdvertisingSpace::find($pid);
        }
        if(!$advS || ($advS["status"] == 0)){
            $status = $advS["status"]??0;
            $parse = '<?php $status='.$status.'; ?>';
            $parse .= '<?php if($status == 1): ?>';
            $parse .= $content;
            $parse .= '<?php endif; ?>';
            return $parse;
        }
        $index = $tag["key"]??"index";
        if($type == 'son'){
            $orderby = $tag['orderby']??'sort';//默认发布时间排序
            $sortorder = $tag['sortorder']??'asc';//默认排序方式 倒序
            $ob = $orderby.' '.$sortorder;//排序
            $parse = '<?php ';
            $parse .= '$model = "slide";';
            $parse .= '
            $__WHERE__ = [];
            $__WHERE__[] = [\'status\', \'=\', 1];
            $__WHERE__[] = [\'advertising_space_id\', \'=\', '.$pid.'];
            ';
            $parse .= '$__LIST__ = \app\common\model\Slide::where($__WHERE__)->order(\''.$ob.'\')->select();';
            $parse .= ' ?>';
            $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$index.'"}';
            $parse .= $content;
            $parse .= '{/volist}';
            return $parse;
        }elseif($type == 'self'){
            $parse = '<?php ';
                $parse .= '$'.$name.' = \app\common\model\AdvertisingSpace::find('.$pid.');';
            $parse .= ' ?>';
            $parse .= $content;
            return $parse;
        }
    }

    //公用列表
    public function tagArclist($tag, $content){
        $orderby = $tag['orderby']??'create_time';//默认发布时间排序
        $sortorder = $tag['sortorder']??'desc';//默认排序方式 倒序
        $ob = $orderby.' '.$sortorder;//排序
        $flag = $tag['flag']??"";//分类属性
        $titlelen = $tag['titlelen']??-1;//标题长度，单位字节 -1:表示显示全部
        $name    = $tag['name'] ?? 'field';
        $offset = !empty($tag['offset']) && is_numeric($tag['offset']) ? intval($tag['offset']) : 0;
        $row = !empty($tag['row']) && is_numeric($tag['row']) ? intval($tag['row']) : 99999;
        $typeid = $tag['typeid'];
        $sid = $tag['sid'];
        $limit = $tag['limit'];
        $type = $tag['type']??'';
        $pname = $tag["pname"]??"field";//父标签名
        $columnModel = $tag["model"]??"";//模型 默认查全部类似文章模型
        $calltype = $tag["calltype"]??"self";//标签调用
        $currentstyle = $tag["currentstyle"]??"";//是否当前文章id
        $index = $tag["key"]??"index";
        $apply = $tag["apply"]??"article";//标签应用场景默认文章内容中使用
        $notypeid = $tag["notypeid"]??"";//排除id
        $addfields = $tag["addfields"]??"";//增加字段
        $channel = $tag["channel"]??"";//增加字段模型
        $parse = '<?php ';
        $parse .= '$tagArclist = new app\taglib\fox\TagArclist;';
        $parse .= '$param = [\'notypeid\'=>\''.$notypeid.'\',\'apply\'=>\''.$apply.'\',\'currentstyle\'=>\''.$currentstyle.'\'
        ,\'typeid\'=>\''.$typeid.'\', \'limit\'=>\''.$limit.'\', \'columnModel\'=>\''.$columnModel.'\', \'type\'=>\''.$type.'\', \'sid\'=>\''.$sid.'\'
        ,  \'typeidP\'=>$'.$pname.'["id"]'.',\'calltype\'=>\''.$calltype.'\',\'addfields\'=>\''.$addfields.'\',\'channel\'=>\''.$channel.'\'];';
        $parse .= '$__LIST__ = $tagArclist->getList($param,\''.$flag.'\',\''.$ob.'\',\''.$offset.'\',\''.$row.'\');';
        $columnModel = "";
        $parse .= '$__LIST__ = alterList($__LIST__,\''.$columnModel.'\', '.$titlelen.');';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$index.'"}';
        $parse .= '<?php $model = $'.$name.'["model"]; ?>';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }

    //列表
    public function tagList($tag, $content){

        $flag = $tag['flag']??"";//分类属性
        $orderby = $tag['orderby']??'create_time';//默认发布时间排序
        $orderway = $tag['orderway']??'desc';//默认排序方式 倒序
        $ob = $orderby.' '.$orderway;//排序
        $pagesize = $tag['pagesize']??10;//分类属性
        $titlelen = $tag['titlelen']??-1;//标题长度，单位字节 -1:表示显示全部
        $name    = $tag['name'] ?? 'field';
        $typeid = $tag['typeid']??"";//栏目id
        $sid = $tag['sid']??"";//栏目标识
        $notypeid = $tag['notypeid']??"";//排查的栏目id
        $columnModel = $tag["model"]??"";//模型
        $addfields = $tag["addfields"]??"";//增加字段
        $channel = $tag["channel"]??"";//增加字段模型

        $page = request()->param('page');
        //生成路由 //1:动态url,2:伪静态化,3:静态页面
        $url_model = xn_cfg("seo.url_model");
        if($url_model == 2){
            $baseurl = request()->baseUrl();//基本路径
            if((empty($page) || $page == null) && (stripos($baseurl,'_') != false)){
                $lastH =strripos($baseurl,"_") + 1;
                $clickPageStr = mb_substr($baseurl, $lastH) ;
                $clickPageStr = str_replace(".".config('view.view_suffix'), "", $clickPageStr);
                if(!empty($clickPageStr) && $clickPageStr != null){
                    if(is_numeric($clickPageStr)){
                        $page = intval($clickPageStr);
                    }
                }
            }
        }
        if(empty($page) || $page == null){
            $page = 1;
        }

        $index = $tag["key"]??"index";
        $parse = '<?php ';
        $parse .= '$param = [\'sid\'=>\''.$sid.'\', \'columnModel\'=>\''.$columnModel.'\',\'addfields\'=>\''.$addfields.'\',\'channel\'=>\''.$channel.'\',\'typeid\'=>\''.$typeid.'\'];';
        $parse .= '$tagList = new app\taglib\fox\TagList;';
        $parse .= '$__rdata__ = $tagList->getList($param,\''.$flag.'\',\''.$ob.'\','.$page.',\''.$pagesize.'\',\''.$notypeid.'\');';
        $parse .= '$__PAGES__ = $__rdata__;';
//        $parse .= '$model = \''.$columnModel.'\';';
        $columnModel = "";
        $parse .= '$__LIST__ = alterList($__rdata__[\'datas\'],\''.$columnModel.'\', '.$titlelen.');';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $name . '" key="'.$index.'"}';
        $parse .= '<?php $model = $'.$name.'["model"]; ?>';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    //分页
    public function tagPagelist($tag, $content){

        $listitem = $tag["listitem"];
        $listsize = $tag["listsize"];
        $currentstyle = $tag["currentstyle"]??"active";
        $disstyle = $tag["disstyle"]??"disabled";
        $diystyle = $tag["diystyle"]??"";
        $lang = $tag["lang"]??"";
        $indexdesc = $tag["indexdesc"]??"";
        $predesc = $tag["predesc"]??"";
        $nextdesc = $tag["nextdesc"]??"";
        $enddesc = $tag["enddesc"]??"";
        $parse = '<?php ';
        $parse .= ' $__PAGES__ = isset($__PAGES__) ? $__PAGES__ : "";';
        $parse .= ' $model = isset($model) ? $model : "";';
        $parse .= ' $tagPagelist = new app\taglib\fox\TagPagelist;';
        $parse .= '$param = [\'listitem\'=>\''.$listitem.'\',\'listsize\'=>\''.$listsize.'\',\'currentstyle\'=>\''.$currentstyle.'\',\'diystyle\'=>\''.$diystyle.'\'
        , \'disstyle\'=>\''.$disstyle.'\', \'lang\'=>\''.$lang.'\', \'indexdesc\'=>\''.$indexdesc.'\', \'predesc\'=>\''.$predesc.'\', \'nextdesc\'=>\''.$nextdesc.'\', \'enddesc\'=>\''.$enddesc.'\'];';
        $parse .= ' $__VALUE__ = $tagPagelist->getPagelist($__PAGES__, $param);';
        $parse .= ' echo $__VALUE__;';
        $parse .= ' ?>';
        return $parse;
    }


    //static 标签解析
    public function tagStatic($tag)
    {
        $file  = isset($tag['file']) ? $tag['file'] : '';
        $file = $this->varOrvalue($file);
        $href  = isset($tag['href']) ? $tag['href'] : '';
        $href = $this->varOrvalue($href);
        $lang = !empty($tag['lang']) ? $tag['lang'] : '';
        $lang = $this->varOrvalue($lang);
        $code = !empty($tag['code']) ? $tag['code'] : '';
        $parseStr = '<?php ';
        // 查询数据库获取的数据集
        $parseStr .= ' $tagStatic = new app\taglib\fox\TagStatic;';
        $parseStr .= ' $__VALUE__ = $tagStatic->getStatic('.$file.','.$lang.','.$href.',"'.$code.'");';
        $parseStr .= ' echo $__VALUE__;';
        $parseStr .= ' ?>';

        if (!empty($parseStr)) {
            return $parseStr;
        }
        return;
    }

    //自动识别构建变量，传值可以使变量也可以是值
    private function varOrvalue($value)
    {
        $flag  = substr($value, 0, 1);
        if ('$' == $flag || ':' == $flag) {
            $value = $this->autoBuildVar($value);
        } else {
            $value = str_replace('"', '\"', $value);
            $value = '"' . $value . '"';
        }

        return $value;
    }

}