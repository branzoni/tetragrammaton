<?php

namespace Tet\Common;

use Tet\Common\CollectionReadOnly;

class Collection extends CollectionReadOnly
{
    function set(string $name, $value)
    {
        $this->values[$name] = $value;
    }

    function add(...$values)
    {
        if (count($values) == 1) $values = $values[0];

        foreach ($values as $name => $value) {
            $this->set($name, $value);
        }
    }
}
