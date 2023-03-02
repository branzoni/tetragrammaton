<?php

namespace Tet\Database;

class TypesDef
{
    // символьные
    static function char($length = 20)
    {
        return self::get(__function__, $length);
    }

    static function varchar($length = 255)
    {
        return self::get(__function__, $length);
    }

    static function tinytext()
    {
        return self::get(__function__, null);
    }

    static function text()
    {
        return __function__;
    }

    static function mediumtext()
    {
        return __function__;
    }

    static function largetext()
    {
        return __function__;
    }

    // числовые
    static function bool()
    {
        return __function__;
    }

    static function boolean()
    {
        return __function__;
    }

    static function bit($length = 1)
    {
        return self::get(__function__, $length);
    }

    static function tinyint($length = 4, bool $unsigned = false, bool $zerofill = false)
    {
        return self::get(__function__, $length, $unsigned, $zerofill);
    }

    static function smallint($length = 6, bool $unsigned = false, bool $zerofill = false)
    {
        return self::get(__function__, $length, $unsigned, $zerofill);
    }

    static function mediumint($length = 9, bool $unsigned = false, bool $zerofill = false)
    {
        return self::get(__function__, $length, $unsigned, $zerofill);
    }

    static function int($length = 11, bool $unsigned = false, bool $zerofill = false)
    {
        return self::get(__function__, $length, $unsigned, $zerofill);
    }

    static function integer($length = 11, bool $unsigned = false, bool $zerofill = false)
    {
        return self::get(__function__, $length, $unsigned, $zerofill);
    }

    static function bigint($length = 20, bool $unsigned = false, bool $zerofill = false)
    {
        return self::get(__function__, $length, $unsigned, $zerofill);
    }

    static function decimal($precision = 5, $scale = 2, bool $unsigned = false, bool $zerofill = false)
    {
        return __function__ . "($precision, $scale, $unsigned, $zerofill)";
    }

    static function numeric($precision = 5, $scale = 2, bool $unsigned = false, bool $zerofill = false)
    {
        return self::decimal($precision, $scale, $unsigned, $zerofill);
    }

    static function dec($precision = 5, $scale = 2, bool $unsigned = false, bool $zerofill = false)
    {
        return self::decimal($precision, $scale, $unsigned, $zerofill);
    }

    static function fixed($precision = 5, $scale = 2, bool $unsigned = false, bool $zerofill = false)
    {
        return self::decimal($precision, $scale, $unsigned, $zerofill);
    }

    static function real($length = 11, bool $unsigned = false, bool $zerofill = false)
    {
        return self::get(__function__, $length, $unsigned, $zerofill);
    }

    static function float($length = 11, bool $unsigned = false, bool $zerofill = false)
    {
        return self::get(__function__, $length, $unsigned, $zerofill);
    }

    static function double($length = 11, bool $unsigned = false, bool $zerofill = false)
    {
        return self::get(__function__, $length, $unsigned, $zerofill);
    }

    // дата, время
    static function date()
    {
        return self::get(__function__, null);
    }

    static function time()
    {
        return self::get(__function__, null);
    }

    static function datetime()
    {
        return self::get(__function__, null);
    }

    static function timestamp()
    {
        return self::get(__function__, null);
    }

    static function year($length = 4)
    {
        return __function__ . "($length)";
    }


    // перечисления
    static function enum($length = 11, bool $unsigned = false)
    {
        return __function__ . "($length)";
    }

    static function set($length = 11, bool $unsigned = false)
    {
        return __function__ . "($length)";
    }


    // бинарные
    static function tinyblob()
    {
        return __function__;
    }

    static function blob()
    {
        return __function__;
    }

    static function mediumblob()
    {
        return __function__;
    }

    static function largeblob()
    {
        return __function__;
    }

    static function binary($length = 20)
    {
        return self::get(__function__, $length);
    }

    static function varbinary($length = 255)
    {
        return self::get(__function__, $length);
    }

    private static function get(string $name, ?int $length, bool $unsigned = false, bool $zerofill = false): TypeDef
    {
        // $result = $name;
        // $result .= $length ? "($length)" : "";
        // $result .= $unsigned ? " unsigned" : "";
        // $result .= $zerofill ? " zerofill" : "";
        // return mb_strtoupper($result);
        return new TypeDef($name, $length, $unsigned, $zerofill);
    }
}
