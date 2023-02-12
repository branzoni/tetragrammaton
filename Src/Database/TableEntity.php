<?php

namespace Tet\Database;

use Tet\Common\CollectionReadOnly;

abstract class TableEntity
{
	public static string $tablename;
	public static MySQL $mySQL;

	protected function execute(): ?array
	{		
		$query = "SELECT * FROM "  . static::$tablename;		
		$rows = static::$mySQL->execute($query);
		return $rows;
	}

	function select(...$fields): CollectionReadOnly
	{
		if(gettype($fields) == "array"){
			if(count($fields) == 1) $fields = $fields[0];
		}
		
		if($fields == [])
		{
		 	$fields = "*";
		}else{
			$fields = implode(", " , $fields);
		}

		$query = "SELECT $fields FROM "  . static::$tablename;
		$rows = static::$mySQL->execute($query);		
		return new CollectionReadOnly($rows);
	}


	function getColumn(...$colnames): CollectionReadOnly
	{
		$tmp = $this->execute();

		$result = [];
		foreach ($colnames as $colname) {
			$result[$colname] = array_column($tmp, $colname);
		}
		return new CollectionReadOnly($result);
	}
}
