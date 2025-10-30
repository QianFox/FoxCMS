<?php

namespace app\taglib\fox;
use think\Request;

/**
 * 资源文件加载
 */
class TagStatic
{
    //初始化
    protected function _initialize()
    {
        parent::_initialize();
    }

    /**
     * 资源文件加载
     */
    public function getStatic($file = '', $lang = '', $href = '', $code='')
    {
        if (empty($file)) {
            return '标签static报错：缺少属性 file 或 href 。';
        }
        $file = !empty($href) ? $href : $file;
        $parseStr = '';
        $update_time = time();
        $parseStr .= $this->toHtml($file, $update_time);
        return $parseStr;
    }

    /**
     * 资源文件转化为html代码
     * @param string $file 文件路径|url路径
     * @param intval $update_time 文件时间戳
     */
    private function toHtml($file = '', $update_time = '')
    {
        $parseStr = '';
        if (!is_http_url($file)) {
            $file = \request()->url().DIRECTORY_SEPARATOR.'templates'.DIRECTORY_SEPARATOR.$file; // 支持子目录
        }
        $update_time_str = !empty($update_time) ? '?t='.$update_time : '';
        $type = strtolower(substr(strrchr($file, '.'), 1));
        switch ($type) {
            case 'js':
                $parseStr .= '<script type="text/javascript" src="' . $file . $update_time_str.'"></script>';
                break;
            case 'css':
                $parseStr .= '<link rel="stylesheet" type="text/css" href="' . $file . $update_time_str.'" />';
                break;
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'ico':
            case 'bmp':
            case 'gif':
            case 'webp':
                $parseStr .= $file . $update_time_str;
                break;
            case 'php':
                $parseStr .= '<?php include "' . $file . '"; ?>';
                break;
        }

        return $parseStr;
    }
}