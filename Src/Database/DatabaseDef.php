<?php

namespace Tet\Database;

class DatabaseDef
{
    public $name;
    private $charset;
    public TableCollection $tables;

    function __construct($name, $charset)
    {
        $this->name = $name;
        $this->charset = $charset;
        $this->tables = new TableCollection([]);
    }

    function addTable(TableDef $newTable): TableDef
    {
        $tmp = $this->tables->toArray();
        $tmp[$newTable->name] = $newTable;
        $this->tables = new TableCollection($tmp);
        return $newTable;
    }

    function getTables(): TableCollection
    {
        return $this->tables;
    }
}
