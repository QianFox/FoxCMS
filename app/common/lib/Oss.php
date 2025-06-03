<?php
//zsl
namespace app\common\lib;

use app\common\model\Attachment;
use app\common\util\ImageUtil;
use OSS\Core\OssException;
use OSS\OssClient;
use think\facade\App;
use think\facade\Filesystem;
use think\Image;

/**
 * 阿里云oss
 * Class Oss
 * @package app\common\lib
 */
class Oss
{
    private $ak='';
    private $sk='';
    private $bucket='';
    private $endpoint='';
    private $is_customize= false;
    private $oss_domain='';


    /**
     *获取endpoint
     */
    public function getOssDomain(){
        return $this->oss_domain;
    }
    public function __construct()
    {
        $atts = Attachment::select()->toArray();
        if(sizeof($atts) > 0){//查询数据库配置
            $config = $atts[0];
            $this->ak = $config['access_key'];
            $this->sk = $config['access_key_secret'];
            $this->bucket = $config['bucket'];
            $is_customize = $config['is_customize'];
            $this->endpoint = $config['endpoint_prefix'].$config['endpoint'];
            if($is_customize == 1){//是自定义域名
                $this->endpoint = $config['url_prefix'].$config['url'];
                $this->is_customize = true;
                $this->oss_domain = $this->endpoint;
            }else{
                $this->oss_domain = $config['endpoint_prefix'].$config['bucket'].".".$config['endpoint'];
            }
        }else{
            //没有就用本地配置
            $config = xn_cfg('upload');
            empty($this->ak) && $this->ak = $config['oss_ak'];
            empty($this->sk) &&  $this->sk = $config['oss_sk'];
            empty($this->bucket) &&  $this->bucket = $config['oss_bucket'];
            empty($this->endpoint) &&  $this->endpoint = $config['oss_endpoint'];
        }
    }

    /**
     * 上传文件
     * @param $file
     * @param string $err
     * @param string $folder_name
     * @return bool|string
     */
    public function putFile($file,&$err='',$folder_name="files")
    {
        try {
            $folder = "/".$folder_name."/".date("Ymd")."/"; //根路径文件夹
            //文件后缀名
            if(method_exists($file, "getOriginalExtension")){
                $ext = $file->getOriginalExtension();
            }else{
                $ext = $file->getExtension();
            }
            $filename = func_random_num(8,16).".".$ext;
            $imageUtil = new ImageUtil();
            if($imageUtil->validationSuffix($ext)){//是否图片
                $image = Image::open($file);
                //添加水印
                $imageUtil->addWatermark($image);
                $quality = 100;
                $file_path = config('filesystem.disks.folder').$folder;
                if(!tp_mkdir( app()->getRootPath().$file_path)){
                    $err = "创建文件夹失败";
                    return false;
                }
                $file_path = $file_path.$filename;
                $image->save($file_path, null, $quality);

                $savename = $file_path;
                $savename = replaceSymbol($savename);
            }else{
                $savename = Filesystem::disk('public')->putFileAs($folder, $file, $filename);
            }

            if ($savename) {
                $ossPath = replaceSymbol($savename);//oss远程路径
                $filePath = app()->getRootPath().$ossPath;
                $object_path = replaceSymbol($filePath);
                try {
                    $oss = new OssClient($this->ak, $this->sk, $this->endpoint, $this->is_customize);
//                    $reslut = $oss->putObject($this->bucket, $ossPath, $content);
                    $reslut = $oss->uploadFile($this->bucket, $ossPath, $object_path);
                    $file_path = $reslut['info']['url'];
                    //删除本地文件
                    unlink($object_path);
                    return $file_path;
                } catch (OssException $e) {
                    $err = $e->getMessage();
                    return false;
                }
            } else {
                $err = '上传失败';
                return false;
            }
        } catch (\Exception $e) {
            $err = $e->getMessage();
            return false;
        }
    }

    /**
     * 视频上传根据路径
     * @param $file
     * @param string $err
     * @param string $folder_name
     * @param string $saveFile
     * @return bool
     */
    public function putFileByPath($file,&$err='',$folder_name="files", $saveFile="")
    {
        try {
            $folder = $folder_name."/".date("Ymd")."/"; //根路径文件夹
            $ext = $file->getOriginalExtension();
            $filename = func_random_num(8,16).".".$ext;
            if ($saveFile) {
                $ossPath = replaceSymbol(($folder.$filename));//oss远程路径
                $object_path = replaceSymbol($saveFile);
                $content = file_get_contents($object_path);
                //删除本地文件
                unlink($saveFile);
                try {
                    $oss = new OssClient($this->ak, $this->sk, $this->endpoint, $this->is_customize);
                    $reslut = $oss->putObject($this->bucket, $ossPath, $content);
                    $file_path = $reslut['info']['url'];
                    return $file_path;
                } catch (OssException $e) {
                    $err = $e->getMessage();
                    return false;
                }
            } else {
                $err = '上传失败';
                return false;
            }
        } catch (\Exception $e) {
            $err = $e->getMessage();
            return false;
        }
    }

    /**
     * 删除远程文件
     * @param $object 填写不包含Bucket名称在内的Object完整路径，例如testfolder/exampleobject.txt
     * @param $err
     * @return bool
     */
    public function delete($object,&$err='')
    {
        try{
            $oss = new OssClient($this->ak, $this->sk, $this->endpoint, $this->is_customize);
            $oss->deleteObject($this->bucket, $object);
        } catch(OssException $e) {
            $err = $e->getMessage();
            return $err;
        }
    }

    /**
     * 以下代码用于通过listObjects方法列举bucket根目录下的文件（不包含目录及目录下的文件），默认列举100个文件
     * @param array $options
     * @param array $findarr
     * @return array
     */
    public function getAllFile($options=array(), $findarr = array()){
        $list = array();
        try{
            $oss = new OssClient($this->ak, $this->sk, $this->endpoint, $this->is_customize);
            $this->getFiles($oss, $options, $list, $findarr);
            $rlist = [];
            foreach ($list as $objectInfo){
                $key = replaceSymbol($objectInfo->getKey());
                $dirArr = explode("/", $key);
                $dir = $dirArr[(sizeof($dirArr)-2)];
                $gfile =$this->getOssDomain()."/". $key;
                $filesize = $objectInfo->getSize();
                if($filesize <= 0){
                    continue;
                }
                $filename = $dirArr[(sizeof($dirArr)-1)];
                $filesizeZ = xn_file_size($filesize);
                $keyArr = explode(".", $key);
                $extension = $keyArr[(sizeof($keyArr)-1)];
                $filetime = date('Y-m-d H:i:s', strtotime($objectInfo->getLastModified()));;
                $arrFile = ["file"=>$gfile, 'filesize'=>$filesizeZ, "fsize"=>$filesize, 'filetime'=>$filetime, "filename"=>$filename, "extension"=>$extension, "dir"=>$dir];
                array_push($rlist, $arrFile);
            }
            return $rlist;
        } catch(OssException $e) {
            return [];
        }
    }

    /**
     * 循环查找文件
     * @param $oss
     * @param $options
     * @param $list
     * @param $findarr
     */
    private function getFiles($oss, $options, &$list, $findarr=array()){
        $listObjectInfo = $oss->listObjects($this->bucket, $options);
        if(sizeof($listObjectInfo->getObjectList()) > 0){
            $ObjectList = $listObjectInfo->getObjectList();
            if(sizeof($findarr)>0){
                $farr = [];
                foreach ($findarr as $fileN){
                    foreach ($ObjectList as $objectInfo){
                        $filename = $objectInfo->getKey();
                        if (strpos($filename, $fileN) !== false) {
                            array_push($farr, $objectInfo);
                            break;
                        }
                    }
                }
                $list = array_merge($list, $farr);
            }else{
                $list = array_merge($list, $ObjectList);
            }
        }
        $prefixList = $listObjectInfo->getPrefixList();
        if(sizeof($prefixList) > 0){
            foreach ($prefixList as $prefixInfo){
                $prefix = $prefixInfo->getPrefix();
                $options = array(
                    'prefix' => $prefix,
                );
                $this->getFiles($oss, $options, $list, $findarr);
            }
        }
    }

    /**
     * @param $object 填写不包含Bucket名称在内的Object完整路径，例如testfolder/exampleobject.txt
     * @param string $err
     */
    public function download($object, &$err=''){
        $uploadPath = config('filesystem.disks.folder')."/".$object;
        $localfile = root_path().$uploadPath;
        $localfile = replaceSymbol($localfile);
        $localfileArr = explode("/", $localfile);
        if(sizeof($localfileArr) > 0){
            unset($localfileArr[sizeof($localfileArr)-1]);
        }
        $localfilePath = implode("/", $localfileArr);
        if(!tp_mkdir($localfilePath)){
            $err = "创建文件夹失败";
            return "";
        }
        $options = array(
            OssClient::OSS_FILE_DOWNLOAD => $localfile
        );
        // 使用try catch捕获异常。如果捕获到异常，则说明下载失败；如果没有捕获到异常，则说明下载成功。
        try{
            $oss = new OssClient($this->ak, $this->sk, $this->endpoint, $this->is_customize);
            $oss->getObject($this->bucket, $object, $options);
        } catch(OssException $e) {
            $err = $e->getMessage();
            return "";
        }
        return replaceSymbol($uploadPath);
    }
}