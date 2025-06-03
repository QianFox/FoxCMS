<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   14:46
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   14:46
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\model\DictType as DictTypeModel;
use \app\common\model\DictData as DictDataModel;

class DictData extends AdminBase
{
    // 获取全部图片分组
    public function groupList()
    {
        $groupList = DictTypeModel::where('dict_type', 'fox_pic_group_type')->with('dictDatas')->find()->dictDatas;
        $rGroupList = [['id' => 0, 'title' => '全部', 'type' => 'all', 'count' => 2312, 'state' => 0,  'groupId' => -1]];
        $total = 0;
        foreach ($groupList as $key => $item) {
            $total += $item->count;
            $rGroupList[($key + 1)] = ['id' => $item->dict_code, 'title' => $item->dict_label, 'type' => $item->type, 'count' => $item->count, 'state' => $item->status, 'groupId' => $item->dict_value];
        }
        $rGroupList[0]['count'] = $total;
        $this->success("查询成功", null, $rGroupList);
    }

    public function delete()
    {
        $id = intval($this->request->get('id'));
        !($id > 0) && $this->error('参数错误');
        DictDataModel::destroy($id);
        $this->success('删除成功');
    }
}