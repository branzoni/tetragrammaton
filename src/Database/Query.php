<?php

namespace Tet;

use Tet\Table;

class Query
{
	private $table;

	function __construct(Table $table)
	{
		$this->table = $table;
	}

	function buildQuery(): String
	{
		// функция обертка для формирования строки запроса

		$query = "";

		switch ($this->table->command) {
			case "delete":
				$query = $this->buildDeleteQuery();
				break;
			case "select":
				$query = $this->buildSelectQuery();
				break;
			case 'insert':
				$query = $this->buildInsertQuery();
				break;
			case 'update':
				$query = $this->buildUpdateQuery();
				break;
		}

		return $query;
	}

	private function buildDeleteQuery(): String
	{
		$where_section = $this->getWhereSection();

		return "DELETE FROM `{$this->table->name}` $where_section";
	}

	private function buildSelectQuery(): String
	{
		// перечисляем название поле		

		$fields_section = $this->getFieldsSection();
		$where_section = $this->getWhereSection();
		$order_section = $this->getOrderSection();

		// финальная сборка частей
		$query = "SELECT $fields_section FROM `{$this->table->name}` $where_section $order_section";
		$query = trim($query);
		return $query;
	}

	private function buildInsertQuery(): String
	{


		$fields = $this->table->fields->getFields();
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

			$values_string .= $quote . $this->escapeString($value) . $quote;
			if ($n < $fields_count) $values_string .= ", ";
		}

		// финальная сборка частей
		$query = "INSERT INTO `{$this->table->name}` ($fields_string) VALUES ($values_string)";
		return $query;
	}

	private function buildUpdateQuery(): String
	{
		$fields_section = $this->getUpdateSection();
		$where_section = $this->getWhereSection();

		return "UPDATE `{$this->table->name}` SET $fields_section $where_section";
	}

	private function getFieldsSection(): String
	{
		$tmp = $this->table->fields->getFields();
		$tmp =  "`" . implode('`, `', $tmp) . "`";
		$tmp = str_replace("`*`", "*", $tmp);
		return $tmp;
	}


	function getUpdateSection(){
		$fields = $this->table->fields->getFields();
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

			$fields_section .= "`$field_name` = " . $quote . $this->escapeString($value) . $quote;
			if ($n < $fields_count) $fields_section .= ", ";
		}

		return $fields_section;

	}

	private function getWhereSection(): String
	{
		if ($this->table->where) return "WHERE " . $this->escapeString($this->table->where);
		else return "";
	}

	private function getOrderSection(): String
	{
		if ($this->table->order) return " ORDER BY {$this->table->order}";
		else return "";
	}

	private function escapeString($string): String
	{
		return mysqli_real_escape_string($this->table->connection, $string);
	}
}
