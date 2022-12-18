<?php


namespace Tetra;

use Exception;
use Properless;

class Prop extends Properless
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
