<?php

class Drawable
{
    public function __construct($color, $font, $text, $x, $y, $width, $height, $fontSize)
    {
        $this->red = $color[0];
        $this->green = $color[1];
        $this->blue = $color[2];
        $this->x = $x;
        $this->y = $y;
        $this->width = $width;
        $this->height = $height;
        $this->font = $font;
        $this->text = $text;
        $this->fontSize = $fontSize;
    }
}

class Card
{
    public static function createDrawable($color, $font, $text, $x, $y, $width, $height, $fontSize = 0)
    {
        return new Drawable($color, $font, $text, $x, $y, $width, $height, $fontSize);
    }

    private static function envelope($font, $text, $width, $height, $fontSize)
    {
        $lastSize = $size = $fontSize;
        $lastHeight = 0;
        $lastWidth = 0;
        $lastAscent = 0;
        do {
            $box = imagettfbbox($size, 0, $font, $text);
            $lastWidth = @$realWidth;
            $lastHeight = @$realHeight;
            $realWidth = $box[2] - $box[0];
            $realHeight = $box[3] - $box[5];
            $lastAscent = abs($box[1]);
            if ($realWidth >= $width || $realHeight >= $height) {
                break;
            }
            $lastSize = $size;
            $size += 0.5;
        } while ($fontSize == 0);
        return [$lastSize, Accessor::either($lastWidth, $realWidth), Accessor::either($lastHeight, $realHeight), abs($box[0]), $lastAscent];
    }
    
    private static function doDraw($image, $drawable)
    {
        try {
            $color = imagecolorallocate($image, $drawable->red, $drawable->green, $drawable->blue);
            $envelope = self::envelope($drawable->font, $drawable->text, $drawable->width, $drawable->height, $drawable->fontSize);
            $xAdjust = (($drawable->width - $envelope[1]) / 2) + $envelope[3];
            $yAdjust = -$envelope[4];
            imagettftext($image, $envelope[0], 0, $drawable->x + $xAdjust, $drawable->y + $yAdjust, $color, $drawable->font, $drawable->text);
        } finally {
            imagecolordeallocate($image, $color);
        }
    }

    public static function generate($template, $drawables, $format = "jpeg")
    {
        return array_reduce($drawables, function($image, $drawable) {
            self::doDraw($image, $drawable);
            return $image;
        }, ("imagecreatefrom" . $format)($template));
    }
}

?>
