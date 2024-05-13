<?php

namespace libaray;

class WaterMark1
{
    //预设颜色
    protected static $colors = [];

    public static function getColors()
    {
        if (!self::$colors) {
            $color_json = file_get_contents(__DIR__ . '/../resources/color.json');
            self::$colors = json_decode($color_json, true);
        }

        return self::$colors;
    }

    /**
     * 获取预设颜色的RGB
     *
     * @param string $color_name
     * @return array{red=>int,green=>int,blue=>int}
     */
    public static function getColor($color_name)
    {
        $colors = self::getColors();

        if (!isset($colors[$color_name])) {
            throw new \Exception('未定义的颜色: ' . $color_name);
        }

        return $colors[$color_name];
    }

    /**
     * 根据所给长宽，分割水印字符串
     *
     * @param array|string $contents
     * @param int $p_width
     * @param int $p_height
     * @param int $font_size
     * @param int $font
     * @return array{contents=>{},font_size=>int}
     *
     * @throws \Exception
     */
    public static function catContent($contents, $p_width, $p_height, $font_size, $font)
    {
        $original_contents = $contents;

        $fontfile = self::getFontfile($font);

        if (!is_array($contents)) {
            $contents = [$contents];
        }

        $fit_index = -1;
        while (true) {
            $total_height = 0;
            foreach ($contents as $index => $content) {
                $temp_box1 = imagettfbbox($font_size, 0, $fontfile, $content);
                //跳过已经适应的字符串
                if ($index <= $fit_index) {
                    $height = self::getNoAngleBoxHeight($temp_box1);
                    $total_height += $height;
                    continue;
                }

                //获取长度
                $width = self::getNoAngleBoxWidth($temp_box1);
                //当超长时，找出最长匹配字符串，将剩余字段添加到下一行
                if ($width > $p_width) {
                    $str_len = mb_strlen($content);
                    for ($i = $str_len; $i > 0; $i--) {
                        $temp_content = mb_substr($content, 0, $i);

                        $temp_box2 = imagettfbbox($font_size, 0, $fontfile, $temp_content);
                        $width = self::getNoAngleBoxWidth($temp_box2);
                        if ($width <= $p_width) {
                            break;
                        }
                    }
                    //当一个字符都超长时，更换字体大小重试
                    if (0 === $i) {
                        //最小字体判断
                        if ($font_size === 1) {
                            throw new \Exception('无法自适应');
                        }
                        $font_size = $font_size * 3 / 4;
                        return self::catContent($original_contents, $p_width, $p_height, $font_size, $font);
                    }
                    $contents[$index] = $temp_content;
                    $left_string = mb_substr($content, $i, $str_len - 1);
                    if (!isset($contents[$index + 1])) {
                        $contents[$index + 1] = '';
                    }
                    $contents[$index + 1] = $left_string . $contents[$index + 1];

                    $fit_index = $index;
                    continue 2;
                }

                $height = self::getNoAngleBoxHeight($temp_box1);
                $total_height += $height;
            }

            //当长度过长时，减小字体重试
            if ($total_height > $p_height) {
                //最小字体判断
                if ($font_size === 1) {
                    throw new \Exception('无法自适应');
                }
                $font_size = $font_size * 3 / 4;
                return self::catContent($original_contents, $p_width, $p_height, $font_size, $font);
            }

            return compact('contents', 'font_size');
        }

    }

    /**
     * 1. 铺满 
     *  1.1 定义行列
     *  1.2 定义间隙
     * 2. 不铺满
     *  2.1 多行多列
     *   2.1.1 定义位置
     *   2.1.2 定义偏移
     *  2.2 一行一列
     *   2.2.1 定义位置
     *   2.2.2 定义偏移
     */
    public static function multiFill($img_path, $contents, $font_size, $space, $color_r, $color_g, $color_b, $color_alpha, $angle, $font, $row = 1, $col = 1, $output_name = '')
    {

    }
    public static function spaceFill($img_path, $contents, $font_size, $space, $color_r, $color_g, $color_b, $color_alpha, $angle, $font, $row_space = 10, $col_space = 10, $output_name = '')
    {

    }

    public static function multiLocate($img_path, $contents, $font_size, $space, $color_r, $color_g, $color_b, $color_alpha, $angle, $location, $font, $row = 1, $col = 1, $row_space = 10, $col_space = 10, $output_name = '')
    {

    }
    public static function multiXY($img_path, $contents, $font_size, $space, $color_r, $color_g, $color_b, $color_alpha, $angle, $font, $draw_x = 0, $draw_y = 0, $row = 1, $col = 1, $row_space = 10, $col_space = 10, $output_name = '')
    {

    }

    public static function locate($img_path, $contents, $font_size, $space, $color_r, $color_g, $color_b, $color_alpha, $angle, $location, $font, $output_name = '')
    {

    }
    public static function XY($img_path, $contents, $font_size, $space, $color_r, $color_g, $color_b, $color_alpha, $angle, $font, $fix, $fill, $draw_x = 0, $draw_y = 0, $output_name = '')
    {

    }



    /**
     * 添加水印 todo: 铺满和定位逻辑分开
     *
     * @param string $img_path 图片路径
     * @param array $contents 内容
     * @param int $font_size 字体大小
     * @param int $space 行间距
     * @param int $color_r 颜色r
     * @param int $color_g 颜色g
     * @param int $color_b 颜色b
     * @param int $color_alpha 颜色-透明度
     * @param int $angle 旋转角度
     * @param int $location 
     * @param string $font 字体
     * @param bool $fix 是否自适应（自动换行，缩小字体） todo: 行列超时自适应
     * @param bool $fill 铺满
     * @param int $draw_x 横向偏移量
     * @param int $draw_y 纵向偏移量
     * @param bool $row 重复行
     * @param bool $col 重复列
     * @param int $row_space 水印行间距
     * @param int $col_space 水印列间距
     * @param string $output_name 保存的文件名（为空则直接输出到浏览器)
     * @return string|void
     *
     * @throws \Exception
     */
    public static function addWaterMark($img_path, $contents, $font_size, $space, $color_r, $color_g, $color_b, $color_alpha, $angle, $location, $font, $fix, $fill, $draw_x = 0, $draw_y = 0, $row = 1, $col = 1, $row_space = 10, $col_space = 10, $output_name = '')
    {
        $fontfile = self::getFontfile($font);

        if ($angle < 0 || $angle > 90) {
            throw new \Exception('不允许的旋转角度，请填写0-90的数值');
        }

        // 获取待处理图片信息
        $img_info = getimagesize($img_path);
        $img_width = $img_info[0]; // 图片宽度
        $img_height = $img_info[1]; // 图片高度

        $img_type = self::getImgExtensionBySizeType($img_info[2]);
        if (!$img_type) {
            throw new \Exception('不支持的图片文件类型: ' . $img_info[2] . ' - ' . $img_info['mime']);
        }

        // 创建画布
        $new_img = imagecreatetruecolor($img_width, $img_height);
        // 创建一个带有水印的新图片
        $create_func = 'imagecreatefrom' . $img_type;
        $original_image = $create_func($img_path);
        // 在画布上绘制原始图片
        imagecopy($new_img, $original_image, 0, 0, 0, 0, $img_width, $img_height);

        // 颜色
        $color = imagecolorallocatealpha($new_img, $color_r, $color_g, $color_b, $color_alpha);

        // 行间距偏移量
        if ($angle > 0) {
            $_x = ceil($space / cos(deg2rad($angle)));
            $_y = ceil($space / sin(deg2rad($angle)));
        } else {
            $_x = $space;
            $_y = 0;
        }

        //每一行的偏移量
        $per_xy = [['x' => 0, 'y' => 0]];
        $per_box_xy = [];

        //自适应
        if ($fix) {
            $fix_result = self::catContent($contents, $img_width, $img_height, $font_size, $font);
            $contents = $fix_result['contents'];
            $font_size = $fix_result['font_size'];
        }
        //计算最大正方形
        $max_x = $max_y = PHP_INT_MIN;
        $min_x = $min_y = PHP_INT_MAX;
        foreach ($contents as $index => $content) {
            $temp_box = imagettfbbox($font_size, $angle, $fontfile, $content);

            //每个box的偏移量
            $per_box_xy[] = [
                'x' => $temp_box[6],
                'y' => $temp_box[7],
            ];
            //保证左上角为0,0
            $_temp_box = [
                'ld' => ['x' => $temp_box[0] - $temp_box[6], 'y' => $temp_box[1] - $temp_box[7]], // 左下 x,y
                'rd' => ['x' => $temp_box[2] - $temp_box[6], 'y' => $temp_box[3] - $temp_box[7]], // 右下 x,y
                'rh' => ['x' => $temp_box[4] - $temp_box[6], 'y' => $temp_box[5] - $temp_box[7]], // 右上 x,y
                'lh' => ['x' => $temp_box[6] - $temp_box[6], 'y' => $temp_box[7] - $temp_box[7]], // 左上 x,y
            ];

            $next_xy = [
                'x' => $_temp_box['ld']['x'] - $_temp_box['lh']['x'] + $_x + $per_xy[$index]['x'],
                'y' => $_temp_box['ld']['y'] - $_temp_box['lh']['y'] + $_y + $per_xy[$index]['y'],
            ];

            $per_xy[] = $next_xy;

            $_temp_box['ld']['x'] += $next_xy['x'];
            $_temp_box['ld']['y'] += $next_xy['y'];
            $_temp_box['rd']['x'] += $next_xy['x'];
            $_temp_box['rd']['y'] += $next_xy['y'];

            $min_x = min($min_x, $_temp_box['ld']['x'], $_temp_box['rd']['x'], $_temp_box['rh']['x'], $_temp_box['lh']['x']);
            $max_x = max($max_x, $_temp_box['ld']['x'], $_temp_box['rd']['x'], $_temp_box['rh']['x'], $_temp_box['lh']['x']);
            $min_y = min($min_y, $_temp_box['ld']['y'], $_temp_box['rd']['y'], $_temp_box['rh']['y'], $_temp_box['lh']['y']);
            $max_y = max($max_y, $_temp_box['ld']['y'], $_temp_box['rd']['y'], $_temp_box['rh']['y'], $_temp_box['lh']['y']);
        }

        //box长宽
        $box_width = $max_x - $min_x;
        $box_height = $max_y - $min_y;

        // 铺满 计算行列
        if ($fill) {
            $row = ceil($img_height / ($box_height + $row_space));
            $col = ceil($img_width / ($box_width + $col_space));
        } else {
            if ($col != 1) {
                $col_space = ceil(($img_width - ($box_width * $col)) / ($col - 1));
            }
            if ($row != 1) {
                $row_space = ceil(($img_height - ($box_height * $row)) / ($row - 1));
            }
        }

        if (1 != $col) {
            $max_width = ($box_width + $col_space) * $col;
            $max_x += ($box_width + $col_space) * ($col - 1);
        } else {
            $max_width = $box_width;
        }
        if (1 != $row) {
            $max_height = ($box_height + $row_space) * $row;
            $max_y += ($box_height + $row_space) * ($row - 1);
        } else {
            $max_height = $box_height;
        }

        //计算总体偏移量
        $x = 0;
        $y = 0;
        list($x_location, $y_location) = explode('_', $location);
        switch ($x_location) {
            case 'left':
                if ($min_x < 0) {
                    $x = 0 - $min_x;
                }
                break;
            case 'center':
                $diff_width = $img_width - $max_width;
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
        switch ($y_location) {
            case 'top':
                if ($min_y < 0) {
                    $y = 0 - $min_y;
                }
                break;
            case 'middle':
                $diff_height = $img_height - $max_height;
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

        //加水印
        for ($c = 0; $c < $col; $c++) {
            for ($r = 0; $r < $row; $r++) {
                $temp_add_x = $c * ($box_width + $col_space);
                $temp_add_y = $r * ($box_height + $row_space);
                foreach ($contents as $index => $content) {
                    $content_x = $x + $per_xy[$index]['x'] - $per_box_xy[$index]['x'] + $temp_add_x;
                    $content_y = $y + $per_xy[$index]['y'] - $per_box_xy[$index]['y'] + $temp_add_y;

                    imagettftext($new_img, $font_size, $angle, $content_x, $content_y, $color, $fontfile, $content);
                }
            }
        }

        // 输出
        $output_func = 'image' . $img_type;
        if (!$output_name) {
            header('Content-type: ' . $img_info['mime']);
            $output_func($new_img);
            exit;
        } else {
            $path_name = './outputs/' . $output_name . '.' . $img_type;
            if (file_exists($path_name)) {
                $new_path_name = './outputs/' . $output_name . '-' . uniqid() . random_int(1, 100) . '.' . $img_type;
                copy($path_name, $new_path_name);
                @unlink($path_name);
            }
            $output_func($new_img, $path_name, 100);
        }

        // 销毁
        imagedestroy($new_img);

        return trim($path_name, '.');
    }

    public static function getLocation($img_path, $location)
    {

    }

    /**
     * 根据box获取宽度（无旋转)
     *
     * @param array $box
     * @return int
     */
    private static function getNoAngleBoxWidth($box)
    {
        return $box[4] - $box[6]; //右上x - 左上x
    }

    /**
     * 根据box获取高度（无旋转)
     *
     * @param array $box
     * @return int
     */
    private static function getNoAngleBoxHeight($box)
    {
        return $box[1] - $box[7]; //左下y - 左上y
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

    /**
     * 获取IMAGETYPE对应的GD库后缀
     *
     * @param int $size_type
     * @return string
     */
    private static function getImgExtensionBySizeType($size_type)
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

}
