<?php

namespace app\taglib\fox;

use app\common\model\Column;
use app\common\model\ModelRecord;
use think\facade\Db;

/**
 * 查询数据
 */
class TagList extends TagBase
{

    /**
     * 查询数据列表
     */
    public function getList($param, $flag, $ob, $page = 1, $pagesize = 10, $notypeid = "")
    {
        $typeid = $param['typeid'];
        $columnModel = $param['columnModel'];
        $where = $this->getSearch(request()); //增加搜索条件
        $visit_lang = $this->getLang(); //语言

        if (sizeof($where) > 0) { //说明当前在搜索
            $skeyword = request()->param("keyword");
            $sfields = request()->param("fields");
        } else {
            $skeyword = "";
            $sfields = "";
        }

        $typeidList = []; //栏目id集合
        $mmList = []; //模型数组

        $sid = $param["sid"]; //栏目标识


        if (empty($typeid) && !empty($sid)) {
            $fColumns = Column::field("column_model,id")->whereIn("nid", $sid)->where(['lang' => $visit_lang])->select();
            if (sizeof($fColumns) > 0) {
                $columnIds = [];
                foreach ($fColumns as $fColumn) {
                    array_push($columnIds, $fColumn['id']);
                }
                $typeid = implode($columnIds, ",");
                //                $columnModel = $fColumn['column_model'];
            } else {
                echo "list标签栏目标识不存在";
                die();
            }
        }

        $curTypeid = $typeid;
        if (empty($typeid)) {
            $id = \request()->param("id");
            $action = \request()->action();
            if ($action == "detail") { //详情
                if (!empty($id)) {
                    if (empty($columnModel)) {
                        $controller = request()->controller();
                        $currColumnModel = $controller;
                    } else {
                        $currColumnModel = $columnModel;
                    }
                    $article = Db::name($currColumnModel)->field('column_id')->find($id);
                    if ($article) {
                        $curTypeid = $article['column_id'];
                    }
                }
            } else {
                $curTypeid = $id;
            }
        }
        if (!empty($curTypeid)) {
            $columns = Column::field("id,limit_column,data_limit,column_model")->whereIn('id', $curTypeid)->select();
            foreach ($columns as $column) {
                if ($column["data_limit"] == 1) { //仅本栏目
                    array_push($typeidList, $column['id']);
                } elseif ($column["data_limit"] == 2) { //本栏目及下级栏目
                    $typeidList = array_merge($typeidList, explode(",", $column["limit_column"]));
                } elseif ($column["data_limit"] == 3) { //本栏目及指定子栏目
                    $typeidList = array_merge($typeidList, explode(",", $column["limit_column"]));
                }
            }
        }
        $curColumnModel = \request()->param("model");
        if (!empty($columnModel)) { //有传入模型
            $curColumnModel = $columnModel;
        }
        $isAllMr = false; //是否查的是全部模型
        if (empty($curColumnModel)) {
            if (sizeof($typeidList) > 0) { //有栏目id就先过滤模型
                $columnList = Column::field("column_model")->whereIn('id', implode(",", $typeidList))->select();
                foreach ($columnList as $c) {
                    array_push($mmList, ['nid' => $c['column_model']]);
                }
            } else { //没有时
                //查询所有模型 类似文章模型
                $mmList = ModelRecord::field("table, nid")->where(['is_delete' => 0, 'status' => 1, "reference_model" => 0])->select();
                $isAllMr = true;
            }
        } else {
            $columnModelArr = explode(",", $curColumnModel);
            foreach ($columnModelArr as $cm) {
                array_push($mmList, ['nid' => $cm]);
            }
        }
        //过滤掉不是类似文章模型的
        if (!$isAllMr) {
            $mrList = ModelRecord::field("table, nid")->where(['is_delete' => 0, 'status' => 1, "reference_model" => 0])->select();
            $curMMList = [];
            foreach ($mmList as $mm) {
                foreach ($mrList as $mr) {
                    if ($mm['nid'] == $mr['nid']) {
                        array_push($curMMList, $mr);
                        break;
                    }
                }
            }
            $mmList = $curMMList;
        }
        if (sizeof($mmList) <= 0) { //没得模型列表直接返回
            return ["current_page" => 0, "last_page" => 0, "per_page" => 0, "total" => 0, "datas" => []];
        }
        $mmfArr = array_unique(xn_cfg("list"));

        //判断是否增加条件字段
        $tp =  request()->param();
        if (array_key_exists('fields', $tp)) {
            $fieldStr = $tp['fields'];
            $fieldArr = explode(",", $fieldStr);
            foreach ($fieldArr as $field) {
                if (!empty($field)) {
                    $fieldVal = $tp[$field];
                    if (empty($fieldVal)) {
                        continue;
                    }
                    $field = "`{$field}`";
                    if (!in_array($field, $mmfArr)) {
                        array_push($mmfArr, $field);
                    }
                }
            }
        }

        $commonFiled = implode(",", $mmfArr);
        $sqlArr = [];
        foreach ($mmList as $key => $mmf) {
            if ($key > 0) {
                $commonFiledSMore = $commonFiled . ',' . "'{$mmf['nid']}' as model";
                array_push($sqlArr, "SELECT {$commonFiledSMore} FROM {$mmf['table']}");
            }
        }
        $commonFiledFirst = $commonFiled . ',' . "'{$mmList[0]['nid']}' as model";
        $tableName = $mmList[0]['table'];
        $dataSql = Db::table($tableName)->field($commonFiledFirst)->union($sqlArr)->buildSql();
        $sql = "({$dataSql})";
        $qurey = Db::table($sql . " as a")->where($where);
        $qurey->where("lang", $visit_lang);

        if (sizeof($typeidList) > 0) {
            $qurey->whereIn("column_id", implode(",", $typeidList));
        }

        if (!empty($flag) && $flag != "" && $flag != null) {
            $qurey->whereFindInSet('article_field', $flag);
        }
        if (!empty($notypeid) && $notypeid != "" && $notypeid != null) {
            $qurey->whereNotIn("column_id", $notypeid);
        }

        $addfields = trim($param["addfields"]); //增加字段
        $channel = trim($param["channel"]); //增加字段模型

        $P = $qurey->order($ob)->paginate(['page' => $page, 'list_rows' => $pagesize])->each(function ($item, $key) use ($skeyword, $sfields, $addfields, $channel, $visit_lang) {
            if (!empty($item['breviary_pic_id'])) {
                $img = \app\common\model\UploadFiles::field('url')->find($item['breviary_pic_id']);
                if ($img) {
                    $item['img_url'] = $img["url"];
                    unset($item["breviary_pic_id"]);
                }
            }
            if (empty($item['img_url'])) {
                $item['img_url'] = "/static/images/noimage.gif";
            }

            if (!empty($sfields) && !empty($skeyword)) {
                // 使用 str_replace 移除潜在的危险字符
                $cleanSkeyword = str_replace(['<', '>', '"', ';'], '', $skeyword);
                // 使用 htmlspecialchars 对关键词进行转义
                $escapedSkeyword = htmlspecialchars($cleanSkeyword, ENT_QUOTES, 'UTF-8', false);
                $resplaceVal = "<font color='red'>" . $escapedSkeyword . "</font>";

                // 确保字段值也经过清理和转义
                $oldItemVal = $item[$sfields];
                $cleanOldItemVal = str_replace(['<', '>', '"', ';'], '', $oldItemVal);
                $escapedOldItemVal = htmlspecialchars($cleanOldItemVal, ENT_QUOTES, 'UTF-8', false);
                $item[$sfields] = str_replace($escapedSkeyword, $resplaceVal, $escapedOldItemVal);
            }

            if (!empty($addfields) || !empty($channel)) {
                $iColumn = Column::field("column_model")->find($item['column_id']);
                if ($iColumn) {
                    $column_model = $iColumn['column_model'];
                    if (!empty($channel)) {
                        $modelChannelArr = explode(",", $channel);
                        if (!in_array($column_model, $modelChannelArr)) {
                            $column_model = "";
                        }
                    }
                    if (!empty($column_model)) {
                        $curItem = Db::name($column_model)->find($item['id']);
                        if (!empty($addfields)) {
                            $addfieldArr = explode(",", $addfields);
                            foreach ($addfieldArr as $addfield) {
                                // 清理并转义每个添加的字段
                                $value = $curItem[$addfield] ?? "";
                                $cleanValue = str_replace(['<', '>', '"', ';'], '', $value);
                                $escapedValue = htmlspecialchars($cleanValue, ENT_QUOTES, 'UTF-8', false);
                                $item[$addfield] = $escapedValue;
                            }
                        } else {
                            // 清理并转义整个合并的数据
                            $mergedItem = array_merge($item, $curItem);
                            foreach ($mergedItem as $key => $value) {
                                $cleanValue = str_replace(['<', '>', '"', ';'], '', $value);
                                $escapedValue = htmlspecialchars($cleanValue, ENT_QUOTES, 'UTF-8', false);
                                $item[$key] = $escapedValue;
                            }
                        }
                    } else {
                        $addfieldArr = explode(",", $addfields);
                        foreach ($addfieldArr as $addfield) {
                            $value = $curItem[$addfield] ?? "";
                            $cleanValue = str_replace(['<', '>', '"', ';'], '', $value);
                            $escapedValue = htmlspecialchars($cleanValue, ENT_QUOTES, 'UTF-8', false);
                            $item[$addfield] = $escapedValue;
                        }
                    }
                }
            }

            $item['visit_lang'] = $visit_lang;
            return $item;
        });

        $items = $P->items();
        $current_page = $P->getCurrentPage(); //当前页
        $last_page = $P->lastPage(); //最后页
        $per_page = $P->listRows(); //每页数量
        $total = $P->total(); //总数
        if (sizeof($items) > 0) {
            $items[0]['total'] = $total;
        }
        $list = ["current_page" => $current_page, "last_page" => $last_page, "per_page" => $per_page, "total" => $total, "datas" => $items];
        return $list;
    }
}