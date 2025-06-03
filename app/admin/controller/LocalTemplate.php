<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   15:02
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   15:02
 */

namespace app\admin\controller;

use app\admin\util\Zipdown;
use app\common\controller\AdminBase;
use app\common\model\AuthRule;
use app\common\model\Template;
use app\common\model\UploadFiles as UploadFilesModel;
use OSS\Core\OssException;
use think\facade\View;
use think\File;

// 本地模板
class LocalTemplate extends AdminBase
{
    public function index($page = 1, $pageSize = 10)
    {
        $param = $this->request->param();
        $runStatus = 1; //默认的选择模板
        if (array_key_exists('runStatus', $param)) {
            $runStatus = $param["runStatus"];
        }
        View::assign("runStatus", $runStatus);

        if ($this->request->isAjax()) {
            $where = array();
            if (array_key_exists('runStatus', $param) && !empty($param['runStatus'])) {
                if (intval($param['runStatus']) == 2) {
                    array_push($where, ['run_status', '=', 2]);
                } else {
                    array_push($where, ['run_status', '<>', 2]);
                }
            }
            array_push($where, ['status', '=', 1]);
            $condition = array();
            if (array_key_exists('keyword', $param) && !empty($param['keyword'])) {
                array_push($condition, ['title|code', 'like', '%' . $param['keyword'] . '%']);
            }
            $list = (new Template())->where([$where])->where($condition)->order("sort", "desc")->paginate(['page' => $page, 'list_rows' => $pageSize])->toArray();
            $this->success('查询成功', '', $list);
        }
        return view('index');
    }

    // 模板设置
    public function localTemplateSet()
    {
        $param = $this->request->param();
        $id = $param['id'];
        $bcid = $param['bcid'];
        View::assign('bcid', $bcid);
        $ids = explode('_', $bcid);
        $authRuleId = $ids[sizeof($ids) - 1]; //栏目id
        $authRule = AuthRule::find($authRuleId);
        $bcidStr = str_replace(",", "_", $authRule->tier);
        $breadcrumb = AuthRule::getBreadcrumb($bcidStr);
        array_push($breadcrumb, ['id' => '', 'title' => '模板信息', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/LocalTemplate/templateSet', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);
        //查询模板
        $template = Template::find($id);
        View::assign("template", $template);

        return view('local_template_set');
    }

    // 模板导入
    public function importTemplate()
    {
        $param = $this->request->param();
        $columnId = $param['columnId'];
        $runStatus = $param['runStatus']; //当选的模板
        View::assign("runStatus", $runStatus);
        View::assign("columnId", $columnId);
        $authRule = AuthRule::find($columnId);
        $bcidStr = str_replace(",", "_", $authRule->tier);
        $breadcrumb = AuthRule::getBreadcrumb($bcidStr);
        array_push($breadcrumb, ['id' => '', 'title' => '本地模板导入', 'name' => DIRECTORY_SEPARATOR . config('adminconfig.admin_path') . '/LocalTemplate/import', 'url' => 'javascript:void(0)']);
        View::assign("breadcrumb", $breadcrumb);

        return view('import_template');
    }

    // 启用模板
    public function startUse()
    {
        if ($this->request->isGet()) {
            $param = $this->request->param();
            if (array_key_exists("id", $param)) {
                $id = $param['id'];
                $templateeModel = new Template();
                $templateeModel->startTrans();
                $oldTemp = $templateeModel->where('run_status', 1)->find();
                $isUpdateAll = false;
                $skinPath = config('filesystem.disks.public.root') . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . "skin"; //静态路径文件
                if ($oldTemp) { //原模板
                    $isUpdateAll = $templateeModel->update(["id" => $oldTemp->id, 'run_status' => 0, 'sort' => 0]);
                    $oldTempName = $oldTemp->template; //原模板
                    $oldTempDir = config('filesystem.disks.public.root') . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $oldTempName . DIRECTORY_SEPARATOR . "skin"; //原模板路径
                    if (tp_mkdir($oldTempDir)) {
                        xCopy($skinPath, $oldTempDir); //复制静态文件
                    }
                }
                $isUpdate = $templateeModel->update(['sort' => 1, 'run_status' => 1, 'id' => $id]);
                if ($isUpdateAll) {
                    if ($isUpdate) {
                        //复制启用模板的静态文件
                        $curTemp = $templateeModel->find($id);
                        if ($curTemp) {
                            $curTempName = $curTemp->template; //当前模板
                            $curTempDir = config('filesystem.disks.public.root') . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $curTempName . DIRECTORY_SEPARATOR . "skin"; //当前模板路径
                            delDir($skinPath); //先清空
                            //再复制当前的静态文件进去
                            if (tp_mkdir($skinPath)) {
                                xCopy($curTempDir, $skinPath); //复制静态文件
                            }
                        }
                        $templateeModel->commit();
                        $this->success('操作成功');
                    } else {
                        $templateeModel->rollback();
                        $this->error('操作失败');
                    }
                } else {
                    $templateeModel->rollback();
                    $this->error('操作失败');
                }
            }
        } else {
            $this->error('请求方式不对');
        }
    }

    // 安装模板
    public function installTemplate()
    {
        if ($this->request->isGet()) {
            $param = $this->request->param();
            if (array_key_exists("id", $param)) {
                $id = $param['id'];
                $templateeModel = new Template();
                $templateeModel->startTrans();
                $isUpdate = $templateeModel->update(['sort' => 0, 'run_status' => 0, 'id' => $id]);
                if ($isUpdate) {
                    $templateeModel->commit();
                    $this->success('操作成功');
                } else {
                    $templateeModel->rollback();
                    $this->error('操作失败');
                }
            }
        } else {
            $this->error('请求方式不对');
        }
    }

    // 导入模板
    public function import()
    {
        $allowedExts = array("zip");
        $temp = explode(".", $_FILES["file"]["name"]);
        $extension = end($temp);     // 获取文件后缀名
        if (in_array($extension, $allowedExts)) {
            if ($_FILES["file"]["error"] > 0) {
                return ['code' => 0, 'msg' => "错误：: " . $_FILES["file"]["error"]];
            } else {
                // 判断当期目录下的 upload 目录是否存在该文件
                // 如果没有 upload 目录，你需要创建它，upload 目录权限为 777
                if (file_exists("uploads" . DIRECTORY_SEPARATOR . $_FILES["file"]["name"])) {
                    return ['code' => 0, 'msg' => $_FILES["file"]["name"] . " 文件已经存在。 "];
                } else {
                    $zipdown = new Zipdown();
                    $fileName = $_FILES["file"]["name"]; //上传文件名
                    $fName = $zipdown->cutStr($fileName); //去掉后缀的文件名
                    $tem = Template::where('template', $fName)->find();
                    if ($tem) {
                        return ['code' => 0, 'msg' => $fName . " 模型已经存在。 "];
                    }
                    $result = UploadFilesModel::uploadFile(DIRECTORY_SEPARATOR . "template" . DIRECTORY_SEPARATOR . "files");
                    if ($result['id'] > 0) {
                        $type = $this->request->param("type");
                        if ($type == "template") { //模型
                            $file_path = config('filesystem.disks.public.root') . DIRECTORY_SEPARATOR . $result['file'];
                            $file = new File($file_path); //减压文件
                            $dirname = config('filesystem.disks.public.root') . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR; //解压目录

                            $r =  $zipdown->unZipFile($file, $dirname);
                            if ($r) { //解压成功
                                $decTemplate = $dirname . $fName; //解压的模型文件夹
                                $explainFilePath = $decTemplate . DIRECTORY_SEPARATOR . config('filesystem.template.explain'); //模板说明文件
                                $contentArr = $zipdown->getTxtContent($explainFilePath);
                                $saveData = []; //保存数据
                                foreach ($contentArr as $k => $content) {
                                    if (strpos($content, "#") !== false) { //存在
                                        $posNum = strpos($content, '-') + 1;
                                        $saveCont = substr($content, $posNum, strlen($content));
                                        $contentV = $contentArr[($k + 1)];
                                        if ($saveCont == "type") {
                                            $contentVArr = [];
                                            if (strpos($contentV, "电脑") !== false) {
                                                array_push($contentVArr, 1);
                                            }
                                            if (strpos($contentV, "手机") !== false) {
                                                array_push($contentVArr, 2);
                                            }
                                            if (strpos($contentV, "自适应") !== false) {
                                                array_push($contentVArr, 4);
                                            }
                                            if ((sizeof($contentVArr) == 2)) {
                                                $contentVArr = [3];
                                            }
                                            $contentV = implode(',', $contentVArr);
                                        }
                                        $saveData[$saveCont] = $contentV;
                                    } else {
                                        continue;
                                    }
                                }

                                //模板缩略图名字
                                $thumbFilePath = getFile($decTemplate, config('filesystem.template.thumb'), false);
                                $rtuf = UploadFilesModel::saveUpload(new File($thumbFilePath));
                                if ($rtuf && $rtuf['code'] == 1) {
                                    $saveData['thumb_id'] = $rtuf['id'];
                                }
                                //模板详细图名字
                                $detailFilePath = getFile($decTemplate, config('filesystem.template.detail'), false);
                                $rdfp = UploadFilesModel::saveUpload(new File($detailFilePath));
                                if ($rdfp && $rdfp['code'] == 1) {
                                    $saveData['detail_id'] = $rdfp['id'];
                                }
                                $saveData['template'] = $fName;
                                $saveData['directory'] = "templates" . DIRECTORY_SEPARATOR . $fName;
                                $saveData['run_status'] = 2; //未安装
                                if ($result && $result["code"] == 1) {
                                    $saveData['upload_template_id'] = $result["id"]; //上传模板文件id
                                }
                                (new Template())->save($saveData);
                            }
                        }
                    }
                    return json($result);
                }
            }
        } else {
            return ['code' => 0, 'msg' => $_FILES["file"]["name"] . "非法的文件格式"];
        }
    }

    // 导出模板
    public function export($id)
    {
        $templateModel = Template::find($id);
        $zipName = $templateModel->template;
        $dirname = config('filesystem.disks.public.root') . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $zipName;
        //模板说明文件
        $hh = "\r\n";
        $explain = "#模板标题-title" . $hh; //标题说明
        $explain .= $templateModel->title . $hh; //标题
        $explain .= "#模板编号-code" . $hh; //编码说明
        $explain .= $templateModel->code . $hh; //编码
        $explain .= "#模板作者-author" . $hh; //作者说明
        $explain .= $templateModel->author . $hh; //作者
        $explain .= "#发布时间-release_time" . $hh; //发布时间说明
        $explain .= $templateModel->release_time . $hh; //发布时间
        $explain .= "#模板类型-type" . $hh; //模板类型说明
        $typeCont = "自适应";
        if ($templateModel->type == 1) {
            $typeCont = "电脑";
        } elseif ($templateModel->type == 2) {
            $typeCont = "手机";
        } elseif ($templateModel->type == 3) {
            $typeCont = "电脑、手机";
        }
        $explain .= $typeCont . $hh; //类型
        $explain .= "#模板描述-describe" . $hh; //模板描述说明
        $explain .= $templateModel->describe . $hh; //模板描述
        $pfile = write($dirname . DIRECTORY_SEPARATOR, "explain.txt", $explain, FILE_USE_INCLUDE_PATH); //写说明文件

        $suffix_t = pathinfo($templateModel->thumb_url, PATHINFO_EXTENSION); //缩略图后缀名
        $suffix_d = pathinfo($templateModel->detail_url, PATHINFO_EXTENSION); //详细图后缀名
        //缩略图
        $uf = new UploadFilesModel();
        if ((strpos($templateModel->thumb_url, 'https:') !== false) || (strpos($templateModel->thumb_url, 'http:') !== false)) { //判断地址 缩略图
            $uf->download($templateModel->thumb_url, 'thumb.' . $suffix_t, $dirname);
        } else {
            $thumbUrlF = config('filesystem.disks.public.root') . $templateModel->thumb_url; //缩略图
            copyFile($thumbUrlF, $dirname . DIRECTORY_SEPARATOR . "thumb." . $suffix_t); //缩略图路径
        }

        //详细图
        if ((strpos($templateModel->detail_url, 'https:') !== false) || (strpos($templateModel->detail_url, 'http:') !== false)) { //判断地址 详细图
            $uf->download($templateModel->detail_url, 'detail.' . $suffix_d, $dirname);
        } else {
            $detailUrlF = config('filesystem.disks.public.root') . $templateModel->detail_url; //详细图
            copyFile($detailUrlF, $dirname . DIRECTORY_SEPARATOR . "detail." . $suffix_d); //缩略图路径
        }
        $templateFiles = dirFile($dirname, $dirname); //获取模板下所有文件

        (new Zipdown())->zipTemplate($templateFiles, $zipName);
        exit("导出失败");
    }

    // 删除模板
    public function delete()
    {
        $id = intval($this->request->get('id'));
        !($id > 0) && $this->error('参数错误');

        $templateModel = Template::find($id);
        $upload_template_id = $templateModel->upload_template_id; //模型文件id
        if (!empty($upload_template_id)) {
            \app\common\model\UploadFiles::destroy($upload_template_id);
        }
        $templateModel->destroy($id);
        $delDir = config('filesystem.disks.public.root') . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR . $templateModel->template;
        delDir($delDir); //删除模型文件
        $this->success('删除成功');
    }
}