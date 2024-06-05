<?php

namespace library8;

class WaterMark
{
    public function __construct(
        // 图片
        private Image $image,
        // 颜色
        private Color $color,
        // 字体
        private string $font = 'fz/FZBSJW',
        // 字号
        private int $font_size = 24,

        // 旋转角度
        private int $angle = 0,
        // 是否平铺(铺满)
        private bool $is_tile = true,

        private int $repeat_row = 1,
        private int $repeat_col = 1,
        // 重复时 行间距
        private int $row_space = 10,
        // 重复时 列间距
        private int $col_space = 10,

        /** 非平铺时 定位 */
        private bool $is_locate = false,
        // 水平 left,centre,right
        private string $horizontal_location = 'left',
        // 垂直 top,middle,bottom
        private string $vertical_location = 'top',

        /** 非平铺，无定位时 */
        // 偏移 x
        private int $x = 0,
        // 偏移 y
        private int $y = 0,

        // 内容间行间距
        private $content_space = 10
    )
    {
        if (!$color) {
            $color = new Color(255, 255, 255);
        }

        $this->color = $color;
    }

    public function add(array $contents)
    {
        if (!$this->image) {
            throw new \Exception('未设置图片');
        }

        $fontfile = self::getFontfile($this->font);

        // if ($angle < 0 || $angle > 90) {
        //     throw new \Exception('不允许的旋转角度，请填写0-90的数值');
        // }

        $new_img = $this->image->createNewImg();

        $color = $this->color->getImgColor($new_img);

        $contents_box = new ContentBox($contents, $this->angle, $this->content_space, $fontfile, $this->font_size);

        if ($this->is_tile) {
            $locate_params = $this->tile($contents_box);
        } elseif ($this->is_locate) {
            $locate_params = $this->locate($contents_box);
        } else {
            $locate_params = $this->draw();
        }

        $box = $contents_box->getBox();
        $contents = $contents_box->getContents();        
        $per_xy = $contents_box->getPerXy();
        $per_box_xy = $contents_box->getPerBoxXy();

        for ($i = 0; $i < $locate_params['cols']; $i++) {
            for ($j = 0; $j < $locate_params['rows']; $j++) {
                $temp_add_x = $locate_params['x'] + $i * ($box['width'] + $locate_params['col_space']);
                $temp_add_y = $locate_params['y'] + $j * ($box['height'] + $locate_params['row_space']);

                foreach ($contents as $index => $content) {
                    $content_x = $locate_params['x'] + $per_xy[$index]['x'] - $per_box_xy[$index]['x'] + $temp_add_x;
                    $content_y = $locate_params['y'] + $per_xy[$index]['y'] - $per_box_xy[$index]['y'] + $temp_add_y;

                    imagettftext($new_img, $this->font_size, $this->angle, $content_x, $content_y, $color, $fontfile, $content);
                }
            }
        }

        // 输出
        $output_func = 'image' . $this->image->getType();
        
        header('Content-type: ' . $this->image->getMime());
        $output_func($new_img);

        // 销毁
        imagedestroy($new_img);
    }

    /**
     * 铺满
     *
     * @param ContentBox $contents_box
     * @return array
     */
    private function tile(ContentBox $contents_box)
    {
        $image_width = $this->image->getWidth();
        $image_height = $this->image->getHeight();

        $box = $contents_box->getBox();

        /** 
         * 计算铺满的行/列,间距,边距
         * $repeat_col,$repeat_row
         * $col_space,$row_space
         * $margin_width,$margin_height
         */
        // 是否设置重复列数
        if (!$this->repeat_col) {
            // 未设置，使用列间距，计算列数
            $repeat_col = floor(($image_width + $this->col_space) / ($box['width'] + $this->col_space));
            
            // content内容 宽度 超过图片
            if ($repeat_col == 0) {
                // 超过，间距/边距 为0
                $repeat_col = 1;
                $col_space = 0;
                $margin_width = 0;
            } else {
                // 不超过，计算边距
                $col_space = $this->col_space;
                $water_mark_width = $repeat_col * ($box['width'] + $col_space) - $col_space;
                $margin_width = floor(($image_width - $water_mark_width) / 2);
            }
        } else {
            // 已设置，计算列间距
            $repeat_col = $this->repeat_col;
            $water_mark_nospace_width = $repeat_col * $box['width'];

            // content内容 高度 超过图片
            if ($water_mark_nospace_width > $image_width) {
                // 超过，间距/边距 为0
                $repeat_col = ceil($image_width / $box['width']);
                $col_space = 0;
                $margin_width = 0;
            } else {
                // 不超过，计算边距
                $col_space = ($repeat_col - 1) ? floor(($image_width - $water_mark_nospace_width) / ($repeat_col - 1)) : 0;
                $water_mark_width = $repeat_col * ($box['width'] + $col_space) - $col_space;
                $margin_width = floor(($image_width - $water_mark_width) / 2);
            }
        }
        // 逻辑同上
        if (!$this->repeat_row) {
            $repeat_row = floor(($image_height + $this->row_space) / ($box['height'] + $this->row_space));
            if ($repeat_row == 0) {
                $repeat_row = 1;
                $row_space = 0;
                $margin_height = 0;
            } else {
                $row_space = $this->row_space;
                $water_mark_height = $repeat_row * ($box['height'] + $row_space) - $row_space;
                $margin_height = floor(($image_height - $water_mark_height) /2);
            }
        } else {
            $repeat_row = $this->repeat_row;
            $water_mark_nospace_height = $repeat_row * $box['height'];
            if ($water_mark_nospace_height > $image_height) {
                $repeat_row = ceil($image_height / $box['height']);
                $row_space = 0;
                $margin_height = 0;
            } else {
                $row_space = ($repeat_row - 1) ? floor(($image_height - $water_mark_nospace_height) / ($repeat_row - 1)) : 0;
                $water_mark_height = $repeat_row * ($box['height'] + $row_space) - $row_space;
                $margin_height = floor(($image_height - $water_mark_height) /2);
            }
        }

        return [
            'x' => $margin_width,
            'y' => $margin_height,
            'cols' => $repeat_col,
            'rows' => $repeat_row,
            'col_space' => $col_space,
            'row_space' => $row_space
        ];
    }
    /**
     * 定位
     *
     * @param ContentBox $contents_box
     * @return array
     */
    private function locate(ContentBox $contents_box)
    {
        $img_width = $this->image->getWidth();
        $img_height = $this->image->getHeight();

        $box = $contents_box->getBox();

        $min_x = $min_y = PHP_INT_MAX;
        $max_x = $max_y = PHP_INT_MIN;
        //计算总体偏移量
        $x = 0;
        $y = 0;
        switch ($this->horizontal_location) {
            case 'left':
                if ($min_x < 0) {
                    $x = 0 - $min_x;
                }
                break;
            case 'center':
                $diff_width = $img_width - $box['width'];
                $center_x = ceil($diff_width / 2);
                $x = $center_x - $min_x;
                break;
            case 'right':
                if ($max_x > $img_width) {
                    $x = 0 - ($max_x = $img_width);
                } else {
                    $x = $img_width - $max_x;
                }
                break;
        }
        switch ($this->vertical_location) {
            case 'top':
                if ($min_y < 0) {
                    $y = 0 - $min_y;
                }
                break;
            case 'middle':
                $diff_height = $img_height - $box['height'];
                $center_y = ceil($diff_height / 2);
                $y = $center_y - $min_y;
                break;
            case 'under':
                if ($max_y > $img_height) {
                    $y = 0 - ($min_y - $img_height);
                } else {
                    $y = $img_height - $max_y;
                }
                break;
        }

        return [
            'x' => $x,
            'y' => $y,
            'cols' => $this->repeat_col ?: 1,
            'rows' => $this->repeat_row ?: 1,
            'col_space' => $this->col_space,
            'row_space' => $this->row_space
        ];
    }
    /**
     * 直接画
     *
     * @return array
     */
    private function draw()
    {
        return [
            'x' => $this->x,
            'y' => $this->y,
            'cols' => $this->repeat_col ?: 1,
            'rows' => $this->repeat_row ?: 1,
            'col_space' => $this->col_space,
            'row_space' => $this->row_space
        ];
    }

    /**
     * 获取字体路径
     *
     * @param string $font
     * @return string
     */
    private static function getFontfile($font)
    {
        $dir = __DIR__ . '/../fonts/' . $font . '.ttf';
        if (!file_exists($dir)) {
            throw new \Exception('不存在的字体: ' . $font);
        }
        return $dir;
    }


    public function getImage()
    {
        return $this->image;
    }

    public function setImageByPath($image_path): self
    {
        $image = new Image($image_path);
        $this->image = $image;

        return $this;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function setColorByRGBA($red, $green, $blue, $alpha = 0): self
    {
        $color = new Color($red, $green, $blue, $alpha);
        $this->color = $color;

        return $this;
    }
    public function setColorByCode($code): self
    {
        $code = trim("#");

        $arr = str_split($code, 2);

        $red = hexdec($arr[0]);
        $green = hexdec($arr[1]);
        $blue = hexdec($arr[2]);

        if (isset($arr[3])) {
            $alpha = hexdec($arr[3]);
            if ($alpha > 100) {
                $alpha = 100;
            }
        } else {
            $alpha = 0;
        }

        return $this->setColorByRGBA($red, $green, $blue, $alpha);
    }

    public function setColor($color): self
    {
        $this->color = $color;

        return $this;
    }

    public function getFont()
    {
        return $this->font;
    }

    public function setFont($font): self
    {
        $this->font = $font;

        return $this;
    }

    public function getFontSize()
    {
        return $this->font_size;
    }

    public function setFontSize($font_size): self
    {
        $this->font_size = $font_size;

        return $this;
    }

    public function getAngle()
    {
        return $this->angle;
    }

    public function setAngle($angle): self
    {
        $this->angle = $angle;

        return $this;
    }

    public function getIsTile()
    {
        return $this->is_tile;
    }

    public function setIsTile($is_tile): self
    {
        $this->is_tile = $is_tile;

        return $this;
    }

    public function getX()
    {
        return $this->x;
    }

    public function setX($x): self
    {
        $this->x = $x;

        return $this;
    }

    public function getY()
    {
        return $this->y;
    }

    public function setY($y): self
    {
        $this->y = $y;

        return $this;
    }

    public function getRepeatRow()
    {
        return $this->repeat_row;
    }

    public function setRepeat($repeat_row): self
    {
        $this->repeat_row = $repeat_row;

        return $this;
    }

    public function getRepeatCol()
    {
        return $this->repeat_col;
    }

    public function getHorizontalLocation()
    {
        return $this->horizontal_location;
    }

    public function setHorizontalLocation($horizontal_location): self
    {
        $this->horizontal_location = $horizontal_location;

        return $this;
    }

    public function getVerticalLocation()
    {
        return $this->vertical_location;
    }

    public function setVerticalLocation($vertical_location): self
    {
        $this->vertical_location = $vertical_location;

        return $this;
    }

    public function getContentSpace()
    {
        return $this->content_space;
    }

    public function setContentSpace($content_space): self
    {
        $this->content_space = $content_space;

        return $this;
    }

    public function getRowSpace()
    {
        return $this->row_space;
    }

    public function setRowSpace($row_space): self
    {
        $this->row_space = $row_space;

        return $this;
    }

    public function getColSpace()
    {
        return $this->col_space;
    }

    public function setColSpace($col_space): self
    {
        $this->col_space = $col_space;

        return $this;
    }

    public function setImage(Image $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getIsLocate()
    {
        return $this->is_locate;
    }

    public function setIsLocate($is_locate): self
    {
        $this->is_locate = $is_locate;

        return $this;
    }
}
