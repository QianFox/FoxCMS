<?php

/**
 * @Descripttion : FOXCMS 是一款高效的 PHP 多端跨平台内容管理系统
 * @Author : FoxCMS Team
 * @Date : 2023/6/26   19:07
 * @version : V1.08
 * @copyright : ©2021-现在 贵州黔狐科技股份有限公司 版权所有
 * @LastEditTime : 2023/6/26   19:07
 */

namespace app\common\util;

class SitemapXmlUtil
{
    private $writer;        // XMLWriter对象
    private $domain = "";            // 网站地图根域名
    private $xmlFile = "sitemap";                    // 网站地图xml文件（不含后缀.xml）
    private $xmlFileFolder = "";                    // 网站地图xml文件夹
    private $currXmlFileFullPath = "";                // 网站地图xml文件当前全路径
    private $isSchemaMore = true;                    // 网站地图是否添加额外的schema
    private $current_item = 0;                        // 网站地图item个数（序号）
    private $current_sitemap = 0;                    // 网站地图的个数（序号）

    const SCHEMA_XMLNS = 'http://www.sitemaps.org/schemas/sitemap/0.9';
    const SCHEMA_XMLNS_XSI = 'http://www.w3.org/2001/XMLSchema-instance';
    const SCHEMA_XSI_SCHEMALOCATION = 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd';
    const DEFAULT_PRIORITY = 0.5;
    const SITEMAP_ITEMS = 50000;
    const SITEMAP_SEPERATOR = '-';
    const INDEX_SUFFIX = 'index';
    const SITEMAP_EXT = '.xml';

    /**
     * @param string $domain	：	初始化网站地图根域名
     */
    public function __construct($domain)
    {
        $this->setDomain($domain);
    }

    /**
     * 设置网站地图根域名，开头用 http:// or https://, 结尾不要反斜杠/
     * @param string $domain	：	网站地图根域名 <br>例如: http://mimvp.com
     */
    public function setDomain($domain)
    {
        if (substr($domain, -1) == "/") {
            $domain = substr($domain, 0, strlen($domain) - 1);
        }
        $this->domain = $domain;
        return $this;
    }

    // 返回网站根域名
    private function getDomain()
    {
        return $this->domain;
    }

    // 设置网站地图的xml文件名
    public function setXmlFile($xmlFile)
    {
        $base = basename($xmlFile);
        $dir = dirname($xmlFile);
        if (!is_dir($dir)) {
            $res = mkdir(iconv("UTF-8", "GBK", $dir), 0777, true);
            if ($res) {
                echo "mkdir $dir success";
            } else {
                echo "mkdir $dir fail.";
            }
        }
        $this->xmlFile = $xmlFile;
        return $this;
    }

    // 返回网站地图的xml文件名
    private function getXmlFile()
    {
        return $this->xmlFile;
    }

    public function setIsChemaMore($isSchemaMore)
    {
        $this->isSchemaMore = $isSchemaMore;
    }

    private function getIsSchemaMore()
    {
        return $this->isSchemaMore;
    }

    // 设置XMLWriter对象
    private function setWriter(\XMLWriter $writer)
    {
        $this->writer = $writer;
    }

    // 返回XMLWriter对象
    private function getWriter()
    {
        return $this->writer;
    }

    // 返回网站地图的当前item
    private function getCurrentItem()
    {
        return $this->current_item;
    }

    // 设置网站地图的item个数加1
    private function incCurrentItem()
    {
        $this->current_item = $this->current_item + 1;
    }

    // 返回当前网站地图（默认50000个item则新建一个网站地图）
    private function getCurrentSitemap()
    {
        return $this->current_sitemap;
    }

    // 设置网站地图个数加1
    private function incCurrentSitemap()
    {
        $this->current_sitemap = $this->current_sitemap + 1;
    }

    private function getXMLFileFullPath()
    {
        $xmlfileFullPath = "";
        if ($this->getCurrentSitemap()) {
            $xmlfileFullPath = $this->getXmlFile() . self::SITEMAP_SEPERATOR . $this->getCurrentSitemap() . self::SITEMAP_EXT;    // 第n个网站地图xml文件名 + -n + 后缀.xml
        } else {
            $xmlfileFullPath = $this->getXmlFile() . self::SITEMAP_EXT;    // 第一个网站地图xml文件名 + 后缀.xml
        }
        $this->setCurrXmlFileFullPath($xmlfileFullPath);        // 保存当前xml文件全路径
        return $xmlfileFullPath;
    }

    public function getCurrXmlFileFullPath()
    {
        return $this->currXmlFileFullPath;
    }

    private function setCurrXmlFileFullPath($currXmlFileFullPath)
    {
        $this->currXmlFileFullPath = $currXmlFileFullPath;
    }

    private function startSitemap()
    {
        $this->setWriter(new \XMLWriter());
        $this->getWriter()->openURI($this->getXMLFileFullPath());    // 获取xml文件全路径
        $this->getWriter()->startDocument('1.0', 'UTF-8');
        $this->getWriter()->setIndentString("\t");
        $this->getWriter()->setIndent(true);
        $this->getWriter()->startElement('urlset');
        if ($this->getIsSchemaMore()) {
            $this->getWriter()->writeAttribute('xmlns:xsi', self::SCHEMA_XMLNS_XSI);
            $this->getWriter()->writeAttribute('xsi:schemaLocation', self::SCHEMA_XSI_SCHEMALOCATION);
        }
        $this->getWriter()->writeAttribute('xmlns', self::SCHEMA_XMLNS);
    }

    // 写入item元素，url、loc、priority字段必选，changefreq、lastmod可选
    public function addItem($loc, $priority = self::DEFAULT_PRIORITY, $changefreq = NULL, $lastmod = NULL)
    {
        if (($this->getCurrentItem() % self::SITEMAP_ITEMS) == 0) {
            if ($this->getWriter() instanceof \XMLWriter) {
                $this->endSitemap();
            }
            $this->startSitemap();
            $this->incCurrentSitemap();
        }
        $this->incCurrentItem();
        $this->getWriter()->startElement('url');
        $this->getWriter()->writeElement('loc', $loc);            // 必选
        $this->getWriter()->writeElement('priority', $priority);                    // 必选
        if ($changefreq) {
            $this->getWriter()->writeElement('changefreq', $changefreq);            // 可选
        }
        if ($lastmod) {
            $this->getWriter()->writeElement('lastmod', $this->getLastModifiedDate($lastmod));    // 可选
        }
        $this->getWriter()->endElement();
        return $this;
    }

    // 转义时间格式，返回时间格式为 2021-09-12
    private function getLastModifiedDate($date = null)
    {
        if (null == $date) {
            $date = time();
        }
        if (ctype_digit($date)) {
            return date('Y-m-d', $date);    // Y-m-d
        } else {
            $date = strtotime($date);
            return date('Y-m-d', $date);
        }
    }

    // 结束网站xml文档，配合开始xml文档使用
    public function endSitemap()
    {
        if (!$this->getWriter()) {
            $this->startSitemap();
        }
        $this->getWriter()->endElement();
        $this->getWriter()->endDocument();
        $this->getWriter()->flush();
    }

    public function createSitemapIndex($loc, $lastmod = 'Today')
    {
        $indexwriter = new XMLWriter();
        $indexwriter->openURI($this->getXmlFile() . self::SITEMAP_SEPERATOR . self::INDEX_SUFFIX . self::SITEMAP_EXT);
        $indexwriter->startDocument('1.0', 'UTF-8');
        $indexwriter->setIndent(true);
        $indexwriter->startElement('sitemapindex');
        $indexwriter->writeAttribute('xmlns:xsi', self::SCHEMA_XMLNS_XSI);
        $indexwriter->writeAttribute('xsi:schemaLocation', self::SCHEMA_XSI_SCHEMALOCATION);
        $indexwriter->writeAttribute('xmlns', self::SCHEMA_XMLNS);
        for ($index = 0; $index < $this->getCurrentSitemap(); $index++) {
            $indexwriter->startElement('sitemap');
            $indexwriter->writeElement('loc', $loc . $this->getFilename() . ($index ? self::SITEMAP_SEPERATOR . $index : '') . self::SITEMAP_EXT);
            $indexwriter->writeElement('lastmod', $this->getLastModifiedDate($lastmod));
            $indexwriter->endElement();
        }
        $indexwriter->endElement();
        $indexwriter->endDocument();
    }
}