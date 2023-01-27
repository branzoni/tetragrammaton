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

    function createtEnumerator(string $destination, string $name, array $enums):bool
    {
        $fn = "$name.php";
        $f = fopen("$destination\\$fn", 'w');

        fwrite($f, "<?php\r\n");
        fwrite($f, "\r\n");
        fwrite($f, "class $name\r\n");
        fwrite($f, "{\r\n");
            foreach ($enums as $value) {
                $constName = strtoupper($value);
                $constValue = $value;
                fwrite($f, "    const $constName = '$constValue';\r\n");
            }    
        fwrite($f, "}\r\n");
        fclose($f);
        return true;
    }    
}
