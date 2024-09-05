<?php
namespace app\home\controller;

use app\common\controller\IndexBase;
use app\common\model\Column;
use think\facade\View;

class Custom extends IndexBase
{

    protected $columnModel;//当前栏目对象

    protected $view_suffix;//文件后缀
    // 初始化
    protected function initialize()
    {
        parent::initialize();
        $id = $this->request->param("id");
        $action = $this->request->action();
        if($action == "detail"){
            $custom = \app\common\model\Custom::find($id);
            View::assign("article", $custom);
            $this->columnModel = Column::find($custom['column_id']);//栏目模型
        }else{
            $this->columnModel = Column::find($id);//栏目模型
        }
        View::assign("column", $this->columnModel);
        $this->view_suffix = config('view.view_suffix');
    }


    public function index()
    {
        $tempHtml = "list_custom.{$this->view_suffix}";
        if($this->columnModel){
            if(!empty($this->columnModel['column_template'])){
                $tempHtml = $this->columnModel['column_template'];
            }
        }
        if(($this->templateType == 2||$this->templateType == 3) && is_mobile()){//判断是否手机访问
            $tempHtmlMobile = $this->mobileHtml($tempHtml, $this->view_suffix);
            $tempHtmlMobilePath =  $this->templateHtml . $tempHtmlMobile;
            if(file_exists($tempHtmlMobilePath)){//判断文件是否存在
                $tempHtml = $tempHtmlMobile;
            }
        }

        $template = $this->templateHtml . $tempHtml;
        $content = View::fetch($template);
        return access_stat_js($content, $this->domainNo);
    }

    public function  detail(){
        $model_template = "view_custom.{$this->view_suffix}";
        if($this->columnModel){
            if(!empty($this->columnModel['model_template'])){
                $model_template = $this->columnModel['model_template'];
            }
        }

        if(($this->templateType == 2||$this->templateType == 3) && is_mobile()){//判断是否手机访问
            $model_templateMobile = $this->mobileHtml($model_template, $this->view_suffix);
            $model_templateMobilePath =  $this->templateHtml . $model_templateMobile;
            if(file_exists($model_templateMobilePath)){//判断文件是否存在
                $model_template = $model_templateMobile;
            }
        }

        $template = $this->templateHtml . $model_template;
        $content = View::fetch($template);
        return access_stat_js($content, $this->domainNo);
    }
}