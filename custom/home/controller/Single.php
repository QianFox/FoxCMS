<?php
namespace app\home\controller;

use app\common\controller\IndexBase;
use app\common\model\Column;
use think\facade\View;

class Custom extends IndexBase
{

    protected $tempHtml;

    // 初始化
    protected function initialize()
    {
        parent::initialize();
        $iew_suffix = config('view.view_suffix');
        $this->tempHtml = "index_custom.{$iew_suffix}";
        $id = $this->request->param("id");
        $columnModel = Column::find($id);//栏目模型
        View::assign("column", $columnModel);
        if($columnModel){
            if(!empty($columnModel['column_template'])){
                $this->tempHtml = $columnModel['column_template'];
            }
        }
    }

    public function index($id)
    {
        $custom = \app\common\model\Custom::where('column_id', $id)->find();
        View::assign('single', $custom);
        if(($this->templateType == 2||$this->templateType == 3) && is_mobile()){//判断是否手机访问
            $tempHtml = $this->mobileHtml($this->tempHtml, $this->view_suffix);
            $templateMobilePath = $this->templateHtml . $tempHtml;
            if(file_exists($templateMobilePath)){//判断文件是否存在
                $this->tempHtml = $tempHtml;
            }
        }
        $template = $this->templateHtml . $this->tempHtml;
        $content = View::fetch($template);
        return access_stat_js($content, $this->domainNo);
    }
}