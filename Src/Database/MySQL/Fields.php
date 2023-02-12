<?php

namespace Tet\Database\MySQL;


use Tet\Common\CollectionReadOnly;

class Fields extends CollectionReadOnly
{
    function get(string $name):FieldDef
    {
        return $this->values[$name];
    }
}