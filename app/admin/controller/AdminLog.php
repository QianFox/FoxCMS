<?php

/**
 * 清除日志
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   14:17
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   14:17
 */

namespace app\admin\controller;

use app\common\controller\AdminBase;
use app\common\model\AdminLog as AdminLogModel;
use think\facade\Db;

class AdminLog extends AdminBase
{
    public function index()
    {
        $param = $this->request->param();
        if ($this->request->isAjax()) {
            $model = new AdminLogModel();
            if ($param['start_date'] != '' && $param['end_date'] != '') {
                $model = $model->whereBetweenTime('create_time', $param['start_date'], $param['end_date']);
            }
            if (empty($param["currentPage"])) {
                $param["currentPage"] = 1;
            }
            if (empty($param["pageSize"])) {
                $param["pageSize"] = 10;
            }
            $where = [];
            if (array_key_exists('keyword', $param) && !empty($param['keyword'])) {
                array_push($where, ['remark', 'like', '%' . $param['keyword'] . '%']);
            }
            $list = $model->where($where)->order('id desc')->paginate(['page' => $param['currentPage'], 'list_rows' => $param['pageSize']])->each(function ($item) {
                $item['create_times'] = explode(" ", $item['create_time']);
                $item['content'] =  $item['content'] ?? "无";
                return $item;
            });
            try { //清除30天以外的日志
                $bfday = date("Y-m-d", strtotime("-30 day"));
                \app\common\model\AdminLog::where([['create_time', '<', $bfday]])->delete();
            } catch (\Exception $e) {
            }
            $this->success("查询成功", "", $list);
        }
        return view('index');
    }

    // 清除日志
    public function clear()
    {
        $model = Db::name('admin_log');
        $day = $this->request->param('day/d');
        if ($day > 0) {
            $model = $model->whereTime('create_time', '<=', strtotime(date('Y-m-d', time() - $day * 86400)));
            $result = $model->delete();
        } else {
            $result = $model->delete(true);
        }
        if ($result) {
            xn_add_admin_log('清除日志');
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    public function delete()
    {
        $id = intval($this->request->get('id'));
        !($id > 0) && $this->error('参数错误');
        AdminLogModel::destroy($id);
        $this->success('删除成功');
    }

    public function deletes()
    {
        $param = $this->request->param();
        if (array_key_exists("idList", $param)) {
            $idList = json_decode($param['idList']);
            $count = 0;
            $model = new AdminLogModel();
            $model->startTrans();
            foreach ($idList as $key => $id) {
                $r = $model->destroy($id);
                if ($r) {
                    $count++;
                }
            }
            if (sizeof($idList) == $count) {
                $model->commit();
                $this->success('操作成功');
            } else {
                $model->rollback();
                $this->error('操作失败');
            }
        }
    }
}