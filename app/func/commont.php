<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   17:42
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   17:42
 */

use think\facade\Db;
use app\admin\util\TableUtil;

//获取基本设置对象
function getBasic()
{
    //获取静态文件存放目录
    $basic = \app\common\model\Basic::where("status", 1)->find();
    return $basic;
}

//组合多维数组
function unlimitedForLayer($cate, $name = 'sub', $pid = 0)
{
    $arr = array();
    foreach ($cate as $v) {
        if ($v['pid'] == $pid) {
            $v["link"] = getColumnUrl($v);
            $v[$name] = unlimitedForLayer($cate, $name, $v['id']);
            $arr[]    = $v;
        }
    }
    return $arr;
}

//子栏目重新组装数据
function alterChildColumn($list)
{
    $arr = array();
    foreach ($list as $v) {
        $v['url'] = $v['v_path'];
        $arr[]    = $v;
    }
    return $arr;
}
//栏目地址重新组装
function columnList($list,  $titlelen = -1)
{
    $arr = array();
    $table_prefix = config("database.connections.mysql.prefix");
    foreach ($list as $v) {
        $v["link"] = getColumnUrl($v);
        $v['source'] = $v['article_source'];
        if ($titlelen != -1 && $titlelen > 0 && strlen($v['title']) > 0) {
            $v['title'] = myTitle($v['title'], $titlelen);
        }

        $columnList = get_column_down($v['id']);
        $v['child_num'] = sizeof($columnList); //子栏目数
        $sum = 0;
        foreach ($columnList as $column) {
            $table_name = $table_prefix . $column['column_model'];
            if (TableUtil::check_table($table_name)) {
                $sum += Db::name($column['column_model'])->where('column_id', $column['id'])->count();
            }
        }
        $v['sum'] = $sum;
        $arr[]    = $v;
    }
    return $arr;
}

//获取父类对象
function getParent($list, $pid)
{

    foreach ($list as $data) {
        if ($pid == $data->id) {
            return $data;
        }
    }
    return [];
}

//栏目地址重新组装
function alterList($list, $pmodel = "", $titlelen = -1)
{
    //生成路由 //1:动态url,2:伪静态化,3:静态页面
    $url_model = xn_cfg("seo.url_model");
    $url_html_suffix = config("route.url_html_suffix");
    $arr = array();
    $basic = getBasic(); //基本信息
    $isMobile = is_mobile();
    $home_lang = xn_cfg("base.home_lang"); //默认语言

    foreach ($list as $v) {
        $html_save_path = $basic["html_save_path"];
        $cur_lang = $v['visit_lang'];
        if ($home_lang == $cur_lang) {
            $cur_lang = "";
        } else {
            $html_save_path = $cur_lang;
        }
        if (empty($pmodel)) {
            $model = $v["model"];
        } else {
            $model = $pmodel;
        }
        if ($v["id"] == -1) {
            $v["link"] = "javascript:void(0);";
        } else {
            if ($url_model == 1) {
                $nUrl = DIRECTORY_SEPARATOR . $model . DIRECTORY_SEPARATOR . 'detail';
                if (empty($cur_lang)) {
                    $v["link"] = url("{$nUrl}") . "?id={$v['id']}";
                } else {
                    $v["link"] = url("{$nUrl}") . "?id={$v['id']}&lang={$cur_lang}";
                }
            } elseif ($url_model == 2) {
                $nUrl = "/{$cur_lang}" . DIRECTORY_SEPARATOR . $model . DIRECTORY_SEPARATOR . 'detail';
                $v["link"] = url("{$nUrl}/{$v['id']}");
            } elseif ($url_model == 3) {
                $column_id = $v['column_id'];
                if (empty($column_id)) {
                    $v["link"] = "javascript:void(0);";
                    continue;
                }
                if ($isMobile) { //手机访问
                    $nUrl = "/$cur_lang" . DIRECTORY_SEPARATOR . $model . DIRECTORY_SEPARATOR . 'detail';
                    $v["link"] = url("{$nUrl}/{$v['id']}");
                } else {
                    $columnList = get_column_up($column_id, "", $v['visit_lang']);
                    $parenPath = "";
                    foreach ($columnList as $column) {
                        $parenPath .= $column["dir_path"];
                    }
                    $document_page = xn_cfg("seo.document_page"); //文档生成方式
                    if ($document_page == 1) { //多加一层时间
                        $url = "/{$html_save_path}/" . $parenPath . '/' . foxDate("Ymd", $v['create_time']) . '/' . $v['id'] . "." . $url_html_suffix;
                    } else {
                        $url = "/{$html_save_path}/" . $parenPath . '/' . $v['id'] . "." . $url_html_suffix;
                    }
                    $v["link"] = replaceSymbol($url);
                }
            }
        }
        $v['source'] = $v['article_source'];
        $v['image'] = $v['img_url'];
        if ($titlelen != -1 && $titlelen > 0 && strlen($v['title']) > 0) {
            $v['title'] = myTitle($v['title'], $titlelen);
        }
        $v["link"] = replaceSymbol($v["link"]);
        $arr[]    = $v;
    }

    return $arr;
}

//当前导航添加
function navAddField(&$list, $id, $currentstyle, $at = "yes")
{

    if ($at == "yes") {
        $curColumn = \app\common\model\Column::field("tier")->find($id);
        if ($curColumn) {
            $tierArr = explode(",", $curColumn["tier"]);
            foreach ($tierArr as $id) {
                foreach ($list as $v) {
                    if ($v["id"] == $id) {
                        $v["currentstyle"] = $currentstyle;
                        break;
                    }
                }
            }
        }
    } elseif ($at == "no") {
        foreach ($list as $v) {
            if ($v["id"] == $id) {
                $v["currentstyle"] = $currentstyle;
                break;
            }
        }
    }
}

//栏目、模型、会员属性动态组装
function fieldObjTag($field, $obj)
{
    if ($field->dtype == "text") { //单行文本

        $val = $field->dfvalue;
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <input class="foxui-size-small {$field->name}" placeholder="{$field->remark}" value="{$val}" />
                            </div>
                        </div>
                    </div>       
php;
        return $html;
    } elseif ($field->dtype == "multitext") { //多行文本
        $val = $field->dfvalue;
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }

        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group foxui-align-items-start">
                             <div class="input-label">
                                <label>{$field->title}：</label>
                             </div>
                             <div class="block-box">
                                 <div class="foxui-textarea">
                                   <textarea class="{$field->name}" autocomplete="off" rows="16" placeholder="{$field->remark}">{$val}</textarea>
                                 </div>
                             </div>
                        </div>
                   </div>

php;
        return $html;
    } elseif ($field->dtype == "htmltext") { //HTML文本

        $val = $field->dfvalue;
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group foxui-align-items-start">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="block-box rich-text">
                                <!-- 富文本 -->
                                <textarea class="richText {$field->name}" id="{$field->name}">{$val}</textarea>
                            </div>
                        </div>
                    </div>
php;
        return $html;
    } elseif ($field->dtype == "radio") { //单选
        $valArr = explode(",", $field->dfvalue);
        $content = "";
        if ($obj) {
            $valf = getFieldVal($obj, $field->name);
        } else {
            if (sizeof($valArr) > 0) {
                $valf = $valArr[0];
            }
        }

        foreach ($valArr as $key => $val) {
            if ($val == $valf) {
                $content .= '<div class="foxui-radio  is-checked">';
            } else {
                $content .= '<div class="foxui-radio">';
            }

            $content .=     '<span class="foxui-radio-input">
                                            <i class="foxui-radio-icon"></i>
                                            <input type="radio" value="' . $val . '" checked="checked" />
                                        </span>
                                        <span class="foxui-radio-label">' . $val . '</span>
                                         </div>';
        }

        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group">
                            <div class="input-label">
                                <label class="foxui-required">{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <div class="foxui-radio-group {$field->name}">
                                    {$content}
                                </div>
                            </div>
                        </div>
                                           
                    </div>

php;
        return $html;
    } elseif ($field->dtype == "checkbox") { //多选
        $valArr = explode(",", $field->dfvalue);

        $valfArr = explode(",", getFieldVal($obj, $field->name));
        $content = "";
        foreach ($valArr as $key => $val) {
            if (in_array($val, $valfArr)) {
                $content .= '<div class="foxui-checkbox  is-checked">';
            } else {
                $content .= '<div class="foxui-checkbox">';
            }

            $content .=     '<span class="foxui-checkbox-input">
                                        <i class="foxui-checkbox-icon"></i>
                                        <input type="checkbox" value="' . $val . '" checked="checked" />
                                    </span>
                                    <span class="foxui-checkbox-label">' . $val . '</span>
                                </div>';
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group foxui-align-items-start">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="{$field->name}">
                                {$content}
                            </div>
                        </div>
                    </div>
php;
        return $html;
    } elseif ($field->dtype == "switch") { //开关
        $val = getFieldVal($obj, $field->name);
        $isCheck = "";
        if ($val == 1) {
            $isCheck = "is-checked";
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box display-flex foxui-align-items-center">
                                <div class="foxui-switch {$isCheck}">
                                    <input type="checkbox" checked="checked" value="{$val}" class="foxui-switch-input {$field->name}" />
                                    <span class="foxui-switch-core"></span>
                                </div>
                            </div>
                        </div>
                    </div>
php;
        return $html;
    } elseif ($field->dtype == "select") { //下拉框

        $val = "";
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }

        $define =  $field->define;
        $define = str_replace("enum(", "", $define);
        $define = str_replace(")", "", $define);
        $define = str_replace("'", "", $define);
        $valArr = explode(",", $define);
        //        $valArr = explode(",", $field->dfvalue);
        $content = "";
        foreach ($valArr as $key => $valLi) {
            $content .= '<li class="foxui-select-item" data-id="' . $valLi . '">' . $valLi . '</li>';
        }

        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <div class="foxui-select">
                                    <div class="foxui-select-handle foxui-select-icon">
                                        <input class="foxui-select-input foxui-size-small {$field->name}" readonly="readonly" placeholder="请选择值" value="{$val}" data-id="{$val}"/>
                                        <i class="foxui-icon-close-circle"></i>
                                    </div>
                                    <div class="foxui-select-menu">
                                        <ul class="foxui-select-slide">
                                            {$content}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
php;
        return $html;
    } elseif ($field->dtype == "datetime") { //日期和时间
        $val = $field->dfvalue;
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>  
                            <div class="input-box">
                                <div class="foxui-picker foxui-datetime-picker">
                                    <div class="foxui-picker-handle foxui-input-prefix">
                                        <i class="foxui-icon-gongzuo-o foxui-prefix-icon"></i>
                                        <input class="foxui-size-small {$field->name}" readonly="readonly"placeholder="请选择时间" value="{$val}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
php;
        return $html;
    } elseif ($field->dtype == "int") { //整数类型
        $val = $field->dfvalue;
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <input type="text" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" class="foxui-size-small {$field->name}" placeholder="只允许纯数字" value="{$val}" />
                            </div>
                        </div>
                   </div>     
php;
        return $html;
    } elseif ($field->dtype == "float") { //小数类型
        $val = $field->dfvalue;
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <input type="text" onkeyup="this.value=this.value.replace(/[^0-9\.]/g,'');" class="foxui-size-small {$field->name}" placeholder="允许小数点的数值" value="{$val}" />
                            </div>
                        </div>
                   </div>
php;
        return $html;
    } elseif ($field->dtype == "decimal") { //金额类型
        $val = $field->dfvalue;
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <input type="text" onkeyup="this.value=this.value.replace(/[^0-9\.]/g,'');" class="foxui-size-small {$field->name}" placeholder="允许小数点的金额" value="{$val}" />
                            </div>
                        </div>
                   </div>
php;
        return $html;
    } elseif ($field->dtype == "pic") { //图片

        $contentImg = "";
        if ($obj) { //值不为空
            $imgIds = getFieldVal($obj, $field->name);
            if (!empty($imgIds)) {
                $idsArr = explode(",", $imgIds);
                if (sizeof($idsArr) > 0) {
                    foreach ($idsArr as $k => $img) {
                        $contentImg .= '
                        <li class="foxui-images-item foxui-animate-fadeInDown">
                            <div class="content">
                                <img src="' . $img . '" />
                                <span class="replace">替换</span>
                                <i class="foxui-icon-cuowu-f delete"></i>
                            </div>
                        </li>
                       ';
                    }
                }
            }
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group foxui-align-items-start">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <div class="foxui-images foxui-images-fluid">
                                    <div class="foxui-images-card {$field->name}">
                                         <ul class="foxui-images-list">
                                            {$contentImg}
                                            <div class="foxui-images-handle">
                                                <div class="foxui-images-handle-inner">
                                                    <i class="foxui-icon-jiahao-o"></i>
                                                    <span class="text">添加图片</span>
                                                </div>
                                            </div>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
php;
        return $html;
    } elseif ($field->dtype == "pics") { //图片集合

        $contentImg = "";
        if ($obj) { //值不为空
            $imgIds = getFieldVal($obj, $field->name);
            if (!empty($imgIds)) {
                $idsArr = explode(",", $imgIds);
                if (sizeof($idsArr) > 0) {
                    foreach ($idsArr as $k => $img) {
                        $contentImg .= '
                        <li class="foxui-images-item foxui-drag-item foxui-animate-fadeInDown">
                            <div class="content">
                                <img src="' . $img . '" />
                                <div class="foxui-drag-handle"></div>
                                <span class="replace">替换</span>
                                <i class="foxui-icon-cuowu-f delete"></i>
                            </div>
                        </li>
                       ';
                    }
                }
            }
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group foxui-align-items-start">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <div class="foxui-images foxui-drag" data-count="-1">
                                    <div class="foxui-images-card {$field->name}">
                                         <ul class="foxui-images-list foxui-drag-container">
                                            {$contentImg}
                                            <div class="foxui-images-handle">
                                                <div class="foxui-images-handle-inner">
                                                    <i class="foxui-icon-jiahao-o"></i>
                                                    <span class="text">添加图片</span>
                                                </div>
                                            </div>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
php;
        return $html;
    } elseif ($field->dtype == "media") { //媒体

        $contentMedia = "";
        if ($obj) { //值不为空
            $videostr = getFieldVal($obj, $field->name);
            $videoArr = json_decode($videostr);
            if (sizeof($videoArr) > 0) {

                foreach ($videoArr as $video) {
                    $contentMedia .= '
                        <li class="foxui-videos-item foxui-animate-fadeInDown">
						<div class="content">
							<img data-id="' . $video->id . '" src="' . $video->pic . '" data-url="' . $video->url . '"/>
							<span class="duration">' . $video->duration . '</span>
							<div class="play">
								<i class="foxui-icon-kaishi-f"></i>
							</div>
							<span class="replace">替换</span>
							<i class="foxui-icon-cuowu-f delete"></i>
						</div>
						<input type="text" class="foxui-videos-title" placeholder="请输入标题" value="' . $video->title . '"/>
					</li>
                       ';
                }
            }
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group foxui-align-items-start">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <div class="foxui-videos foxui-drag" data-count="-1">
                                    <div class="foxui-videos-card {$field->name}">
                                         <ul class="foxui-videos-list foxui-drag-container">
                                            {$contentMedia}
                                            <div class="foxui-videos-handle">
                                                <div class="foxui-videos-handle-inner">
                                                    <i class="foxui-icon-jiahao-o"></i>
                                                    <span class="text">添加视频</span>
                                                </div>
                                            </div>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
php;
        return $html;
    } elseif ($field->dtype == "color") {

        $val = $field->dfvalue;
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }

        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <div class="foxui-color">
                                    <div class="foxui-color-handle is-alpha">
                                        <span class="foxui-color-show" style="background-color: {$val}"></span>
                                        <i class="foxui-icon-close"></i>
                                        <span class="foxui-color-background"></span>
                                        <input class="foxui-color-input {$field->name}" value="{$val}"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                   </div>
php;
        return $html;
    } elseif ($field->dtype == "icon") { //图标

        $val = $field->dfvalue;
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }
        $html = <<<php
                <div class="section-main-item">
                    <div class="foxui-input-group">
                        <div class="input-label">
                            <label>{$field->title}：</label>
                        </div>
                        <div class="inline-box">
                            <div class="foxui-iconsel foxui-size-medium">
                                <div class="foxui-iconsel-handle">
                                    <div class="foxui-iconsel-show">
                                        <i class="{$val}"></i>
                                    </div>
                                    <i class="foxui-icon-jiahao-o"></i>
                                    <input class="foxui-iconsel-input {$field->name}" value="{$val}" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>       
php;
        return $html;
    }
}

//基本设置、联系方式属性动态组装
function fieldTag($field, $obj, $group = "")
{
    $group = $group ?? "basic"; //默认
    $call = "{fox:{$group} name='{$field->name}'/}";

    if ($field->dtype == "text") { //单行文本

        $val = $field->dfvalue;
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <input class="foxui-size-small {$field->name}" placeholder="{$field->remark}" value="{$val}" />
                            </div>
                        </div>
                        
                        <div class="call-field">
                            <span class="foxui-tag foxui-light-info">{$call}</span>
                        </div> 
                    </div>
php;
        return $html;
    } elseif ($field->dtype == "multitext") { //多行文本
        $val = $field->dfvalue;
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }

        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group foxui-align-items-start">
                             <div class="input-label">
                                <label>{$field->title}：</label>
                             </div>
                             <div class="input-box">
                                 <div class="foxui-textarea">
                                    <textarea class="{$field->name}" autocomplete="off" rows="1" placeholder="{$field->remark}">{$val}</textarea>
                                 </div>
                             </div>
                        </div>
                        
                        <div class="call-field">
                            <span class="foxui-tag foxui-light-info">{$call}</span>
                        </div> 
                   </div>

php;
        return $html;
    } elseif ($field->dtype == "htmltext") { //HTML文本

        $val = $field->dfvalue;
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group foxui-align-items-start">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="block-box rich-text">
                                <!-- 富文本 -->
                                <textarea class="richText {$field->name}" id="{$field->name}">{$val}</textarea>
                            </div>
                        </div>
                    </div>
php;
        return $html;
    } elseif ($field->dtype == "radio") { //单选
        $valArr = explode(",", $field->dfvalue);
        $content = "";
        if ($obj) {
            $valf = getFieldVal($obj, $field->name);
        } else {
            if (sizeof($valArr) > 0) {
                $valf = $valArr[0];
            }
        }

        foreach ($valArr as $key => $val) {
            if ($val == $valf) {
                $content .= '<div class="foxui-radio  is-checked">';
            } else {
                $content .= '<div class="foxui-radio">';
            }

            $content .=     '<span class="foxui-radio-input">
                                            <i class="foxui-radio-icon"></i>
                                            <input type="radio" value="' . $val . '" checked="checked" />
                                        </span>
                                        <span class="foxui-radio-label">' . $val . '</span>
                                         </div>';
        }

        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group">
                            <div class="input-label">
                                <label class="foxui-required">{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <div class="foxui-radio-group {$field->name}">
                                    {$content}
                                </div>
                            </div>
                        </div>
                                           
                    </div>

php;
        return $html;
    } elseif ($field->dtype == "checkbox") { //多选
        $valArr = explode(",", $field->dfvalue);

        $valfArr = explode(",", getFieldVal($obj, $field->name));
        $content = "";
        foreach ($valArr as $key => $val) {
            if (in_array($val, $valfArr)) {
                $content .= '<div class="foxui-checkbox  is-checked">';
            } else {
                $content .= '<div class="foxui-checkbox">';
            }

            $content .=     '<span class="foxui-checkbox-input">
                                        <i class="foxui-checkbox-icon"></i>
                                        <input type="checkbox" value="' . $val . '" checked="checked" />
                                    </span>
                                    <span class="foxui-checkbox-label">' . $val . '</span>
                                </div>';
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group foxui-align-items-start">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="{$field->name}">
                                {$content}
                            </div>
                        </div>
                    </div>
php;
        return $html;
    } elseif ($field->dtype == "switch") { //开关
        $val = getFieldVal($obj, $field->name);
        $isCheck = "";
        if ($val == 1) {
            $isCheck = "is-checked";
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box display-flex foxui-align-items-center">
                                <div class="foxui-switch {$isCheck}">
                                    <input type="checkbox" checked="checked" value="{$val}" class="foxui-switch-input {$field->name}" />
                                    <span class="foxui-switch-core"></span>
                                </div>
                            </div>
                        </div>
                    </div>
php;
        return $html;
    } elseif ($field->dtype == "select") { //下拉框

        $val = "";
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }
        $define =  $field->define;
        $define = str_replace("enum(", "", $define);
        $define = str_replace(")", "", $define);
        $define = str_replace("'", "", $define);
        $valArr = explode(",", $define);
        //        $valArr = explode(",", $field->dfvalue);
        $content = "";
        foreach ($valArr as $key => $valLi) {
            $content .= '<li class="foxui-select-item" data-id="' . $valLi . '">' . $valLi . '</li>';
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <div class="foxui-select">
                                    <div class="foxui-select-handle foxui-select-icon">
                                        <input class="foxui-select-input foxui-size-small {$field->name}" readonly="readonly" placeholder="请选择值" value="{$val}" data-id="{$val}"/>
                                        <i class="foxui-icon-close-circle"></i>
                                    </div>
                                    <div class="foxui-select-menu">
                                        <ul class="foxui-select-slide">
                                            {$content}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="call-field">
                            <span class="foxui-tag foxui-light-info">{$call}</span>
                        </div> 
                    </div>
                    
php;
        return $html;
    } elseif ($field->dtype == "datetime") { //日期和时间
        $val = $field->dfvalue;
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>  
                            <div class="input-box">
                                <div class="foxui-picker foxui-datetime-picker">
                                    <div class="foxui-picker-handle foxui-input-prefix">
                                        <i class="foxui-icon-gongzuo-o foxui-prefix-icon"></i>
                                        <input class="foxui-size-small {$field->name}" readonly="readonly"placeholder="请选择时间" value="{$val}" data-id="{$val}"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="call-field">
                            <span class="foxui-tag foxui-light-info">{$call}</span>
                        </div> 
                    </div>
php;
        return $html;
    } elseif ($field->dtype == "int") { //整数类型
        $val = $field->dfvalue;
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <input type="text" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" class="foxui-size-small {$field->name}" placeholder="只允许纯数字" value="{$val}" />
                            </div>
                        </div>
                        
                        <div class="call-field">
                            <span class="foxui-tag foxui-light-info">{$call}</span>
                        </div> 
                   </div>     
php;
        return $html;
    } elseif ($field->dtype == "float") { //小数类型
        $val = $field->dfvalue;
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <input type="text" onkeyup="this.value=this.value.replace(/[^0-9\.]/g,'');" class="foxui-size-small {$field->name}" placeholder="允许小数点的数值" value="{$val}" />
                            </div>
                        </div>
                        
                        <div class="call-field">
                            <span class="foxui-tag foxui-light-info">{$call}</span>
                        </div> 
                   </div>
php;
        return $html;
    } elseif ($field->dtype == "decimal") { //金额类型
        $val = $field->dfvalue;
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <input type="text" onkeyup="this.value=this.value.replace(/[^0-9\.]/g,'');" class="foxui-size-small {$field->name}" placeholder="允许小数点的金额" value="{$val}" />
                            </div>
                        </div>
                        
                        <div class="call-field">
                            <span class="foxui-tag foxui-light-info">{$call}</span>
                        </div> 
                   </div>
php;
        return $html;
    } elseif ($field->dtype == "pic") { //图片

        $contentImg = "";
        if ($obj) { //值不为空
            $imgIds = getFieldVal($obj, $field->name);
            if (!empty($imgIds)) {
                $idsArr = explode(",", $imgIds);
                if (sizeof($idsArr) > 0) {
                    foreach ($idsArr as $k => $img) {
                        $contentImg .= '
                        <li class="foxui-images-item foxui-animate-fadeInDown">
                            <div class="content">
                                <img  src="' . $img . '" />
                                <span class="replace">替换</span>
                                <i class="foxui-icon-cuowu-f delete"></i>
                            </div>
                        </li>
                       ';
                    }
                }
            }
        }

        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group foxui-align-items-start">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <div class="foxui-images foxui-images-fluid">
                                    <div class="foxui-images-card {$field->name}">
                                         <ul class="foxui-images-list">
                                            {$contentImg}
                                            <div class="foxui-images-handle">
                                                <div class="foxui-images-handle-inner">
                                                    <i class="foxui-icon-jiahao-o"></i>
                                                    <span class="text">添加图片</span>
                                                </div>
                                            </div>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="call-field">
                            <span class="foxui-tag foxui-light-info">{$call}</span>
                        </div> 
                    </div>
php;
        return $html;
    } elseif ($field->dtype == "media") { //媒体

        $contentMedia = "";
        if ($obj) { //值不为空
            $videostr = getFieldVal($obj, $field->name);
            $videoArr = json_decode($videostr);
            if (sizeof($videoArr) > 0) {
                foreach ($videoArr as $video) {
                    $contentMedia .= '
                        <li class="foxui-videos-item foxui-animate-fadeInDown">
						<div class="content">
							<img data-id="' . $video->id . '" src="' . $video->pic . '" data-url="' . $video->url . '"/>
							<span class="duration">' . $video->duration . '</span>
							<div class="play">
								<i class="foxui-icon-kaishi-f"></i>
							</div>
							<span class="replace">替换</span>
							<i class="foxui-icon-cuowu-f delete"></i>
						</div>
						<input type="text" class="foxui-videos-title" placeholder="请输入标题" value="' . $video->title . '"/>
					</li>
                       ';
                }
            }
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group foxui-align-items-start">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <div class="foxui-videos foxui-drag" data-count="1">
                                    <div class="foxui-videos-card {$field->name}">
                                         <ul class="foxui-videos-list foxui-drag-container">
                                            {$contentMedia}
                                            <div class="foxui-videos-handle">
                                                <div class="foxui-videos-handle-inner">
                                                    <i class="foxui-icon-jiahao-o"></i>
                                                    <span class="text">添加视频</span>
                                                </div>
                                            </div>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="call-field">
                            <span class="foxui-tag foxui-light-info">{$call}</span>
                        </div> 
                    </div>
php;
        return $html;
    } elseif ($field->dtype == "color") {

        $val = $field->dfvalue;
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }

        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <div class="foxui-color">
                                    <div class="foxui-color-handle is-alpha">
                                        <span class="foxui-color-show" style="background-color: {$val}"></span>
                                        <i class="foxui-icon-close"></i>
                                        <span class="foxui-color-background"></span>
                                        <input class="foxui-color-input {$field->name}" value="{$val}"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="call-field">
                            <span class="foxui-tag foxui-light-info">{$call}</span>
                        </div>
                   </div>
php;
        return $html;
    } elseif ($field->dtype == "icon") { //图标

        $val = $field->dfvalue;
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }
        $html = <<<php
                <div class="section-main-item">
                    <div class="foxui-input-group">
                        <div class="input-label">
                            <label>{$field->title}：</label>
                        </div>
                        <div class="inline-box">
                            <div class="foxui-iconsel foxui-size-medium">
                                <div class="foxui-iconsel-handle">
                                    <div class="foxui-iconsel-show">
                                        <i class="{$val}"></i>
                                    </div>
                                    <i class="foxui-icon-jiahao-o"></i>
                                    <input class="foxui-iconsel-input {$field->name}" value="{$val}" />
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="call-field">
                        <span class="foxui-tag foxui-light-info">{$call}</span>
                    </div>
                </div>       
php;
        return $html;
    }
}
//广告位等直接显示字段值
function fieldOtherTag($field, $obj)
{
    $call = "{$field->name}";
    if ($field->dtype == "text") { //单行文本

        $val = $field->dfvalue;
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <input class="foxui-size-small {$field->name}" placeholder="{$field->remark}" value="{$val}" />
                            </div>
                        </div>
                        
                        <div class="call-field">
                            <span class="foxui-color-secondary">字段名称：</span>
                            <span class="foxui-tag foxui-light-info">{$call}</span>
                        </div>
                    </div>
php;
        return $html;
    } elseif ($field->dtype == "multitext") { //多行文本
        $val = $field->dfvalue;
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }

        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group foxui-align-items-start">
                             <div class="input-label">
                                <label>{$field->title}：</label>
                             </div>
                             <div class="input-box">
                                 <div class="foxui-textarea">
                                    <textarea class="{$field->name}" autocomplete="off" rows="1" placeholder="{$field->remark}">{$val}</textarea>
                                 </div>
                             </div>
                        </div>
                        
                        <div class="call-field">
                            <span class="foxui-tag foxui-light-info">{$call}</span>
                        </div> 
                   </div>

php;
        return $html;
    } elseif ($field->dtype == "htmltext") { //HTML文本

        $val = $field->dfvalue;
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group foxui-align-items-start">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="block-box rich-text">
                                <!-- 富文本 -->
                                <textarea class="richText {$field->name}" id="{$field->name}">{$val}</textarea>
                            </div>
                        </div>
                        
                        <div class="call-field">
                            <span class="foxui-color-secondary">字段名称：</span>
                            <span class="foxui-tag foxui-light-info">{$call}</span>
                        </div>
                    </div>
php;
        return $html;
    } elseif ($field->dtype == "radio") { //单选
        $valArr = explode(",", $field->dfvalue);
        $content = "";
        if ($obj) {
            $valf = getFieldVal($obj, $field->name);
        } else {
            if (sizeof($valArr) > 0) {
                $valf = $valArr[0];
            }
        }

        foreach ($valArr as $key => $val) {
            if ($val == $valf) {
                $content .= '<div class="foxui-radio  is-checked">';
            } else {
                $content .= '<div class="foxui-radio">';
            }

            $content .=     '<span class="foxui-radio-input">
                                            <i class="foxui-radio-icon"></i>
                                            <input type="radio" value="' . $val . '" checked="checked" />
                                        </span>
                                        <span class="foxui-radio-label">' . $val . '</span>
                                         </div>';
        }

        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group">
                            <div class="input-label">
                                <label class="foxui-required">{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <div class="foxui-radio-group {$field->name}">
                                    {$content}
                                </div>
                            </div>
                        </div>
                        
                        <div class="call-field">
                            <span class="foxui-color-secondary">字段名称：</span>
                            <span class="foxui-tag foxui-light-info">{$call}</span>
                        </div>          
                    </div>

php;
        return $html;
    } elseif ($field->dtype == "checkbox") { //多选
        $valArr = explode(",", $field->dfvalue);

        $valfArr = explode(",", getFieldVal($obj, $field->name));
        $content = "";
        foreach ($valArr as $key => $val) {
            if (in_array($val, $valfArr)) {
                $content .= '<div class="foxui-checkbox  is-checked">';
            } else {
                $content .= '<div class="foxui-checkbox">';
            }

            $content .=     '<span class="foxui-checkbox-input">
                                        <i class="foxui-checkbox-icon"></i>
                                        <input type="checkbox" value="' . $val . '" checked="checked" />
                                    </span>
                                    <span class="foxui-checkbox-label">' . $val . '</span>
                                </div>';
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group foxui-align-items-start">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="{$field->name}">
                                {$content}
                            </div>
                        </div>
                        
                        <div class="call-field">
                            <span class="foxui-color-secondary">字段名称：</span>
                            <span class="foxui-tag foxui-light-info">{$call}</span>
                        </div>
                    </div>
php;
        return $html;
    } elseif ($field->dtype == "switch") { //开关
        $val = getFieldVal($obj, $field->name);
        $isCheck = "";
        if ($val == 1) {
            $isCheck = "is-checked";
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box display-flex foxui-align-items-center">
                                <div class="foxui-switch {$isCheck}">
                                    <input type="checkbox" checked="checked" value="{$val}" class="foxui-switch-input {$field->name}" />
                                    <span class="foxui-switch-core"></span>
                                </div>
                            </div>
                        </div>
                        <div class="call-field">
                            <span class="foxui-color-secondary">字段名称：</span>
                            <span class="foxui-tag foxui-light-info">{$call}</span>
                        </div>
                    </div>
php;
        return $html;
    } elseif ($field->dtype == "select") { //下拉框

        $val = "";
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }
        $define =  $field->define;
        $define = str_replace("enum(", "", $define);
        $define = str_replace(")", "", $define);
        $define = str_replace("'", "", $define);
        $valArr = explode(",", $define);
        $content = "";
        foreach ($valArr as $key => $valLi) {
            $content .= '<li class="foxui-select-item" data-id="' . $valLi . '">' . $valLi . '</li>';
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <div class="foxui-select">
                                    <div class="foxui-select-handle foxui-select-icon">
                                        <input class="foxui-select-input foxui-size-small {$field->name}" readonly="readonly" placeholder="请选择值" value="{$val}" data-id="{$val}"/>
                                        <i class="foxui-icon-close-circle"></i>
                                    </div>
                                    <div class="foxui-select-menu">
                                        <ul class="foxui-select-slide">
                                            {$content}
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="call-field">
                            <span class="foxui-color-secondary">字段名称：</span>
                            <span class="foxui-tag foxui-light-info">{$call}</span>
                        </div> 
                    </div>
                    
php;
        return $html;
    } elseif ($field->dtype == "datetime") { //日期和时间
        $val = $field->dfvalue;
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>  
                            <div class="input-box">
                                <div class="foxui-picker foxui-datetime-picker">
                                    <div class="foxui-picker-handle foxui-input-prefix">
                                        <i class="foxui-icon-gongzuo-o foxui-prefix-icon"></i>
                                        <input class="foxui-size-small {$field->name}" readonly="readonly"placeholder="请选择时间" value="{$val}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="call-field">
                            <span class="foxui-color-secondary">字段名称：</span>
                            <span class="foxui-tag foxui-light-info">{$call}</span>
                        </div> 
                    </div>
php;
        return $html;
    } elseif ($field->dtype == "int") { //整数类型
        $val = $field->dfvalue;
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <input type="text" onkeyup="this.value=this.value.replace(/[^0-9]/g,'');" class="foxui-size-small {$field->name}" placeholder="只允许纯数字" value="{$val}" />
                            </div>
                        </div>
                        
                        <div class="call-field">
                            <span class="foxui-color-secondary">字段名称：</span>
                            <span class="foxui-tag foxui-light-info">{$call}</span>
                        </div>
                   </div>     
php;
        return $html;
    } elseif ($field->dtype == "float") { //小数类型
        $val = $field->dfvalue;
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <input type="text" onkeyup="this.value=this.value.replace(/[^0-9\.]/g,'');" class="foxui-size-small {$field->name}" placeholder="允许小数点的数值" value="{$val}" />
                            </div>
                        </div>
                        
                        <div class="call-field">
                            <span class="foxui-color-secondary">字段名称：</span>
                            <span class="foxui-tag foxui-light-info">{$call}</span>
                        </div>
                   </div>
php;
        return $html;
    } elseif ($field->dtype == "decimal") { //金额类型
        $val = $field->dfvalue;
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <input type="text" onkeyup="this.value=this.value.replace(/[^0-9\.]/g,'');" class="foxui-size-small {$field->name}" placeholder="允许小数点的金额" value="{$val}" />
                            </div>
                        </div>
                        
                        <div class="call-field">
                            <span class="foxui-color-secondary">字段名称：</span>
                            <span class="foxui-tag foxui-light-info">{$call}</span>
                        </div>
                   </div>
php;
        return $html;
    } elseif ($field->dtype == "pic") { //图片

        $contentImg = "";
        if ($obj) { //值不为空
            $imgIds = getFieldVal($obj, $field->name);
            if (!empty($imgIds)) {
                $idsArr = explode(",", $imgIds);
                if (sizeof($idsArr) > 0) {
                    foreach ($idsArr as $k => $img) {
                        $contentImg .= '
                        <li class="foxui-images-item foxui-animate-fadeInDown">
                            <div class="content">
                                <img  src="' . $img . '" />
                                <span class="replace">替换</span>
                                <i class="foxui-icon-cuowu-f delete"></i>
                            </div>
                        </li>
                       ';
                    }
                }
            }
        }

        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group foxui-align-items-start">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <div class="foxui-images foxui-images-fluid">
                                    <div class="foxui-images-card {$field->name}">
                                         <ul class="foxui-images-list">
                                            {$contentImg}
                                            <div class="foxui-images-handle">
                                                <div class="foxui-images-handle-inner">
                                                    <i class="foxui-icon-jiahao-o"></i>
                                                    <span class="text">添加图片</span>
                                                </div>
                                            </div>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="call-field">
                            <span class="foxui-color-secondary">字段名称：</span>
                            <span class="foxui-tag foxui-light-info">{$call}</span>
                        </div>
                    </div>
php;
        return $html;
    } elseif ($field->dtype == "pics") { //图片集合

        $contentImg = "";
        if ($obj) { //值不为空
            $imgIds = getFieldVal($obj, $field->name);
            if (!empty($imgIds)) {
                $idsArr = explode(",", $imgIds);
                if (sizeof($idsArr) > 0) {
                    foreach ($idsArr as $k => $img) {
                        $contentImg .= '
                        <li class="foxui-images-item foxui-drag-item foxui-animate-fadeInDown">
                            <div class="content">
                                <img src="' . $img . '" />
                                <div class="foxui-drag-handle"></div>
                                <span class="replace">替换</span>
                                <i class="foxui-icon-cuowu-f delete"></i>
                            </div>
                        </li>
                       ';
                    }
                }
            }
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group foxui-align-items-start">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <div class="foxui-images foxui-drag" data-count="-1">
                                    <div class="foxui-images-card {$field->name}">
                                         <ul class="foxui-images-list foxui-drag-container">
                                            {$contentImg}
                                            <div class="foxui-images-handle">
                                                <div class="foxui-images-handle-inner">
                                                    <i class="foxui-icon-jiahao-o"></i>
                                                    <span class="text">添加图片</span>
                                                </div>
                                            </div>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="call-field">
                            <span class="foxui-color-secondary">字段名称：</span>
                            <span class="foxui-tag foxui-light-info">{$call}</span>
                        </div>
                    </div>
php;
        return $html;
    } elseif ($field->dtype == "media") { //媒体

        $contentMedia = "";
        if ($obj) { //值不为空
            $videostr = getFieldVal($obj, $field->name);
            $videoArr = json_decode($videostr);
            if (sizeof($videoArr) > 0) {
                foreach ($videoArr as $video) {
                    $contentMedia .= '
                        <li class="foxui-videos-item foxui-animate-fadeInDown">
						<div class="content">
							<img data-id="' . $video->id . '" src="' . $video->pic . '" data-url="' . $video->url . '"/>
							<span class="duration">' . $video->duration . '</span>
							<div class="play">
								<i class="foxui-icon-kaishi-f"></i>
							</div>
							<span class="replace">替换</span>
							<i class="foxui-icon-cuowu-f delete"></i>
						</div>
						<input type="text" class="foxui-videos-title" placeholder="请输入标题" value="' . $video->title . '"/>
					</li>
                       ';
                }
            }
        }
        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group foxui-align-items-start">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <div class="foxui-videos foxui-drag" data-count="1">
                                    <div class="foxui-videos-card {$field->name}">
                                         <ul class="foxui-videos-list foxui-drag-container">
                                            {$contentMedia}
                                            <div class="foxui-videos-handle">
                                                <div class="foxui-videos-handle-inner">
                                                    <i class="foxui-icon-jiahao-o"></i>
                                                    <span class="text">添加视频</span>
                                                </div>
                                            </div>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="call-field">
                            <span class="foxui-color-secondary">字段名称：</span>
                            <span class="foxui-tag foxui-light-info">{$call}</span>
                        </div>
                    </div>
php;
        return $html;
    } elseif ($field->dtype == "color") {

        $val = $field->dfvalue;
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }

        $html = <<<php
                    <div class="section-main-item">
                        <div class="foxui-input-group">
                            <div class="input-label">
                                <label>{$field->title}：</label>
                            </div>
                            <div class="input-box">
                                <div class="foxui-color">
                                    <div class="foxui-color-handle is-alpha">
                                        <span class="foxui-color-show" style="background-color: {$val}"></span>
                                        <i class="foxui-icon-close"></i>
                                        <span class="foxui-color-background"></span>
                                        <input class="foxui-color-input {$field->name}" value="{$val}"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                       <div class="call-field">
                            <span class="foxui-color-secondary">字段名称：</span>
                            <span class="foxui-tag foxui-light-info">{$call}</span>
                        </div>
                   </div>
php;
        return $html;
    } elseif ($field->dtype == "icon") { //图标

        $val = $field->dfvalue;
        if ($obj) { //值不为空
            $val = getFieldVal($obj, $field->name);
        }
        $html = <<<php
                <div class="section-main-item">
                    <div class="foxui-input-group">
                        <div class="input-label">
                            <label>{$field->title}：</label>
                        </div>
                        <div class="inline-box">
                            <div class="foxui-iconsel foxui-size-medium">
                                <div class="foxui-iconsel-handle">
                                    <div class="foxui-iconsel-show">
                                        <i class="{$val}"></i>
                                    </div>
                                    <i class="foxui-icon-jiahao-o"></i>
                                    <input class="foxui-iconsel-input {$field->name}" value="{$val}" />
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="call-field">
                        <span class="foxui-color-secondary">字段名称：</span>
                        <span class="foxui-tag foxui-light-info">{$call}</span>
                    </div>
                </div>       
php;
        return $html;
    }
}

//获取对象中的属性值
function getFieldVal($obj, $fieldName)
{
    if ($obj) {
        return $obj[$fieldName];
    }
    return "";
}

//栏目属性动态组装
function columnFieldTag($id = "")
{
    $column = \app\common\model\Column::find($id);
    $where = ['status' => 1, "is_system" => 0];

    $columnList = \app\common\model\ColumnField::where($where)->where(function ($query) use ($id) {
        $query->whereOr([['', 'exp', \think\facade\Db::raw("FIND_IN_SET($id, column_ids)")]]);
    })->order(["sort_order" => "desc", "create_time" => "asc"])->select();
    $html = "";
    foreach ($columnList as $field) { //循环栏目模型
        $html .= fieldObjTag($field, $column);
    }
    return $html;
}

//模型属性动态组装
function modelFieldTag($model, $id = "")
{
    $html = "";
    $modelObj = \think\facade\Db::name($model)->find($id); //查询对应模型值
    $where = ["model" => $model, "is_system" => 0, "status" => 1];
    $modelList = \app\common\model\ModelField::where($where)->order(["sort_order" => "desc", "create_time" => "asc"])->select(); //查询模型字段
    foreach ($modelList as $field) { //循环栏目模型
        $html .= fieldObjTag($field, $modelObj);
    }
    return $html;
}


//会员属性动态组装

function memberFieldTag($model = "", $id = "", $member_model_id = 0)
{
    $html = "";
    $modelObj = null;
    if (!empty($model) && !empty($id)) {
        $modelObj = \think\facade\Db::name($model)->find($id); //查询对应模型值
        //不显示密码
        $modelObj['password'] = "";
    }

    $where = ["status" => 1,  "member_model_id" => $member_model_id];
    $modelList = \app\common\model\MemberModelField::where($where)->order(["sort_order" => "desc", "create_time" => "asc"])->select(); //查询模型字段
    foreach ($modelList as $field) { //循环栏目模型
        $html .= fieldObjTag($field, $modelObj);
    }
    return $html;
}

//变量属性动态组装
function variateFieldTag($group, $id = "")
{
    $html = "";
    $groupObj = \think\facade\Db::name($group)->find($id); //查询对应模型值
    $where = ["status" => 1, 'group' => $group];
    $modelList = \app\common\model\VariateField::where($where)->order(["sort_order" => "desc", "create_time" => "asc"])->select(); //查询模型字段
    foreach ($modelList as $field) { //循环栏目模型
        $html .= fieldTag($field, $groupObj, $group);
    }
    return $html;
}

//表单字段
function formFieldTag($tableName, $id = "", $formListId = "")
{
    $html = "";
    $tableObj = \think\facade\Db::table($tableName)->find($id); //查询对应模型值
    $where = [];
    if (!empty($formListId)) {
        array_push($where, ['form_list_id', '=', $formListId]);
    }
    $formFieldList = \app\common\model\FormField::where($where)->order(["sort_order" => "asc", "create_time" => "asc"])->select(); //查询模型字段
    foreach ($formFieldList as $field) { //循环栏目模型
        $html .= fieldObjTag($field, $tableObj);
    }
    return $html;
}

//广告位字段
function advFieldTag($id)
{
    $html = "";
    $slideObj = \app\common\model\Slide::find($id);
    $where = ["advertising_space_id" => $slideObj["advertising_space_id"]];
    $advFieldList = \app\common\model\AdvertisingSpaceField::where($where)->order(["sort_order" => "desc", "create_time" => "asc"])->select(); //查询模型字段
    foreach ($advFieldList as $field) { //循环栏目模型
        $html .= fieldOtherTag($field, $slideObj);
    }
    return $html;
}

//首页地址处理

function resetIndexUrl($url, $lang = "")
{
    $home_lang = xn_cfg("base.home_lang"); //默认语言
    if ($home_lang == $lang) {
        $lang = "";
    }
    //生成路由 //1:动态url,2:伪静态化,3:静态页面
    $url_model = xn_cfg("seo.url_model");
    $url_html_suffix = config("route.url_html_suffix");
    if (!empty($lang)) {
        if ($url_model == 1) {
            $url = url("{$url}") . "?lang={$lang}";
        } else if ($url_model == 2) {
            $url = "{$url}_{$lang}";
            $url = url("{$url}");
        } else if ($url_model == 3) {
            $url = "{$url}_{$lang}.$url_html_suffix";
        }
    } else {
        if ($url_model == 1 || $url_model == 2) {
            if (empty($url)) {
                $url = "/";
            }
        } else if ($url_model == 3) {
            $url = "{$url}.$url_html_suffix";
        }
    }
    return $url;
}

//针对栏目，重新根据seo设置组装url
function resetUrl($url, $id, $lang = "")
{
    // if (empty($url)) {
    //     return $url;
    // }
    $home_lang = xn_cfg("base.home_lang"); //默认语言
    if ($home_lang == $lang) {
        $lang = "";
    }
    //生成路由 //1:动态url,2:伪静态化,3:静态页面
    $url_model = xn_cfg("seo.url_model");
    $url_html_suffix = config("route.url_html_suffix");
    //栏目
    $column_page = xn_cfg("seo.column_page"); //栏目生成方式
    if ($url_model == 1) {
        if (empty($lang)) {
            if (empty($id)) {
                $url = url("{$url}");
            } else {
                $url = url("{$url}") . "?id={$id}";
            }
        } else {
            if (empty($id)) {
                $url = url("{$url}") . "?id={$id}&lang={$lang}";
            } else {
                $url = url("{$url}") . "?id={$id}&lang={$lang}";
            }
        }
    } elseif ($url_model == 2) {
        $url = url("/{$lang}{$url}/{$id}");
    } elseif ($url_model == 3) { //静态模式下
        if (is_mobile()) { //手机访问
            if (empty($id)) {
                $url = url("/{$lang}{$url}");
            } else {
                $url = url("/{$lang}{$url}/{$id}");
            }
            return replaceSymbol($url);
        }
        $column_id = $id;
        if (empty($column_id)) {
            $url = "javascript:void(0);";
        } else {
            $basic = getBasic(); //暂时不用
            if (empty($lang)) {
                $html_save_path = $basic['html_save_path'];
            } else {
                $html_save_path = $lang;
            }
            $curColumn = \app\common\model\Column::find($id);
            $columnList = get_column_up($column_id);
            if ($column_page == 1 && $curColumn["pid"] != 0) {
                $htmlfile = "list_" . $curColumn['id']; //子栏目名称
                //子栏目存放路径
                foreach ($columnList as $column) {
                    if ($column["pid"] == 0) {
                        $url = "/{$html_save_path}/" . $column['dir_path'] . "/" . $htmlfile . "." . $url_html_suffix;
                        break;
                    }
                }
            } else {
                if ($curColumn["pid"] == 0) {
                    $url = "/{$html_save_path}/" . $curColumn['dir_path'] . '/';
                } else {
                    $parenPath = "";
                    foreach ($columnList as $column) {
                        $parenPath .= $column["dir_path"];
                    }
                    $url = "/{$html_save_path}/" . $parenPath . '/';
                }
            }
        }
    }
    return replaceSymbol($url);
}

//详情数据
function detailSetUrl($url, $id, $columnId, $model, $lang = "")
{
    $url_html_suffix = config("route.url_html_suffix");
    //生成路由 //1:动态url,2:伪静态化,3:静态页面
    $url_model = xn_cfg("seo.url_model");

    $home_lang = xn_cfg("base.home_lang"); //默认语言
    if ($home_lang == $lang) {
        $lang = "";
    }
    //栏目
    $document_page = xn_cfg("seo.document_page"); //文档生成方式
    if ($url_model == 1) {
        if (empty($lang)) {
            if (empty($id)) {
                $url = url("{$url}");
            } else {
                $url = url("{$url}") . "?id={$id}";
            }
        } else {
            if (empty($id)) {
                $url = url("{$url}") . "?id={$id}&lang={$lang}";
            } else {
                $url = url("{$url}") . "?id={$id}&lang={$lang}";
            }
        }
    } elseif ($url_model == 2) {
        $url = url("/{$lang}{$url}/{$id}");
    } elseif ($url_model == 3) { //静态模式下
        if (is_mobile()) { //手机访问
            if (empty($id)) {
                $url = url("/{$lang}{$url}");
            } else {
                $url = url("/{$lang}{$url}/{$id}");
            }
            return replaceSymbol($url);
        }
        $basic = getBasic(); //暂时不能用
        if (empty($lang)) {
            $html_save_path = $basic['html_save_path'];
        } else {
            $html_save_path = $lang;
        }

        $curData = \think\facade\Db::name($model)->find($id);
        $columnList = get_column_up($columnId);
        $parenPath = "";
        foreach ($columnList as $column) {
            $parenPath .= "/" . $column["dir_path"];
        }
        if ($document_page == 1) {
            $url = "/{$html_save_path}/" . $parenPath . '/' . foxDate("Ymd", $curData['create_time']) . '/' . $curData['id'] . "." . $url_html_suffix;
        } else {
            $url = "/{$html_save_path}/" . $parenPath . '/' . $curData['id'] . "." . $url_html_suffix;
        }
    }
    return replaceSymbol($url);
}

//获取标签的url （针对TAG标签）
function tagUrl($id)
{
    $url_html_suffix = config("route.url_html_suffix");
    //生成路由 //1:动态url,2:伪静态化,3:静态页面
    $url_model = xn_cfg("seo.url_model");
    if ($url_model == 1) {
        $url = url("list") . "?id={$id}";
    } elseif ($url_model == 2) {
        $url = url("list/{$id}");
    } elseif ($url_model == 3) { //静态模式下
        if (is_mobile()) { //手机访问
            $url = url("list/{$id}");
            return replaceSymbol($url);
        }
        $basic = getBasic();
        $url = "/{$basic["html_save_path"]}/tag/" . $id . "." . $url_html_suffix;
    }
    return replaceSymbol($url);
}

//获取栏目url
function getColumnUrl($column, $index = 0)
{
    $url = "";
    if ($column["column_attr"] == 0) {
        $url =  resetUrl($column['v_path'], $column["id"], $column['visit_lang']);
    } elseif ($column["column_attr"] == 1) { //外部链接
        $url = $column['out_link_head'] . $column['out_link'];
    } elseif ($column["column_attr"] == 2) { //内链栏目
        $inner_column = $column['inner_column'];
        if (!empty($inner_column)) {
            $columnInner = \app\common\model\Column::find($inner_column);
            if ($columnInner) {
                $vPath = $columnInner["v_path"];
                $url = resetUrl($vPath, $inner_column, $column['visit_lang']);
            }
        }
    }
    return replaceSymbol($url);
}

//tag标签地址
function tagSetUrl($url, $param)
{
    //生成路由 //1:动态url,2:伪静态化,3:静态页面
    $url_model = xn_cfg("seo.url_model");
    $id = $param['id'];
    //    $name = $param['name'];
    if ($url_model == 1) {
        if (sizeof($param) > 0) {
            $paramStr = func_param_pack($param, "&"); //参数串
            $url = url("{$url}") . "?{$paramStr}";
        } else {
            $url = url("{$url}");
        }
    } elseif ($url_model == 2 || $url_model == 3) { //2:伪静态化,3:静态页面
        unset($param['id']);
        if (sizeof($param) > 0) {
            $paramStr = func_param_pack($param, "&"); //参数串
            //        $url =  url("{$url}/{$id}")."?{$paramStr}";
            $url = url("{$url}") . "?{$paramStr}";
        } else {
            $url = url("{$url}");
        }
    }
    return replaceSymbol($url);
}

//查询栏目的所有字栏目id
function getChildrensColumnId($columnId, $model, $lang = "")
{
    //向下查询所有子孙栏目
    $allColumn = get_column_down($columnId, $model, $lang);
    $columnIdArr = [];
    foreach ($allColumn as $column) {
        array_push($columnIdArr, $column["id"]);
    }
    return $columnIdArr;
}

//获取所有父类祖辈的栏目id
function getParentsColumnId($columnId, $model, $lang = "")
{
    //向下查询所有子孙栏目
    $allColumn = get_column_up($columnId, $model, $lang);
    $columnIdArr = [];
    foreach ($allColumn as $column) {
        array_push($columnIdArr, $column["id"]);
    }
    return $columnIdArr;
}

//获取页面中分页的分页标签配置
function getPageList($fileContent)
{
    $param = [];
    if (!empty($fileContent) && $fileContent != null) {
        $start = "{fox:list";
        $end = "{/fox:list}";
        $fileContent = get_between($fileContent, $start, $end);
        $fileContent = mb_substr($fileContent, 0, stripos($fileContent, "}"));
        $fileContent = preg_replace('/\ +=/', "=", $fileContent); //去掉等号(=)前面的空格
        $fileContent = preg_replace('/\= +/', "=", $fileContent); //去掉等号(=)后面的空格
        $fileArr = explode(" ", $fileContent);
        foreach ($fileArr as $fc) {
            $kwArr = explode("=", $fc);
            if (sizeof($kwArr) == 2) {
                $k = $kwArr[0];
                $v = num_en($kwArr[1]);
                $param[$k] = $v;
            }
        }
    }
    return $param;
}

//访问统计
function access_stat_js($html, $domainNo)
{

    $url = "//" . $domainNo . url("/plus/Access/stat");
    $jsStr = <<<EOF
    <script async>
        (function () {
            let hm = document.createElement("script");
            hm.src = "{$url}?title=" + encodeURIComponent(document.title);
            let s = document.getElementsByTagName("script")[0]; 
            s.parentNode.insertBefore(hm, s);
        })();
    </script>
    EOF;
    $html  = str_ireplace('</head>', $jsStr . "\n</head>", $html);
    $html = add_copyright($html);
    return $html;
}

//版权
function add_copyright($html)
{
    $base = xn_cfg("base");
    $copyright_remove_mark = $base['copyright_remove_mark'] ?? 0;
    $copyright_color = $base['copyright_color'];
    if (isset($base['copyright_mark_desc'])) {
        $copyright_mark_desc = dataD($base['copyright_mark_desc']);
    } else {
        $copyright_mark_desc = 'fd72bSHX4oDNwz44otEPuqo2mtVYMV1SpwCXdeIkhGNpp4FV2IjkjPcWL5x2gBTnmTeGfddN/DbGCYloPlMW9TXA0i4bGnRP9u/O6LrJc6ZKhYNG1uhNEmx7258k15KGuG4JmTQR4aVuE1foEVS8jesSsJjsICeyrZ52Us0mQ+yiR7puMQUDakZjw6Z3Hx8yJIOfbBkILMoZFckX83M8HUwn9ustzDNwTg42Q5/RO6PO9XWEV7Dr3kRhsOOzMDZbLoThkObKowxFE8i8oRPVo9wzQsDcPyog98IstVArSx6NFA7BxfG84KYvzCC4T8UX8pke9XQZo4xWnKaPOz+Ga5CvXsEsWg56Qv+8wjtlk42YUrOeptS752MW2tWns18GLC475eL7ITr0RgOK6q6DSDsaVa25z0sgFFns8pKBEJWM1C9laExssU+6GtmQgTIvhrHtMwQnuP40g85/YRsC6u97rSMIaCMm3LvmVAbE7KcKx1JU/KMdfbx88/u4gHeqvjmm1xunk4Vsou/hhwoCZws';
        $copyright_mark_desc = dataD($copyright_mark_desc);
    }
    if ($copyright_remove_mark == 0 || $copyright_remove_mark != 1) {
        $versionPath = root_path() . "data/update/version/info.php";
        $versionInfo = require($versionPath);
        $version = "未知";
        if (!empty($versionInfo['version'])) {
            $version = "V{$versionInfo['version']}";
        }
        $textColor = $base['font_color'];
        $addHtml = str_replace("_COLOR", $textColor, $copyright_mark_desc);
        $addHtml = str_replace("_COPYRIGHTCOLOR", $copyright_color, $addHtml);
        $addHtml = str_replace("V_V", $version, $addHtml);
        $addHtml = str_replace("_YEAR", date("Y"), $addHtml);
        $html  = str_ireplace('</html>', $addHtml . "\n</html>", $html);
    }
    return $html;
}

//判断表是否存在
function is_exist_table($tableName)
{
    $table_infos = Db::query('SHOW TABLE STATUS');
    if (sizeof($table_infos) > 0) {
        foreach ($table_infos as $table_info) {
            if ($tableName == $table_info['Name']) {
                return true;
            }
        }
    }
    return false;
}

//退出地址
function logout_url()
{
    $adminP = config("adminconfig.admin_path");
    return url($adminP . "/Login/logout");
}

//栏目模型地址
function getColumModels_url()
{
    $adminP = config("adminconfig.admin_path");
    return url($adminP . "/Column/getColumModels");
}

//个人中心
function adminEdit_url()
{
    $adminP = config("adminconfig.admin_path");
    return url($adminP . "/Admin/edit");
}

//清除缓存
function loginClearCache_url()
{
    $adminP = config("adminconfig.admin_path");
    return url($adminP . "/Login/clearCache");
}

//修改密码
function adminUpdatePassword_url()
{
    $adminP = config("adminconfig.admin_path");
    return url($adminP . "/admin/updatePassword");
}


//循环生成静态html
function seoAllSite_url()
{
    $adminP = config("adminconfig.admin_path");
    return url($adminP . "/Seo/allSite");
}

//单独静态生成
function seoAddDataBuildDetail_url()
{
    $adminP = config("adminconfig.admin_path");
    return url($adminP . "/Seo/addDataBuildDetail");
}

//单独静态栏目生成
function singleAllSite_url()
{
    $adminP = config("adminconfig.admin_path");
    return url($adminP . "/Seo/singleAllSite");
}

//删除幻灯片
function slideDelete_url()
{
    $adminP = config("adminconfig.admin_path");
    return url($adminP . "/Slide/delete");
}

//幻灯片编辑
function slideEdit_url()
{
    $adminP = config("adminconfig.admin_path");
    return url($adminP . "/Slide/edit");
}

// 获取语言包数据
function getLang($lang)
{
    // 设置缓存的键，可以包含文件的路径或其他标识符
    $cacheKey = 'file_content_' . md5($lang);
    // 尝试从缓存中读取内容
    $rContent = \think\facade\Cache::get($cacheKey);
    if ($rContent === null || $rContent === "{}" || $rContent === "") {
        // 缓存未命中，从文件中读取内容
        $filePath = root_path('app/lang') . "pack/{$lang}.ini"; //复制原语言包路
        $filePath = replaceSymbol($filePath);
        $content = file_get_contents($filePath);
        $rContent = "{" . $content . "}";
        // 将内容写入缓存，并设置缓存过期时间
        \think\facade\Cache::set($cacheKey, $rContent, 3600);  // 假设缓存有效期为1小时
    }
    if (empty($rContent)) {
        return [];
    }
    return json_decode($rContent, true);
}

//根据语言标识翻译
function getLangContentByMark($lang, $mark)
{
    $langs = getLang($lang);
    return $langs[$mark];
}
