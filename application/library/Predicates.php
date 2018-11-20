<?php

class Predicates
{
    public static function isNotNull($arg)
    {
        return !Predicates::isNull($arg);
    }

    public static function isNull($arg)
    {
        return is_null($arg);
    }

    public static function isNotEmpty($arg)
    {
        return !Predicates::isEmpty($arg);
    }

    public static function isEmpty($arg)
    {
        if (is_null($arg)) {
            return true;
        }
        if (is_string($arg)) {
            return strlen(trim($arg)) == 0;
        } else if (is_scalar($arg)) {
            return false;
        } else {
            return empty($arg);
        }
    }

    public static function isObject($arg)
    {
        return is_object($arg);
    }

    public static function isNotObject($arg)
    {
        return !self::isObject($arg);
    }

    public static function isArray($arg)
    {
        return is_array($arg);
    }

    public static function isNotArray($arg)
    {
        return !self::isArray($arg);
    }

    public static function isString($arg)
    {
        return is_string($arg);
    }

    public static function isNotString($arg)
    {
        return !self::isString($arg);
    }

    public static function isNumeric($arg)
    {
        return is_numeric($arg);
    }

    public static function isNotNumeric($arg)
    {
        return !self::isNumeric($arg);
    }

    public static function equals($lhs, $rhs)
    {
        return $lhs === $rhs;
    }

    public static function isNotPositive($arg)
    {
        return is_numeric($arg) && $arg <= 0;
    }

    public static function isPositive($arg)
    {
        return is_numeric($arg) && $arg > 0;
    }

    public static function isNotNegative($arg)
    {
        return is_numeric($arg) && $arg >= 0;
    }

    public static function isNegative($arg)
    {
        return is_numeric($arg) && $arg < 0;
    }
}

?>
