<?php

namespace Tet\Database;

use Tet\Common\CollectionReadOnly;


class SelectBuilder
{

	private static MySQL $mySQL;
	private static string $tablename;
	private static $columns;
	private static $where = null;
	private static $order = null;

	function __construct ($mySQL, $tablename, ...$columns)
	{
		self::$mySQL = $mySQL;
		self::$tablename = $tablename;
		self::$columns = $columns;
		return self::class;
	}
	

	static function where(string $where)
	{
		self::$where = $where;
		return self::class;
	}

	static function orderBy($order)
	{
		self::$order = $order;
		return self::class;
	}

	static function execute()
	{
		$columns = self::$columns;

		if (gettype($columns) == "array" && count(self::$columns) == 1) $columns = $columns[0];
		
		if ($columns == []) $columns = "*";

		if (gettype($columns) == "array") $columns = implode(", ", $columns);

		$query = "SELECT $columns FROM " . self::$tablename;
		if(self::$where) $query .=  " WHERE " . self::$where;
		//return $query;
		return self::$mySQL->execute($query);
	}
}

abstract class TableEntity
{
	public static string $tablename;
	public static MySQL $mySQL;

	function execute($query = null): ?array
	{
		$query = $query ?? "SELECT * FROM "  . static::$tablename;		
		return static::$mySQL->execute($query);
	}

	function insert(...$columns)
	{
		if (gettype($columns) == "array") {
			if (count($columns) == 1) $columns = $columns[0];
		}

		$values = [];
		$columns_new = [];
		foreach ($columns as $column => $value) {
			$columns_new[] = "`$column`";
			$values[] = gettype($value) == "string" ?  "'$value'" : $value;
		}

		$columns_new =  implode(", ", $columns_new);
		$values =  implode(", ", $values);

		$query = "INSERT INTO " . static::$tablename . " ($columns_new) VALUES ($values)";

		return  static::$mySQL->execute($query);
	}

	static function select(...$columns):SelectBuilder
	{
		return new SelectBuilder(static::$mySQL, static::$tablename, $columns);
	}


	protected function execute2(): ?array
	{
		$query = "SELECT * FROM "  . static::$tablename;
		$rows = $this->execute($query);
		return $rows;
	}


	// function select(...$columns): CollectionReadOnly
	// {
	// 	if (gettype($columns) == "array") {
	// 		if (count($columns) == 1) $columns = $columns[0];
	// 	}

	// 	if ($columns == []) {
	// 		$columns = "*";
	// 	} else {
	// 		$columns = implode(", ", $columns);
	// 	}

	// 	$query = "SELECT $columns FROM "  . static::$tablename;
	// 	$rows = static::$mySQL->execute($query);
	// 	return new CollectionReadOnly($rows);
	// }


	// function deleteWhere(...$where)
	// {
	// 	if (gettype($where) == "array") {
	// 		if (count($where) == 1) $where = $where[0];
	// 	}

	// 	$values = [];
	// 	$where_new = [];
	// 	$result = "DELETE FROM " . $this::$tablename . " WHERE TRUE ";
	// 	foreach ($where as $column => $value) {
	// 		//$columns_new[] = "`$column`";
	// 		$value = gettype($value) == "string" ?  "'$value'" : $value;
	// 		$result .= " AND $column = $value";
	// 	}
	// }




	// function getColumn(...$colnames): CollectionReadOnly
	// {
	// 	$tmp = $this->execute2();

	// 	$result = [];
	// 	foreach ($colnames as $colname) {
	// 		$result[$colname] = array_column($tmp, $colname);
	// 	}
	// 	return new CollectionReadOnly($result);
	// }
}
