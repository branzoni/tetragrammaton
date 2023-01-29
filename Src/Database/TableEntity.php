<?php

namespace Tet;

abstract class TableEntity
{
	public static string $tablename;
	public static MySQL $mySQL;

	public function createSelectQuery(array $fields, $where = null, array $orderBy = null)
	{
		$query = new Query;
		$query->command = $query::COMMAND_SELECT;
		$query->fields->add($fields);
		$query->tablename = static::$tablename;
		return static::$mySQL->execute($query)->data;		
	}

	public function createUpdateQuery(array $fields, $where): string
	{
		$query = new Query;
		$query->command = $query::COMMAND_UPDATE;
		$query->fields->add($fields);
		$query->tablename = static::$tablename;
		return $query;
	}

	public function createInsertQuery(array $fields): string
	{
		$query = new Query;
		$query->command = $query::COMMAND_INSERT;
		$query->fields->add($fields);
		$query->tablename = static::$tablename;
		return $query;
	}

	public function createDeleteQuery($where): string
	{
		$query = new Query;
		$query->command = $query::COMMAND_DELETE;
		$query->where = $where;
		$query->tablename = static::$tablename;
		return $query;
	}
	
	// function duplicate($idFieldName, $idFieldValue): Result
	// {

	// 	$fieldsString = implode(", ", $this->getFieldNames());
	// 	$query = "INSERT INTO `" . $this->name . "` ($fieldsString) SELECT " . str_replace($idFieldName, "NULL", $fieldsString) . " FROM `" . $this->name . "` WHERE `" . $idFieldName . "` = " . $idFieldValue;
	// 	return $this->execute($query);
	// }
}