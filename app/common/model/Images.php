<?php

namespace app\common\model;
use think\Model;

class Images extends Model
{

    // 追加属性
    protected $append = ['column','img_url','attr_list','pic_set'];

    protected $autoWriteTimestamp = "datetime";


    public function getUploadFilesAttr($value, $data){
        $uploadFils = [];
        if(!empty($data['pic_ids'])){
            $uploadFils = UploadFiles::whereIn('id',$data['pic_ids'])->select();
        }
        return $uploadFils;
    }


    public function getPicSetAttr($value, $data){
        $uploadFils = [];
        if(!empty($data['picset_ids'])){
            $pic_idArr = explode(",", $data['picset_ids']);
            $ufs = UploadFiles::whereIn('id',$data['picset_ids'])->select();
            foreach ($pic_idArr as $pic_id){
                if(!empty($pic_id)){
                    foreach ($ufs as $uf){
                        if($pic_id == $uf->id){
                            array_push($uploadFils, $uf);
                            break;
                        }
                    }
                }
            }
        }
        return $uploadFils;
    }


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