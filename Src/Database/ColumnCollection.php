<?php

namespace Tet\Database;

use Tet\Common\CollectionReadOnly;

class ColumnCollection extends CollectionReadOnly
{
    function get(string $name):ColumnDef
    {
        return $this->values[$name];
    }
}