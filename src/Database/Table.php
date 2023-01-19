<?php

// класс для работы с таблицей

namespace Tet;

use Tet\Query;

class Table
{
	public $name = false;
	public $command = false;
	public $fields;
	public $where = false;
	public $order = false;
	public $connection;

	function __construct()
	{
		$this->fields = new Row;
	}


	function getFieldNames()
	{
		$tmp = $this->execute("DESCRIBE " . $this->name, MYSQLI_ASSOC);
		$tmp = $tmp->data;
		$tmp = array_column((array) $tmp, "Field");
		return $tmp;
	}

	function getQuery(): String
	{
		return (new Query($this))->buildQuery();
	}

	function duplicateRecord($idFieldName, $idFieldValue): Result
	{

		$fieldsString = implode(", ", $this->getFieldNames());

		$query = "INSERT INTO `" . $this->name . "` ($fieldsString) SELECT " . str_replace($idFieldName, "NULL", $fieldsString) . " FROM `" . $this->name . "` WHERE `" . $idFieldName . "` = " . $idFieldValue;

		return $this->execute($query);
	}

	function insert(): Result
	{
		$this->command = "insert";
		$query = (new Query($this))->buildQuery();
		return $this->execute($query);
	}

	function update(): Result
	{
		$this->command = "update";
		$query = (new Query($this))->buildQuery();
		return $this->execute($query);
	}

	function delete(): Result
	{
		$this->command = "delete";
		$query = (new Query($this))->buildQuery();
		return $this->execute($query);
	}

	function select(): Result
	{
		$this->command = "select";
		$query = (new Query($this))->buildQuery();
		return $this->execute($query, MYSQLI_ASSOC);
	}

	function execute(string $query, int $mode = MYSQLI_NUM): Result
	{
		$result = new Result;
		$result->query =  $query;
		$result->method = __METHOD__;

		$query_result = mysqli_query($this->connection, $query);
		switch (gettype($query_result)) {
			case "object":
				$query_result = mysqli_fetch_all($query_result, $mode); //MYSQLI_ASSOC
				break;
		}

		$error = (mysqli_error($this->connection) != "");
		$result->error = $error ? true : false;
		$result->result = !$error ? true : false;
		$result->description =  mysqli_error($this->connection);
		$result->data = $query;
		$result->data = $query_result;
		return $result;
	}
}