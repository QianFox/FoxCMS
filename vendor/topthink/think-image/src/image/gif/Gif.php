<?php
namespace think\image\gif;
class Gif
{
    private $frames = [];
    private $delays = [];
    public function __construct($src = null, $mod = 'url')
    {
        if (!is_null($src)) {
            if ('url' == $mod && is_file($src)) {
                $src = file_get_contents($src);
            }
            /* 解码GIF图片 */
            try {
                $de           = new Decoder($src);
                $this->frames = $de->getFrames();
                $this->delays = $de->getDelays();
            } catch (\Exception $e) {
                throw new \Exception("解码GIF图片出错");
            }
        }
    }

    /**
     * 设置或获取当前帧的数据
     * @param  string $stream 二进制数据流
     */
    public function image($stream = null)
    {
        if (is_null($stream)) {
            $current = current($this->frames);
            return false === $current ? reset($this->frames) : $current;
        }
        $this->frames[key($this->frames)] = $stream;
    }

    /**
     * 将当前帧移动到下一帧
     */
    public function nextImage()
    {
        return next($this->frames);
    }

    /**
     * 编码并保存当前GIF图片
     */
    public function save($pathname)
    {
        $gif = new Encoder($this->frames, $this->delays, 0, 2, 0, 0, 0, 'bin');
        file_put_contents($pathname, $gif->getAnimation());
    }
}