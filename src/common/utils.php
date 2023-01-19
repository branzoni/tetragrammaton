<?php

namespace Tet;

class Utils
{

    function getRandomString($length = 40, $keyspace = ""): String
    {
        //возвращает строку из произвольного количества случайных символов
        if ($keyspace = "") $keyspace = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";

        $tmp = '';
        $max = strlen($keyspace) - 1;
        
        for ($i = 0; $i < $length; ++$i) {
            $tmp .= $keyspace[random_int(0, $max)];
        }

        return $tmp;
    }
    
}
