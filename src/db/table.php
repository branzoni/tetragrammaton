<?php

// класс для работы с таблицей

namespace Tetra;

use Tetra\Query;

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


	function get_field_names()
	{
		$tmp = $this->execute("DESCRIBE " . $this->name, MYSQLI_ASSOC);
		$tmp = $tmp->data;
		$tmp = array_column((array) $tmp, "Field");
		return $tmp;
	}

	function get_query(): String
	{
		return (new Query($this))->build_query();
	}

	function duplicate_record($id_field_name, $id_field_value): Result
	{
				
		$fields_string = implode(", ", $this->get_field_names());

		$query = "INSERT INTO `" . $this->name . "` ($fields_string) SELECT " . str_replace($id_field_name, "NULL", $fields_string) . " FROM `" . $this->name . "` WHERE `" . $id_field_name . "` = " . $id_field_value;

		return $this->execute($query);
	}

	function insert(): Result
	{
		$this->command = "insert";
		$query = (new Query($this))->build_query();
		return $this->execute($query);
	}

	function update(): Result
	{
		$this->command = "update";
		$query = (new Query($this))->build_query();
		return $this->execute($query);
	}

	function delete(): Result
	{
		$this->command = "delete";
		$query = (new Query($this))->build_query();
		return $this->execute($query);
	}

	function select(): Result
	{
		$this->command = "select";
		$query = (new Query($this))->build_query();
		return $this->execute($query, MYSQLI_ASSOC);
	}

	function execute($query, int $mode = MYSQLI_NUM): Result
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

	// КОНЕЦ КЛАССА
}
