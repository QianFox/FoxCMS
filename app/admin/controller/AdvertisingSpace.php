<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   14:19
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   14:19
 */

namespace app\admin\controller;

use app\admin\util\Field;
use app\admin\util\TableUtil;
use app\common\controller\AdminBase;
use app\common\model\AdvertisingSpaceField;
use app\common\model\AuthRule;
use \app\common\model\AdvertisingSpace as AdvertisingSpaceModel;
use app\common\model\FieldType;
use think\Exception;
use think\facade\Cache;
use think\facade\Db;
use think\facade\View;

class AdvertisingSpace extends AdminBase
{
    public function index($page = 1, $pageSize = 100)
    {
        $advertisingSpaceList = AdvertisingSpaceModel::where([['lang', '=', $this->getMyLang()]])->order("sid", "asc")->select();
        View::assign("advertisingSpaceList", $advertisingSpaceList);
        $jscript = substr($this->domain, 0, strlen($this->domain) - 1);
        View::assign("jscript", $jscript);
        return view('index');
    }

    public function updateStatus()
    {
        $param = $this->request->param();
        $idList = json_decode($param['idList']);
        if (sizeof($idList) <= 0) {
            $this->error("操作失败，请选择对应启用数据");
        }
        $status = intval($param['status']);
        $advertisingSpaceModel = new AdvertisingSpaceModel();
        try {
            $advertisingSpaceModel->whereIn("id", implode(",", $idList))->update(["status" => $status]);
        } catch (\Exception $e) {
            $this->error('操作失败,' . $e->getMessage());
        }
        $this->success('操作成功');
    }

    public function save()
    {
        $param = $this->request->param();
        if (!array_key_exists("dataArr", $param)) {
            $this->error("参数错误");
        }
        $dataArr = $param['dataArr'];
        $clang = $this->getMyLang();
        $oldAdvertisingSpaceList = (new \app\common\model\AdvertisingSpace())->field("id")->where(["status" => 1, "lang" => $clang])->select(); //原广告位
        $advertisingSpaceDelIds = []; //删除的广告位
        foreach ($oldAdvertisingSpaceList as $oldAdvertisingSpace) {
            $isExist = false;
            foreach ($dataArr as $data) {
                if (!empty($data["id"]) && $oldAdvertisingSpace["id"] == $data["id"]) {
                    $isExist = true;
                    break;
                }
            }
            if (!$isExist) {
                array_push($advertisingSpaceDelIds, $oldAdvertisingSpace["id"]);
            }
        }

        $table_prefix = config("database.connections.mysql.prefix");
        if (TableUtil::check_table($table_prefix . "lang")) {
            $langList = Db::name('lang')->field("lang,name")->where([['status', '=', 1], ['lang', '<>', $clang]])->cache(true)->select()->toArray(); //其它语言
        } else {
            $langList = [];
        }
        foreach ($dataArr as $data) {
            $advertisingSpace = new \app\common\model\AdvertisingSpace();
            $slides = $data['slides'];
            unset($data["slides"]); //移除列表
            if (empty($data["id"])) {
                $data['lang'] = $clang;
                $advertisingSpace->save($data);
                $advertising_space_id = $advertisingSpace["id"];
                $curSid = 's' . $advertising_space_id;
                $advertisingSpace->update(['sid' => $curSid, 'id' => $advertising_space_id]);
                foreach ($langList as $itemLang) {
                    $data['lang'] = $itemLang['lang'];
                    $data['sid'] = $curSid;
                    $otherAd =  new \app\common\model\AdvertisingSpace();
                    $otherAd->save($data);
                    $otherId = $otherAd['id'];
                    foreach ($slides as $slide) {
                        if (empty($slide["id"])) {
                            $slide['lang'] = $itemLang['lang'];
                            $slide['sid'] = $curSid;
                            $slide["advertising_space_id"] = $otherId;
                            $slideM = new \app\common\model\Slide();
                            $slideM->save($slide);
                        }
                    }
                }
            } else {
                $rdata = $advertisingSpace->update($data);
                $slideList = \app\common\model\Slide::where("advertising_space_id", $rdata->id)->select();
                $slideDelIds = [];
                foreach ($slideList as $sl) {
                    $isE = false;
                    foreach ($slides as $slide) {
                        if (!empty($slide["id"]) && $sl["id"] == $slide["id"]) {
                            $isE = true;
                            break;
                        }
                    }
                    if (!$isE) {
                        array_push($slideDelIds, $sl["id"]);
                    }
                }
                $advertising_space_id = $data['id'];
                //删除广告
                if (sizeof($slideDelIds) > 0) {
                    \app\common\model\Slide::destroy($slideDelIds);
                }
                $fas =  (new \app\common\model\AdvertisingSpace())->field("sid")->find($advertising_space_id);
                if ($fas) {
                    $curSid = $fas['sid'];
                } else {
                    $curSid = 0; //错误id
                }
            }

            foreach ($slides as $slide) {
                $slide["advertising_space_id"] = $advertising_space_id;
                $slideM = new \app\common\model\Slide();
                if (empty($slide["id"])) {
                    $slide['lang'] = $clang;
                    $slide['sid'] = $curSid;
                    $slideM->save($slide);
                } else {
                    $slideM->update($slide);
                }
            }
        }

        //删除广告位
        if (sizeof($advertisingSpaceDelIds) > 0) {
            foreach ($advertisingSpaceDelIds as $advertisingSpaceDelId) {
                AdvertisingSpaceModel::destroy($advertisingSpaceDelId);
                \app\common\model\Slide::where("advertising_space_id", $advertisingSpaceDelId)->delete();
            }
        }
        $this->success("成功");
    }

    public function add()
    {
        $columnId = $this->request->param("columnId");
        $authRule = AuthRule::find($columnId);
        $bcidStr = str_replace(",", "_", $authRule->tier);
        $breadcrumb = AuthRule::getBreadcrumb($bcidStr);
        array_push($breadcrumb, ['id' => '', 'title' => '添加广告位', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/Slide/add', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);

        if ($this->request->isPost()) {
            $param = $this->request->param();
            AdvertisingSpaceModel::create($param);
            $this->success("操作成功");
        }
    }

    public function edit()
    {
        $columnId = $this->request->param("columnId");
        $authRule = AuthRule::find($columnId);
        $bcidStr = str_replace(",", "_", $authRule->tier);
        $breadcrumb = AuthRule::getBreadcrumb($bcidStr);
        array_push($breadcrumb, ['id' => '', 'title' => '编辑广告位', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/AdvertisingSpaceModel/edit', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);

        if ($this->request->isPost()) {
            $param = $this->request->param();
            AdvertisingSpaceModel::update($param);
            $this->success("操作成功");
        }
        $id = $this->request->get('id');
        $advertisingSpace = AdvertisingSpaceModel::find($id);
        return view('edit', ['advertisingSpace' => $advertisingSpace]);
    }

    public function delete()
    {
        $id = intval($this->request->get('id'));
        !($id > 0) && $this->error('参数错误');
        try {
            //删除广告位
            AdvertisingSpaceModel::destroy($id);
            //删除广告
            \app\common\model\Slide::where("advertising_space_id", $id)->delete();
            //删除对应广告位字段
            $advFieldList = \app\common\model\AdvertisingSpaceField::where("advertising_space_id", $id)->select();
            $table = "fox_slide";
            foreach ($advFieldList as $advField) {
                try {
                    $sql = "ALTER TABLE $table DROP COLUMN {$advField['name']}";
                    Db::execute($sql);
                    \app\common\model\AdvertisingSpaceField::destroy($advField->id); //删除记录
                } catch (Exception $ee) {
                }
            }
            $this->success("删除成功");
        } catch (Exception $e) {
            $this->error('删除失败');
        }
    }

    // 添加字段
    public function addField()
    {
        $param = $this->request->param();
        $columnId = $param["columnId"];
        $authRule = AuthRule::find($columnId);
        $bcidStr = str_replace(",", "_", $authRule->tier);
        $breadcrumb = AuthRule::getBreadcrumb($bcidStr);
        array_push($breadcrumb, ['id' => '', 'title' => '添加字段', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/AdvertisingSpaceModel/addField', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);
        $fieldTypeList = FieldType::field("name,title,status")->where("status", 1)->select();
        View::assign("fieldTypeList", $fieldTypeList); //字段类型数据
        $id = $param["id"];
        if ($this->request->isAjax()) {
            //查询广告位
            $id = $param["id"]; //广告位id
            $advertisingSpace = \app\common\model\AdvertisingSpace::find($id);
            if (!$advertisingSpace) {
                $this->error("广告位不存在");
            }

            if (empty($param['dtype']) || empty($param['title']) || empty($param['name'])) {
                $this->error("缺少必填信息！");
            }

            $findAdvFieldList = AdvertisingSpaceField::where(['name' => $param['name']])->select();
            if (sizeof($findAdvFieldList) > 0) {
                foreach ($findAdvFieldList as $advField) {
                    if ($advField['advertising_space_id'] == $id) {
                        $this->error("广告位字段已经存在,请修改字段名");
                    }
                }

                $field = $findAdvFieldList[0];
                /*保存新增字段的记录*/
                $newData = array(
                    'advertising_space_id'      => $id,
                    'dfvalue'     => $field['dfvalue'],
                    'maxlength'   => $field['maxlength'],
                    'define'      => $field['define'],
                    'dtype'       => $param['dtype'],
                    'name'        => $param['name'],
                    'remark'      => $param['remark'],
                    'title'       => $param['title'],
                );
                (new \app\common\model\AdvertisingSpaceField())->save($newData);
                Cache::clear();
                $this->success("操作成功");
            } else { //字段不存在就添加
                /*去除中文逗号，过滤左右空格与空值*/
                $dfvalue = str_replace('，', ',', $param['dfvalue']);
                if (in_array($param['dtype'], ['radio', 'checkbox', 'select', 'region'])) {
                    $pattern = ['"', '\'', ';', '&', '?', '='];
                    $dfvalue = func_preg_replace($pattern, '', $dfvalue);
                }
                $dfvalueArr = explode(',', $dfvalue);
                foreach ($dfvalueArr as $key => $val) {
                    $tmp_val = trim($val);
                    if (empty($tmp_val)) {
                        unset($dfvalueArr[$key]);
                        continue;
                    }
                    $dfvalueArr[$key] = $tmp_val;
                }
                $dfvalueArr = array_unique($dfvalueArr);
                $dfvalue = implode(',', $dfvalueArr);
                /*--end*/

                $fieldinfos = (new Field())->GetFieldMake($param['dtype'], $param['name'], $dfvalue, $param['title']);
                $ntabsql = $fieldinfos[0];
                $buideType = $fieldinfos[1];
                $maxlength = $fieldinfos[2];

                $table = "fox_slide"; //幻灯片
                $sql = "ALTER TABLE `$table` ADD ${ntabsql}";

                try {
                    $ex = Db::execute($sql);
                } catch (\Exception $e) {
                    if (strpos($e->getMessage(), 'Column already exists') !== false) {
                        $this->error(config("Constant.column_already_exists"));
                    } else {
                        $this->error("操作失败");
                    }
                }
                /*保存新增字段的记录*/
                $newData = array(
                    'advertising_space_id' => $id,
                    'dfvalue' => $dfvalue,
                    'maxlength' => $maxlength,
                    'define' => $buideType,
                    'dtype' => $param['dtype'],
                    'name' => $param['name'],
                    'remark' => $param['remark'],
                    'title' => $param['title'],
                );
                (new \app\common\model\AdvertisingSpaceField())->save($newData);
                Cache::clear();
                $this->success("操作成功");
            }
        }

        View::assign("id", $id);
        return view();
    }

    // 查询自定义变量
    public function getField()
    {
        $param = $this->request->param();
        $where = ["advertising_space_id" => $param["advertising_space_id"]];
        $advFieldList = \app\common\model\AdvertisingSpaceField::where($where)->order(["sort_order" => "desc", "create_time" => "asc"])->select(); //查询模型字段
        $this->success("查询成功", '', $advFieldList);
    }

    // 复制广告位
    public function copyLangAdv()
    {
        $lang = $this->request->param("lang", "");
        if (empty($lang)) {
            $this->error("缺少参数，操作失败");
        }
        $curLang = $this->getMyLang(); //当前语言
        if ($lang == $curLang) {
            $this->error("同语言不允许复制哟");
        }
        $adList = \app\common\model\AdvertisingSpace::where(['status' => 1, 'lang' => $lang])->select()->toArray();
        $as =  new \app\common\model\AdvertisingSpace();
        $sl = new \app\common\model\Slide();
        $asfm = new \app\common\model\AdvertisingSpaceField();
        $index = 0;
        foreach ($adList as $ad) {
            $adId = $ad['id'];
            $fcList = $as->where(['sid' => $ad['sid'], 'lang' => $curLang])->select();
            if (sizeof($fcList) > 0) {
                $index++;
                continue;
            }
            unset($ad['id']);
            $ad['lang'] = $curLang;
            $ad['create_time'] = date("Y-m-d H:i:s");
            $ad['update_time'] = $ad['create_time'];
            $as->save($ad);
            $newAsId = $as->id;
            //复制项数据
            $slideList = \app\common\model\Slide::where(['advertising_space_id' => $adId])->select()->toArray();
            $saveSlides = [];
            foreach ($slideList as $slide) {
                unset($slide['id']);
                $slide['advertising_space_id'] = $newAsId;
                $slide['lang'] = $curLang;
                $saveSlides[] = $slide;
            }
            if (sizeof($saveSlides) > 0) {
                $sl->saveAll($saveSlides);
            }
            //复制新增字段
            $asfList = \app\common\model\AdvertisingSpaceField::where(['advertising_space_id' => $adId])->select()->toArray();
            $saveAsf = [];
            foreach ($asfList as $asf) {
                unset($asf['id']);
                $asf['advertising_space_id'] = $newAsId;
                $asf['lang'] = $curLang;
                $saveAsf[] = $asf;
            }
            if (sizeof($saveAsf) > 0) {
                $asfm->saveAll($saveAsf);
            }
            $index++;
        }
        if ($index > 0) {
            $this->success("操作成功");
        } else {
            $this->error("操作失败");
        }
    }
}