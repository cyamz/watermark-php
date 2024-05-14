<?php

namespace libaray;

class Image
{
    protected $path;
    protected $width;
    protected $height;
    protected $type;
    protected $mime;

    public function __construct($img_path)
    {
        // 获取图片信息
        $img_info = getimagesize($img_path);
        if (!$img_info) {
            throw new \Exception('错误的图片路径');
        }
        $this->width = $img_info[0]; // 图片宽度
        $this->height = $img_info[1]; // 图片高度
        $this->mime = $img_info['mime'];

        $this->type = self::getImgExtensionBySizeType($img_info[2]);
        if (!$this->type) {
            throw new \Exception('不支持的图片文件类型: ' . $img_info[2] . ' - ' . $img_info['mime']);
        }

        $this->path = $img_path;
    }

    public function createNewImg()
    {
        // 创建画布
        $new_img = imagecreatetruecolor($this->width, $this->height);
        // 创建一个带有水印的新图片
        $create_func = 'imagecreatefrom' . $this->type;
        $original_image = $create_func($this->path);
        // 在画布上绘制原始图片
        imagecopy($new_img, $original_image, 0, 0, 0, 0, $this->width, $this->height);

        return $new_img;
    }

    /**
     * 获取IMAGETYPE对应的GD库后缀
     *
     * @param int $size_type
     * @return string
     */
    protected static function getImgExtensionBySizeType($size_type)
    {
        switch ($size_type) {
            case 1:
                return 'gif';
            case 2:
                return 'jpeg';
            case 3:
                return 'png';
            case 6:
                return 'bmp';
            case 15:
                return 'wbmp';
            case 16:
                return 'xbm';
            case 18:
                return 'webp';
            //gd库不支持的类型
            //相关mime - image_type_to_mime_type() - https://www.runoob.com/php/php-image-type-to-mime-type.html
            case 4: //swf
            case 5: //psd
            case 7: //tiff_ii
            case 8: //tiff_mm
            case 9: //jpc
            case 10: //jp2
            case 11: //jpx
            case 12: //jb2
            case 13: //swc
            case 14: //iff
            case 17: //ico
            default:
                return '';
        }
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getMime()
    {
        return $this->mime;
    }
}