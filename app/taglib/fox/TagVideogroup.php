<?php

namespace app\taglib\fox;
use app\common\model\VideoFiles;
use think\facade\Db;

/**
 * 图片组
 */
class TagVideogroup
{

    /**
     * 查询数据
     */
    public function getList($param)
    {
        $typeid = $param["typeid"];//有图片组的数据id
        $field = $param["field"];//字段
        $model = $param["model"];
		$modelC = $param["modelC"];
		$type = $param["type"];

		if(!empty($modelC)){
			$model = $modelC;
		}
	
        if(empty($model) || empty($typeid)){
            $action = request()->action();
            if(!empty($model) && empty($typeid)){
                if($action != "detail"){
                    echo '标签videogroup报错：请传入typeid。';
                    return false;
                }
                $id = request()->param("id");
				$typeid = (String)$id;
            }elseif (empty($model) && !empty($typeid)){
                echo '标签videogroup报错：请传入模型。';
                return false;
            }elseif(empty($model) && empty($typeid)){
				
				$model = strtolower(request()->controller());
                if($action != "detail"){
					if($type == "model"){//判断是否放到模型里面
						$id = request()->param("id");
						$findDatas = Db::name($model)->where("column_id", $id)->select();
						$ids = [];
						foreach ($findDatas as $findData){
							array_push($ids, $findData["id"]);
						}
						$typeid = implode(",", $ids);
					}else{
						$id = request()->param("id");
						$typeid = (String)$id;
						$model = "column";
					}
                	
                }else{
					
					$id = request()->param("id");
					$typeid = (String)$id;
				}
                
            }else{
                echo '标签videogroup报错：请检查调用';
                return false;
            }
        }

        if(empty($field)){
            echo "fox:videogroup标签必须告知那个字段是视频组";
            return false;
        }

        $fdata = Db::name($model)->field($field)->find($typeid);

        if($fdata){
            $videoGStr  = $fdata[$field];
            $videoArr = json_decode($videoGStr);
            $rdata = [];
            foreach ($videoArr as $video){
                if(empty($video->id)){
                    array_push($rdata, ["pic"=>$video->pic, "url"=> $video->url, "title"=>$video->title]);
                }else{
                    $videoC = VideoFiles::find($video->id);
                    array_push($rdata, ["pic"=>$video->pic, "url"=> $videoC['url'], "title"=>$video->title, "duration"=>$videoC['duration'], "file_name"=>$videoC['file_name']]);
                }
            }
            return $rdata;
        }else{
            return [];
        }

    }

}