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
}

