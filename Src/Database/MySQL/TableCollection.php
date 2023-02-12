<?php

namespace Tet\Database\MySQL;

use Tet\Common\CollectionReadOnly;

class TableCollection extends CollectionReadOnly
{
    function get(string $name):Table
    {
        
        return $this->values[$name];
    }
}