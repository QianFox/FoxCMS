<?php


namespace app\count\controller;


class Picture
{
    public function icon(){
       $imagePath = __DIR__. "/../";
       $infoContent = file_get_contents(__DIR__. "/../info.ini");
       $infoArr = explode("\r\n", $infoContent);
       foreach ($infoArr as $vo){
           if(str_starts_with( $vo,"icon")){
               $iconArr = explode("=", $vo);
               if (sizeof($iconArr) == 2){
                   $imagePath = $imagePath.trim($iconArr[sizeof($iconArr) - 1]);
               }
           }
       }
       if($imagePath == (__DIR__. "/../")){
           $imagePath = $imagePath."icon.png";
       }
        list($type) = getimagesize($imagePath);
        $header = [];
        if ($type == IMAGETYPE_JPEG) {
           array_push($header, ['Content-Type' => 'image/jpeg']);
        }elseif ($type == IMAGETYPE_PNG){
            array_push($header, ['Content-Type' => 'image/png']);
        }elseif($type == IMAGETYPE_GIF){
            array_push($header, ['Content-Type' => 'image/gif']);
        }
        return \think\Response::create(file_get_contents($imagePath))->code(200)->header(['Content-Type' => 'image/png']);

    }
}