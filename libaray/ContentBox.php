<?php

namespace libaray;

class ContentBox
{

    private $contents;
    private $box;
    private $per_box_xy;
    private $per_xy;

    public function __construct($contents, $angle, $content_space, $fontfile, $font_size)
    {
        // 行间距偏移量
        if ($angle > 0) {
            $_x = ceil($content_space / cos(deg2rad($angle)));
            $_y = ceil($content_space / sin(deg2rad($angle)));
        } else {
            $_x = $content_space;
            $_y = 0;
        }

        //每一行的偏移量
        $per_xy = [['x' => 0, 'y' => 0]];
        $per_box_xy = [];

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
        $width = $max_x - $min_x;
        $height = $max_y - $min_y;

        $this->box = compact('min_x', 'min_y', 'max_x', 'max_y', 'width', 'height');
        $this->per_box_xy = $per_box_xy;
        $this->per_xy = $per_xy;
    }


    public function getContents()
    {
        return $this->contents;
    }

    public function setContents($contents): self
    {
        $this->contents = $contents;

        return $this;
    }

    public function getBox()
    {
        return $this->box;
    }

    public function setBox($box): self
    {
        $this->box = $box;

        return $this;
    }

    public function getPerBoxXy()
    {
        return $this->per_box_xy;
    }

    public function setPerBoxXy($per_box_xy): self
    {
        $this->per_box_xy = $per_box_xy;

        return $this;
    }

    public function getPerXy()
    {
        return $this->per_xy;
    }

    public function setPerXy($per_xy): self
    {
        $this->per_xy = $per_xy;

        return $this;
    }
}