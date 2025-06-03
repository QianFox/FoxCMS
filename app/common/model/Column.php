<?php

namespace app\common\model;

use think\facade\Db;
use think\Model;

class Column extends Model
{
    protected $autoWriteTimestamp = "datetime";

    protected $append = ['thiscat','pic_url'];

    /**
     * 所属栏目
     * @param $value
     * @param $data
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getThiscatAttr($value, $data){
        $thiscat = '顶层栏目';
        if($data['pid'] != 0){
            $pColumn =  $this->field('name')->find($data['pid']);
            if($pColumn){
                $thiscat = $pColumn["name"];
            }
        }
        return $thiscat;
    }

    public function getPicUrlAttr($value, $data){
        if(empty($data['pic_ids'])){
            return '';
        }
        return UploadFiles::field('url')->find($data['pic_ids'])['url'];
    }

    /**
     * 获取面包屑当前位置数据
     * @param $bcid
     * @return array
     */
    public function getBreadcrumb($bcid)
    {
        $ids = explode('_', $bcid);
        unset($ids[0]);
        $list = Db::name('column')->where('id','in',$ids)->column('id,name as title,v_path as name','id');
        foreach ($list as &$_list) {
            $_list['url'] = !empty($_list['name']) ? url($_list['name']) : 'javascript:void(0)';
        }
        $data[0] = ['id'=>4, 'title'=>'内容', 'name'=>'/neirong','url'=>'javascript:void(0)'];
        foreach ($ids as $key=>$id) {
            $data[$id] = $list[$id];
        }
        return $data;
    }

    //一对一
    public function single(){
        return $this->hasOne(Single::class,'column_id','id');
    }

}