<?php

namespace app\common\model;

use think\Model;

class Contact extends Model
{
    protected $autoWriteTimestamp = "datetime";

    // 追加属性
//    protected $append = ['qr_code3_url'];

//    /**
//     * 二维码3
//     * @param $value
//     * @param $data
//     * @return mixed|string
//     * @throws \think\db\exception\DataNotFoundException
//     * @throws \think\db\exception\DbException
//     * @throws \think\db\exception\ModelNotFoundException
//     */
//    public function getQrCode3UrlAttr($value, $data){
//        if(empty($data['qrcode3'])){
//            return '';
//        }
//        return UploadFiles::field('url')->find($data['qrcode3'])['url'];
//    }
}