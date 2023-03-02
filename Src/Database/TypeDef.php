<?php

namespace Tet\Database;

class TypeDef
{
    private string $name;
    private  ?int $length;
    private bool $unsigned = false;
    private bool $zerofill = false;

    function __construct(string $name, ?int $length, bool $unsigned = false, bool $zerofill = false)
    {
        $this->name = $name;
        $this->length = $length;
        $this->unsigned = $unsigned;
        $this->zerofill = $zerofill;
    }

    function getName()
    {
        return $this->name;
    }

    function __toString()
    {
        $result = $this->name;
        $result .= $this->length ? "($this->length)" : "";
        $result .= $this->unsigned ? " unsigned" : "";
        $result .= $this->zerofill ? " zerofill" : "";
        return mb_strtoupper($result);
    }
}
