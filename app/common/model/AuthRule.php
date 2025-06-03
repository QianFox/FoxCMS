<?php

namespace app\common\model;

use think\facade\Db;
use think\Model;

class AuthRule extends Model
{
    public function getMenu()
    {
        $list = $this->where('status',1)->whereIn('type',['M','C'])->order('sort asc, id asc')->select()->toArray();
//        $data = Data::channelLevel($list,0,'&nbsp;','id');
        return $list;
    }

    /**
     * 获取面包屑当前位置数据
     * @param $bcid
     * @return array
     */
    public function getBreadcrumb($bcid)
    {
        $adminP = config("adminconfig.admin_path");
        $ids = explode('_', $bcid);
        $list = Db::name('auth_rule')->where('id','in',$ids)->column('id,name,title','id');
        foreach ($list as &$_list) {

            if(!empty($_list['name'])){
                $name = $_list['name'];
                if(stripos($name, "?") != false){//判断是否存在问号
                    $lastH = strripos($name,"?");
                    $url_pre = mb_substr($name, 0, $lastH);
                    $url_suf = mb_substr($name, $lastH);
                    $url = url($adminP.$url_pre).$url_suf;
                }else{
                    $url = url($adminP.$name)."?columnId={$_list['id']}";
                }
            }else{
                $url = "javascript:void(0)";
            }
            $_list['url'] = $url;
        }
        $data = [];
        foreach ($ids as $key=>$id) {
            if($id != 0){
                $data[$id] = $list[$id];
            }
        }
        return $data;
    }
}