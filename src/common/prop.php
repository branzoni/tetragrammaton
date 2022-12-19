<?php


namespace Tetra;

class Prop
{
    private $value;

    function set($value)
    {
        $this->value = $value;
    }

    function get()
    {        
        return $this->value;
    }

    function __toString()
    {
        return $this->value;
    }
}
