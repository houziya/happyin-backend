<?php

class Frameset
{
    private $rows;
    private $cols;
    private $width;
    private $height;
    private $border;
    private $edge;
    private $spacing;
    private $inputs = [];
    private $orientation;
    private $numberOfPictures;
    private static $specs = [];

    private static function checkAndGetSpec($orientation, $cols, $rows, $width, $height, $border, $edge, $spacing)
    {
        $key = implode("-", [$orientation, $cols, $rows, $width, $height, $border, $edge, $spacing]);
        if (!array_key_exists($key, self::$specs)) {
            $layout = [];
            $index = 0;
            if ($orientation == 0) {
                for ($row = 0; $row < $rows; ++$row) {
                    for ($col = 0; $col < $cols; ++$col) {
                        $layout[$index++] = [
                            $border + ($col * ($edge + $spacing)), 
                            $border + ($row * ($edge + $spacing)), 
                            $edge, $edge
                        ];
                    }
                }
            } else {
                $tmp = $rows;
                $rows = $cols;
                $cols = $tmp;
                $tmp = $height;
                $height = $width;
                $width = $tmp;
                for ($col = 0; $col < $cols; ++$col) {
                    for ($row = $rows - 1; $row >= 0; --$row) {
                        $layout[$index++] = [
                            $border + ($col * ($edge + $spacing)), 
                            $border + ($row * ($edge + $spacing)), 
                            $edge, $edge
                        ];
                    }
                }
            }
            $specs[$key] = ["width" => $width, "height" => $height, "layout" => $layout];
        }
        return $specs[$key];
    }

    private function spec()
    {
        return self::checkAndGetSpec($this->orientation, $this->cols, $this->rows, $this->width, $this->height, $this->border, $this->edge, $this->spacing);
    }

    private function layout($index)
    {
        return $this->spec()["layout"][$index];
    }

    public static function builder($orientation, $cols, $rows, $width, $height, $edge, $spacing, $dpi = 300, $unit = "cm")
    {
        return new Frameset($orientation, $cols, $rows, $width, $height, $edge, $spacing, $dpi, $unit);
    }

    public function __construct($orientation, $cols, $rows, $width, $height, $edge, $spacing, $dpi, $unit) {
        $unit = ($unit === "cm") ? 2.54 : 1;
        $this->rows = Preconditions::checkPositive($rows);
        $this->cols = Preconditions::checkPositive($cols);
        $this->width = ($width * $dpi) / $unit;
        $this->height = ($height * $dpi) / $unit;
        $this->edge = ($edge * $dpi) / $unit;
        $this->spacing = ($spacing * $dpi) / $unit;
        $this->border = ($this->width - ($this->edge * $this->cols) - ($this->spacing * ($this->cols - 1))) / 2;
        $this->numberOfPictures = $this->rows * $this->cols;
        $this->orientation = $orientation;
    }

    public function reset()
    {
        $this->inputs = [];
    }

    public function add($input, $area)
    {
        Preconditions::checkArgument(count($this->inputs) < $this->numberOfPictures);
        $this->inputs[] = [$input, $area, $this->layout(count($this->inputs))];
        return $this;
    }

    public function build($output = NULL, $quality = 75)
    {
        Preconditions::checkArgument(count($this->inputs) == $this->numberOfPictures);
        $arguments = array_reduce($this->inputs, function($carry, $input) {
            $carry = array_merge($carry, $input);
            return $carry;
        }, []);
        $spec = $this->spec();
        Photographer::drawArray([$spec["width"], $spec["height"]], $output, $quality, $arguments);
    }
};

?>
