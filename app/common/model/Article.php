<?php

namespace app\common\model;
use think\Model;

/**
 * 文章模板
 * Class Article
 * @package app\common\model
 */
class Article extends Model
{

    // 追加属性
    protected $append = ['column','img_url','attr_list'];
    //自动时间戳
    protected $autoWriteTimestamp = "datetime";

    /**
     * 内容图片
     * @param $value
     * @param $data
     * @return array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getUploadFilesAttr($value, $data){
        $uploadFils = [];
        if(!empty($data['pic_ids'])){
            $uploadFils = UploadFiles::whereIn('id',$data['pic_ids'])->select();
        }
        return $uploadFils;
    }

    /**
     * 文章缩略图
     * @param $value
     * @param $data
     * @return array|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getImgUrlAttr($value, $data){
        $img_url = "/static/images/noimage.gif";
        if(!empty($data['breviary_pic_id'])){
            $uf = UploadFiles::field('url')->find($data['breviary_pic_id']);
            if($uf && !empty($uf['url'])){
                $img_url = $uf['url'];
            }
        }
        return $img_url;
    }

    /**
     * 栏目
     * @param $value
     * @param $data
     */
    public function getColumnAttr($value,$data){
        if(empty($data['column_id'])){
            return '';
        }
        return Column::field('name')->find($data['column_id'])['name'];
    }

    public function getAttrListAttr($value,$data){

        $attrTextList = [
            'c'=>['text'=>'推荐','state'=>0, 'type'=>'c'],
            't'=>['text'=>'头条','state'=>0, 'type'=>'t'],
            'h'=>['text'=>'热门','state'=>0, 'type'=>'h'],
        ];

        $attrTextListR = [];

        $articleFieldArr = explode(',', $data['article_field']);

        foreach ($attrTextList as $akey=>$ak){
            foreach ($articleFieldArr as $key=>$articleField){
                if($articleField == $akey){
                    $ak['state'] = 1;
                    break;
                }
            }
            array_push($attrTextListR, $ak);
        }
        return $attrTextListR;
    }

    /**
     * 栏目
     * @return array|\think\model\relation\HasOne
     */
    public function columnO(){
        return $this->hasOne(Column::class,'id','column_id');
    }

    public function getStatusDownAttr($value,$data)
    {
        $teamStatusArr = explode(',', $data['team_status']);
        if($teamStatusArr && in_array('down', $teamStatusArr)){
            return 'down';
        }
        return '';
    }

    public function getStatusDelAttr($value,$data)
    {
        $teamStatusArr = explode(',', $data['team_status']);
        if($teamStatusArr && in_array('del', $teamStatusArr)){
            return 'del';
        }
        return '';
    }
}