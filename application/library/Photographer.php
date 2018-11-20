<?php

use lsolesen\pel\PelJpeg;
use lsolesen\pel\PelTag;

class Photographer {
    private static $debug = false;
    private static $border = [255, 255, 255];

    private static function output($image, $output, $quality) 
    {
        if (self::$debug) {
            return;
        }
        if (Predicates::isNull($output)) {
            header("Content-Type: image/jpeg");
            ob_start();
            self::saveImage($image, NULL, $quality);
            header("Content-Length:" . ob_get_length());
            echo ob_get_flush();
        } else {
            self::saveImage($image, $output, $quality);
        }
    }

    private static function copy($dest, $src, $destX, $destY, $srcX, $srcY, $destWidth, $destHeight, $srcWidth, $srcHeight)
    {
        if (self::$debug) {
            var_dump([$destX, $destY, $srcX, $srcY, $destWidth, $destHeight, $srcWidth, $srcHeight]);
            return;
        }
        $area = self::fixArea($src, [$srcX, $srcY, $srcWidth, $srcHeight]);
        $srcX = $area[0];
        $srcY = $area[1];
        $srcWidth = $area[2];
        $srcHeight = $area[3];
        if ($srcWidth != $destWidth || $srcHeight != $destHeight) {
            imagecopyresampled($dest, $src, $destX, $destY, $srcX, $srcY, $destWidth, $destHeight, $srcWidth, $srcHeight);
        } else {
            imagecopy($dest, $src, $destX, $destY, $srcX, $srcY, $destWidth, $destHeight);
        }
    }

    private static function fixOrientation($image, $file)
    {
        try {
            $orientation = Execution::withFallback(function() use ($file) {
                return exif_read_data($file)["Orientation"];
            }, function() use ($file) {
                if (($exif = (new PelJpeg($file))->getExif())) {
                    if (($tiff = $exif->getTiff())) {
                        if (($ifd = $tiff->getIfd())) {
                            if (($entry = $ifd->getEntry(PelTag::ORIENTATION))) {
                                return $entry->getValue();
                            }
                        }
                    }
                }
                return 0;
            });
            switch ($orientation) {
            case 8:
                $image = imagerotate($image, 90, 0);
                break;
            case 3:
                $image = imagerotate($image, 180, 0);
                break;
            case 6:
                $image = imagerotate($image, -90, 0);
                break;
            }
        } catch (Exception $ignored) {
            error_log("Could not fix orientation of file '" . $file . "'\n" . $ignored->getTraceAsString());
        }
        return $image;
    }

    private static function drawTo($canvas, $area, $box, $file)
    {
        $toX = intval($box[0]);
        $toY = intval($box[1]);
        $toWidth = intval($box[2]);
        $toHeight = intval($box[3]);
        try {
            $image = self::loadImage($file);
            if (Predicates::isNull($area)) {
                $fromX = 0;
                $fromY = 0;
                $fromWidth = imagesx($image);
                $fromHeight = imagesy($image);
            } else {
                $fromX = intval($area[0]);
                $fromY = intval($area[1]);
                $fromWidth = intval($area[2]);
                $fromHeight = intval($area[3]);
            }
            $image = self::fixOrientation($image, $file);
            self::copy($canvas, $image, $toX, $toY, $fromX, $fromY, $toWidth, $toHeight, $fromWidth, $fromHeight);
        } catch (Exception $ex) {
            error_log("Could not load image from '" . $file . "'\n" . $ex->getTraceAsString());
            throw $ex;
        } finally {
            if (@$image) {
                imagedestroy($image);
            }
        }
    }

    public static function drawArray($canvas, $output, $quality, $inputs) {
        $count = count($inputs);
        if ($count % 3 != 0) {
            throw new Exception("Input and drawing area not paired up");
        }
        $target = imagecreatetruecolor($canvas[0], $canvas[1]);
        try {
            $background = imagecolorallocate($target, self::$border[0], self::$border[1], self::$border[2]);
            imagefill($target, 0, 0, $background);
            for ($index = 0; $index < $count; $index += 3) {
                $realInput = $inputs[$index];
                $realArea = $inputs[$index + 1];
                $realBox = $inputs[$index + 2];
                self::drawTo($target, $realArea, $realBox, $realInput);
            }
            self::output($target, $output, $quality);
        } finally {
            imagedestroy($target);
        }
    }

    public static function draw($canvas, $output, $quality, $input, $area, $box, ...$others) {
        self::drawArray($canvas, $output, $quality, array_merge([$input, $area, $box], $others));
    }

    public static function loadImage($file, $format = null) {
        if (Predicates::isNull($format)) {
            $parts = explode(".", $file);
            $format = strtolower(array_pop($parts));
            if (!in_array($format, ["jpg", "jpeg", "png", "gd", "xpm", "gif", "webp", "xbm"])) {
                $parts = explode("/", getimagesize($file)["mime"]);
                $format = array_pop($parts);
            }
            if ($format == "jpg") {
                $format = "jpeg";
            }
        }
        return ("imagecreatefrom" . $format)($file);
    }

    public static function saveImage($image, $file = NULL, $quality = 75) {
        if (Predicates::isNull($file)) {
            $format = "jpeg";
        } else {
            $parts = explode(".", $file);
            $format = strtolower(array_pop($parts));
            if (!in_array($format, ["jpg", "jpeg", "png", "gd", "xpm", "gif", "webp", "xbm"])) {
                $format = "jpeg";
            } else if ($format == "jpg") {
                $format = "jpeg";
            }
        }
        return ("image" . $format)($image, $file, $quality);
    }

    private static function fixArea($image, $area)
    {
        $width = imagesx($image);
        $height = imagesy($image);
        if ($area[0] + $area[2] <= $width && $area[1] + $area[3] <= $height) {
            return $area;
        }
        /* use width as most significant metric */
        $fullWidth = ($area[0] * 2) + $area[2];
        $fullHeight = ($area[1] * 2) + $area[3];
        $xScale = $width / $fullWidth;
        $yScale = $height / $fullHeight;
        if ($xScale != 0) {
            $area[0] = intval($area[0] * $xScale);
            $area[2] = intval($area[2] * $xScale);
        }
        if ($yScale != 0) {
            $area[1] = intval($area[1] * $yScale);
            $area[3] = intval($area[3] * $yScale);
        }
        if ($area[0] + $area[2] > $width) {
            $area[2] = $width - $area[0];
        }
        if ($area[1] + $area[3] > $height) {
            $area[3] = $height - $area[1];
        }
        return $area;
    }

    public static function clip($input, $area, $border, $output = NULL, $quality = 75) {
        try {
            $src = self::fixOrientation(imagecreatefromjpeg($input), $input);
            $fromX = intval($area[0]);
            $fromY = intval($area[1]);
            $fromWidth = intval($area[2]);
            $fromHeight = intval($area[3]);
            $realBorder = $fromWidth > $fromHeight ? intval($fromWidth * $border) : intval($fromHeight * $border);
            $toWidth = $fromWidth + (2 * $realBorder);
            $toHeight = $fromHeight + (2 * $realBorder);
            if ($toWidth <= 0) {
                $toWidth = 1;
            }
            if ($toHeight <= 0) {
                $toHeight = 1;
            }
            $dest = imagecreatetruecolor($toWidth, $toHeight);
            $background = imagecolorallocate($dest, self::$border[0], self::$border[1], self::$border[2]);
            imagefill($dest, 0, 0, $background);
            self::copy($dest, $src, $realBorder, $realBorder, $fromX, $fromY, $fromWidth, $fromHeight, $fromWidth, $fromHeight);
            self::output($dest, $output, $quality);
        } finally {
            if (@$src) {
                imagedestroy($src);
            }
            if (@$dest) {
                imagedestroy($dest);
            }
        }
    }

    public static function dpi($file, $dpi)
    {
        try {
            $file = fopen($file, 'r+');
            fseek($file, 13);
            $tmp1 = chr(floor($dpi / 256));
            $tmp2 = chr(floor($dpi % 256));
            $data = chr(1) . $tmp1 . $tmp2 . $tmp1 . $tmp2;
            fwrite($file, $data);
        } finally {
            if (@$file) {
                fclose($file);
            }
        }
    }

    public static function resize($file, $format, $newWidth, $newHeight, $quality = 75, $dpi = null, $flip = false)
    {
        try {
            $src = self::loadImage($file, $format);
            if ($flip) {
                $dest = imagerotate($src, -90, 0);
                imagedestroy($src);
                $src = $dest;
                $dest = null;
            }
            $dest = imagecreatetruecolor($newWidth, $newHeight);
            self::copy($dest, $src, 0, 0, 0, 0, $newWidth, $newHeight, imagesx($src), imagesy($src));
            ("image" . $format)($dest, $file, $quality);
            if (Predicates::isNotNull($dpi)) {
                self::dpi($file, $dpi);
            }
        } finally {
            if (@$src) {
                imagedestroy($src);
            }
            if (@$dest) {
                imagedestroy($dest);
            }
        }
    }
};

?>
