<?php

namespace Tet;

class Utils
{
    function getRandomString(int $length = 40, string $keyspace = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"): String
    {
        //возвращает строку из произвольного количества случайных символов
        $tmp = '';
        $max = strlen($keyspace) - 1;
        for ($i = 0; $i < $length; ++$i) {
            $tmp .= $keyspace[random_int(0, $max)];
        }

        return $tmp;
    }

    static function getFormatedBytes($value, $format = "gb")
    {
        $tmp = $value;
        if ($format == "kb")  $tmp = $tmp / 1024;
        if ($format == "mb") $tmp = $tmp / 1024 / 1024;
        if ($format == "gb") $tmp = $tmp / 1024 / 1024 / 1024;
        $tmp = round($tmp, 2);
        return $tmp;
    }
}

