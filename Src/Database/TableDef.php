<?php

namespace Tet\Database;

class TableDef
{
    public string $name;
    public string $charset;
	public string $engine;
    public ColumnCollection $columns;
	public array $indexes;

    function __construct(string $name,  $charset = "utf8", $engine = "INNODB")
    {
        $this->name = $name;
        $this->charset = $charset;
		$this->engine = $engine;
        $this->columns = new ColumnCollection();
		$this->indexes = [];
    }

    function addColumn(string $name, TypeDef $type): ColumnDef
    {
        $column = new ColumnDef($name, $type);
        $tmp = $this->columns->toArray();
        $tmp[$column->getName()] = $column;
        $this->columns = new ColumnCollection($tmp);
        return $column;
    }

	function addIndex(...$columnNames)
	{
		$this->indexes[] = $columnNames;
	}

    function __toString()
    {
        $query = "CREATE TABLE $this->name (\r\n";
        $query .= implode(",\r\n", $this->columns->toArray()) . "\r\n";
        $query .= ")\r\n";
        $query .= "ENGINE = $this->engine,\r\n";
        $query .= "CHARACTER SET $this->charset\r\n";
        return $query;
    }
}
