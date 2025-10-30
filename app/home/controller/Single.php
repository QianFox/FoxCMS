<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   19:20
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   19:20
 */

namespace app\home\controller;

use app\common\controller\IndexBase;
use app\common\model\Column;
use think\facade\View;

// 单页模型处理界面
class Single extends IndexBase
{
    protected $tempHtml;
    protected $view_suffix; //文件后缀
    protected function initialize()
    {
        parent::initialize();
        $this->view_suffix = config('view.view_suffix');
        $this->tempHtml = "index_single.{$this->view_suffix}";
        $id = $this->request->param("id");
        $columnModel = Column::find($id); //栏目模型

        View::assign("column", $this->columnHandle($columnModel));
        if ($columnModel) {
            if (!empty($columnModel['column_template'])) {
                $this->tempHtml = $columnModel['column_template'];
            }
        }
    }

    public function index($id)
    {
        $single = \app\common\model\Single::where('column_id', $id)->find();
        if ($single) {
            $single = [];
        }
        View::assign('single', $single);

        if (($this->templateType == 2 || $this->templateType == 3) && is_mobile()) { //判断是否手机访问
            $tempHtml = $this->mobileHtml($this->tempHtml, $this->view_suffix);
            $templateMobilePath = $this->templateHtml . $tempHtml;
            if (file_exists($templateMobilePath)) { //判断文件是否存在
                $this->tempHtml = $tempHtml;
            }
        }

        $template = $this->templateHtml . $this->tempHtml;
        $content = View::fetch($template);
        return access_stat_js($content, $this->domainNo);
    }
}