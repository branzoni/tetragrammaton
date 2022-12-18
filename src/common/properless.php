<?php


class Properless
{
    function __set($propname, $value)
    {
        throw new Exception("try to set unknown property name '$propname'");
    }

    function __get($propname)
    {
        throw new Exception("try to get unknown property name '$propname'");
    }
}
