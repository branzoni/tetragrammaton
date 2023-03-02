<?php

namespace Tet\Database;

use ArrayObject;
use JetBrains\PhpStorm\ArrayShape;
use Tet\Common\CollectionReadOnly;

class TableCollection extends CollectionReadOnly
{
    function get(string $name):TableDef
    {
        
        return $this->values[$name];
    }
}