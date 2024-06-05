<?php

namespace library;

Class Color
{
    protected $red;
    protected $green;
    protected $blue;

    protected $alpha;

    /**
     * Undocumented function
     *
     * @param integer $red 0-255
     * @param integer $green 0-255
     * @param integer $blue 0-255
     * @param integer 0-127 0为完全不透明
     */
    public function __construct($red, $green, $blue, $alpha = 0)
    {
        $this->red = $red;
        $this->green = $green;
        $this->blue = $blue;
        $this->alpha = $alpha;
    }


    public static function createPresetColor($name, $alpha = 0)
    {
        $color_json = file_get_contents(__DIR__ . "/../resources/color.json");
        $color_arr = json_decode($color_json, true);

        $color = $color_arr[$name];
        return new self($color['red'], $color['green'], $color['blue'], $alpha);
    }

    public function getImgColor(&$img)
    {
        return imagecolorallocatealpha($img, $this->red, $this->green, $this->blue, $this->alpha);
    }

    public function getRed()
    {
        return $this->red;
    }

    public function setRed($red): self
    {
        $this->red = $red;

        return $this;
    }

    public function getGreen()
    {
        return $this->green;
    }

    public function setGreen($green): self
    {
        $this->green = $green;

        return $this;
    }

    public function getBlue()
    {
        return $this->blue;
    }

    public function setBlue($blue): self
    {
        $this->blue = $blue;

        return $this;
    }

    public function getAlpha()
    {
        return $this->alpha;
    }

    public function setAlpha($alpha): self
    {
        $this->alpha = $alpha;

        return $this;
    }
}