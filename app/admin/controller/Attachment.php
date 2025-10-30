<?php

namespace app\admin\controller;

use app\common\controller\AdminBase;
use think\facade\View;
use app\common\model\Attachment as AttachmentModel;

class Attachment extends AdminBase
{
    private $allowMedias = ["mp4", "ogg", "webm", "mp3", "wav"];//默认只允许上传媒体
    private $allowImages = ['bmp','jpg','jpeg','gif','svg','ico','png','jpeg2000'];//默认只允许上传图片
    private $allowFiles = ['doc','docx','pdf','txt','xls','xlsx','ppt', "zip", "rar"];//默认只允许上传文件
    public function index()
    {
        $param = $this->request->param();
        if(array_key_exists('bcid', $param)){
            View::assign('bcid',$param['bcid']);
        }
        $attaArr = AttachmentModel::select()->toArray();
        if(sizeof($attaArr) > 0){
            $atta = $attaArr[0];
            $atta['bucket_domain'] = $atta['bucket'].".".$atta['endpoint'];
            $atta['i_suffixs'] = str_replace(","," ",  $atta['i_suffixs']);
            $atta['a_suffixs'] = str_replace(","," ",  $atta['a_suffixs']);
            $atta['f_suffixs'] = str_replace(","," ",  $atta['f_suffixs']);
            View::assign('attachment', $atta);
        }
        //文件上传大小
        $postSize = @ini_get('file_uploads') ? ini_get('post_max_size') :'未知';
        $fileUpload  = @ini_get('file_uploads') ? ini_get('upload_max_filesize') :'未知';
        View::assign('postSize', $postSize);
        View::assign('fileUpload', $fileUpload);

        return view('index');
    }

    /**
     * 后最输入处理
     */
    private function suffixHandler($suffix, $type="i"){
        $suffix = str_replace("\n", " ", $suffix);
        $suffix = str_replace("\r", " ", $suffix);
        $suffix = str_replace("　", " ", $suffix);
        $suffixArr = explode(" ", $suffix);
        $sfArr = [];
        foreach ($suffixArr as $sf){
            if(!empty($sf)){
                if($type == "i"){//支持图片后缀
                    if(in_array($sf, $this->allowImages)){
                        array_push($sfArr, $sf);
                    }
                }elseif ($type == "a"){//支持音频视频附件后缀
                    if(in_array($sf, $this->allowMedias)){
                        array_push($sfArr, $sf);
                    }
                }elseif ($type == "f"){//支持文件附件后缀
                    if(in_array($sf, $this->allowFiles)){
                        array_push($sfArr, $sf);
                    }
                }
            }
        }
        $sfArr = array_unique($sfArr);
        return implode(",", $sfArr);
    }

    public function save()
    {
        $file = $this->request->action();
        if( $this->request->isPost() ) {
            $param = $this->request->post();
            //现在附近设置
            if(!empty($param["i_suffixs"])){
                $i_suffixs = $this->suffixHandler($param["i_suffixs"], "i");//支持图片后缀
                $param['i_suffixs'] = $i_suffixs;
            }
            if(!empty($param["a_suffixs"])){
                $a_suffixs = $this->suffixHandler($param["a_suffixs"], "a");//支持音频视频附件后缀
                $param['a_suffixs'] = $a_suffixs;
            }
            if(!empty($param["f_suffixs"])){
                $f_suffixs = $this->suffixHandler($param["f_suffixs"], "f");//支持文件附件后缀
                $param['f_suffixs'] = $f_suffixs;
            }
            $bucket_domain = $param['bucket_domain'];
            $bdArr = explode(".", $bucket_domain);
            unset($bdArr[0]);
            $endpoint = implode(".", $bdArr);
            $param['endpoint'] = $endpoint;
            unset($param['bucket_domain']);
            $id = $param["id"];

            if($id == "" || empty($id)){
                AttachmentModel::create($param);
            }else{
                AttachmentModel::update($param);
            }
            $this->success('设置成功');
        }
        return view($file,['data'=>$this->_load($file)]);
    }
}