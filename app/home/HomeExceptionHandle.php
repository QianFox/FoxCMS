<?php
/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   19:21
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   19:21
 */

namespace app\home;

use app\common\model\Basic as BasicModel;
use app\common\model\Contact as ContactModel;
use app\common\model\Column;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\ErrorException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\facade\View;
use think\Response;
use think\template\exception\TemplateNotFoundException;
use Throwable;

/**
 * 应用异常处理类
 */
class HomeExceptionHandle extends Handle
{
    /**
     * 不需要记录信息（日志）的异常类列表
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
    ];


    /**
     *  记录异常信息（包括日志或者其它方式记录）
     */
    public function report(Throwable $exception): void
    {
        // 使用内置的方式记录异常日志
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @access public
     * @param \think\Request   $request
     * @param Throwable $e
     * @return Response
     */
    public function render($request, Throwable $e): Response
    {
        // 添加自定义异常处理机制
        // 其他错误交给系统处理
//        return parent::render($request, $e);
        if (!$this->isJson) {
//            $response = Response::create($this->renderExceptionContent($e));
            $response = Response::create();
        } else {
            $response = Response::create($this->convertExceptionToArray($e), 'json');
        }

//        $url_model = xn_cfg("seo.url_model");//seoURL模式

        $lang = request()->param("lang");
        if(empty($lang)){
            $lang = xn_cfg("base.home_lang");
        }

        //网站基本信息
        $basic = [];
        $basicArr = BasicModel::where([['lang', '=', $lang]])->select()->toArray();
        $basicN = [];//基本属性返回
        if(sizeof($basicArr) > 0) {
            $basic = $basicArr[0];
            $basicN['keyword']= $basic['keyword'];
            $basicN['description']= $basic['description'];
        }
        $basicN['logo']= $basic['web_logo']??"/static/404/images/foxcms_logo.svg";
        $view_suffix = config('view.view_suffix');//模型文件后缀
        $contactArr = ContactModel::where([['lang', '=', $lang]])->field('company_tel')->select()->toArray();
        $contact = [];
        if(sizeof($contactArr)>0){
            $contact = $contactArr[0];
        }

        //查询栏目数据
        $columns = Column::where(['pid'=>0, 'status'=>1, 'lang'=>$lang])->select();
        $columnList = unlimitedForLayer($columns);
        if($e instanceof TemplateNotFoundException){//没找到模板
            $url404 = root_path()."/404.{$view_suffix}";
            if(!file_exists($url404)){
                $template = \app\common\model\Template::where('run_status',1)->find();
                $url404T = root_path().'templates/'. $template['template']."/". $template['html']."/404.{$view_suffix}";
                if(file_exists($url404T)){//文件存在的
                    $url404 = $url404T;
                }else{
                    $url404 = root_path()."/app/home/view/404.{$view_suffix}";
                }
                $basicN['title']= $basic['title']."-404"??"404";
                $content = View::fetch($url404, ['statusCode'=>404, 'navs'=>$columnList, 'basic'=>$basicN, 'contact'=>$contact]);
                $htmlfile = root_path()."/404.{$view_suffix}";
                @file_put_contents($htmlfile, $content);
            }else{
                $content = View::fetch($url404);
            }
            $response->content($content);
            return $response->code(404);
        }elseif ($e instanceof HttpException) {
            $statusCode = $e->getStatusCode();
            $response->header($e->getHeaders());
            if($statusCode == 404){//404模板
                $url404 = root_path()."/404.{$view_suffix}";
                if(!file_exists($url404)){
                    $template = \app\common\model\Template::where('run_status',1)->find();
                    $url404T = root_path().'templates/'. $template['template']."/". $template['html']."/404.{$view_suffix}";
                    if(file_exists($url404T)){//文件存在的
                        $url404 = $url404T;
                    }else{
                        $url404 = root_path()."/app/home/view/404.{$view_suffix}";
                    }
                    $basicN['title']= $basic['title']."-404"??"404";
                    $content = View::fetch($url404, ['statusCode'=>$statusCode, 'navs'=>$columnList, 'basic'=>$basicN, 'contact'=>$contact]);
                    $htmlfile = root_path()."/404.{$view_suffix}";
                    @file_put_contents($htmlfile, $content);
                }else{
                    $content = View::fetch($url404);
                }
            }else{
                $url404 = root_path()."/500.{$view_suffix}";
                if(!file_exists($url404)){
                    $template = \app\common\model\Template::where('run_status',1)->find();
                    $url404T = root_path().'templates/'. $template['template']."/". $template['html']."/500.{$view_suffix}";
                    if(file_exists($url404T)){//文件存在的
                        $url404 = $url404T;
                    }else{
                        $url404 = root_path()."/app/home/view/500.{$view_suffix}";
                    }
                    $basicN['title']= $basic['title']."-500"??"500";
                    $content = View::fetch($url404, ['statusCode'=>$statusCode, 'navs'=>$columnList, 'basic'=>$basicN, 'contact'=>$contact]);
                    $htmlfile = root_path()."/500.{$view_suffix}";
                    @file_put_contents($htmlfile, $content);
                }else{
                    $content = View::fetch($url404);
                }
            }
            $response->content($content);
            return $response->code($statusCode ?? 500);
        }elseif ($e instanceof ErrorException){
            $url500 = root_path()."/500.{$view_suffix}";
            if(!file_exists($url500)){
                $template = \app\common\model\Template::where('run_status',1)->find();
                $url500T = root_path().'templates/'. $template['template']."/". $template['html']."/500.{$view_suffix}";
                if(file_exists($url500T)){//文件存在的
                    $url500 = $url500T;
                }else{
                    $url500 = root_path()."/app/home/view/500.{$view_suffix}";
                }
                $basicN['title']= $basic['title']."-500"??"500";
                $content = View::fetch($url500, ['statusCode'=>500, 'navs'=>$columnList, 'basic'=>$basicN, 'contact'=>$contact]);
                $htmlfile = root_path()."/500.{$view_suffix}";
                @file_put_contents($htmlfile, $content);
            }else{
                $content = View::fetch($url500);
            }
            $response->content($content);
            return $response->code( 500);
        }
        return $response->code($statusCode ?? 500);
    }
}
