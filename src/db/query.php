<?php

namespace Tetra;

use Tetra\Table;

class Query
{
	private $table;

	function __construct(Table $table)
	{
		$this->table = $table;
	}

	function build_query(): String
	{
		// функция обертка для формирования строки запроса

		$query = "";

		switch ($this->table->command) {
			case "delete":
				$query = $this->build_delete_query();
				break;
			case "select":
				$query = $this->build_select_query();
				break;
			case 'insert':
				$query = $this->build_insert_query();
				break;
			case 'update':
				$query = $this->build_update_query();
				break;
		}

		return $query;
	}

	private function build_delete_query(): String
	{
		$where_section = $this->get_where_section();

		return "DELETE FROM `{$this->table->name}` $where_section";
	}

	private function build_select_query(): String
	{
		// перечисляем название поле		

		$fields_section = $this->get_fields_section();
		$where_section = $this->get_where_section();
		$order_section = $this->get_order_section();

		// финальная сборка частей
		$query = "SELECT $fields_section FROM `{$this->table->name}` $where_section $order_section";
		$query = trim($query);
		return $query;
	}

	private function build_insert_query(): String
	{


		$fields = $this->table->fields->get_all();
		$fields_count = count($fields);
		$fields_string = "";

		$n = 0;
		// перебираем поля
		foreach ($fields as $key => $value) {
			$n = $n + 1;

			$key = str_replace("__STRING", "|STRING", $key);
			$key_arr = explode('|', $key);
			$field_name = $key_arr[0];
			$fields_string .= "`" . $field_name . "`";
			if ($n < $fields_count) $fields_string .= ", ";
		}

		$values_string = "";
		$n = 0;

		// перебираем поля
		foreach ($fields as $key => $value) {
			$n = $n + 1;
			$key = str_replace("__STRING", "|STRING", $key);
			$key_arr = explode('|', $key);
			if (!isset($key_arr[1])) {
				$quote = "";
			} else {
				// указан тип поля, определяем необходимость кавычек
				switch ($key_arr[1]) {
					case "STRING":
						$quote = "'";
						break;
					default:
						$quote = "";
				}
			}

			$values_string .= $quote . $this->escape_string($value) . $quote;
			if ($n < $fields_count) $values_string .= ", ";
		}

		// финальная сборка частей
		$query = "INSERT INTO `{$this->table->name}` ($fields_string) VALUES ($values_string)";
		return $query;
	}

	private function build_update_query(): String
	{
		$fields_section = $this->get_update_section();
		$where_section = $this->get_where_section();

		return "UPDATE `{$this->table->name}` SET $fields_section $where_section";
	}

	private function get_fields_section(): String
	{
		$tmp = $this->table->fields->get_all();
		$tmp =  "`" . implode('`, `', $tmp) . "`";
		$tmp = str_replace("`*`", "*", $tmp);
		return $tmp;
	}


	function get_update_section(){
		$fields = $this->table->fields->get_all();
		$fields_count = count($fields);	
		$fields_section = "";

		$n = 0;

		foreach ($fields as $key => $value) {
			$n = $n + 1;
			$key = str_replace("__STRING", "|STRING", $key);
			$key_arr = explode('|', $key);
			$field_name = $key_arr[0];
			if (!isset($key_arr[1])) {
				$quote = "";
			} else {
				// указан тип поля, определяем необходимость кавычек
				switch ($key_arr[1]) {
					case "STRING":
						$quote = "'";
						break;
					default:
						$quote = "";
				}
			}

			$fields_section .= "`$field_name` = " . $quote . $this->escape_string($value) . $quote;
			if ($n < $fields_count) $fields_section .= ", ";
		}

		return $fields_section;

	}

	private function get_where_section(): String
	{
		if ($this->table->where) return "WHERE " . $this->escape_string($this->table->where);
		else return "";
	}

	private function get_order_section(): String
	{
		if ($this->table->order) return " ORDER BY {$this->table->order}";
		else return "";
	}

	private function escape_string($string): String
	{
		return mysqli_real_escape_string($this->table->connection, $string);
	}
}
