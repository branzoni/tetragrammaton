<?php

namespace Tet;

class WhereCondition
{
	function is($key, $value)
	{
	}

	function and($key, $value)
	{
	}

	function or($key, $value)
	{
	}
}

class Query
{
	public string $tablename;
	public string $command;
	public FieldCollection $fields;
	public $where;
	public $orderBy;

	const COMMAND_SELECT = "SELECT";
	const COMMAND_INSERT = "INSERT";
	const COMMAND_UPDATE = "UPDATE";
	const COMMAND_DELETE = "DELETE";

	// function orderBy(...$values)
	// {
	// 	$this->orderBy = new Collection;
	// 	$this->orderBy->add($values);
	// }

	function __construct()
	{
		$this->fields = new FieldCollection;
	}

	function __toString(): string
	{
		return $this->getQueryString();
	}

	private function getQueryString(): string
	{

		// функция обертка для формирования строки запроса
		$query = "";

		switch ($this->command) {
			case $this::COMMAND_SELECT:
				$query = $this->getSelectQuery();
				break;
			case $this::COMMAND_INSERT:
				$query = $this->getInsertQuery();
				break;
			case $this::COMMAND_UPDATE:
				$query = $this->getUpdateQuery();
				break;
			case $this::COMMAND_DELETE:
				$query = $this->getDeleteQuery();
				break;
		}

		return $query;
	}

	private function getSelectQuery(): string
	{
		// перечисляем название поле		

		$fields_section = "";
		$this->fields->forEach(function ($key, $value, $count, $counter) use (&$fields_section) {
			$comma = $counter < $count ? ", " : "";
			if ($value != "*") $value = "`$value`";
			$fields_section = "{$fields_section}{$value}{$comma}";
		});

		$where_section = $this->getWhereSection();
		$order_section = $this->getOrderSection();

		// финальная сборка частей
		$query = $this::COMMAND_SELECT . " $fields_section FROM `{$this->tablename}` $where_section $order_section";
		$query = trim($query);
		return $query;
	}

	private function getInsertQuery(): string
	{
		$fields_section = "";
		$values_section = "";
		$this->fields->forEach(function ($key, $value, $count, $counter) use (&$fields_section, &$values_section) {


			$comma = $counter < $count ? ", " : "";
			$fields_section = "{$fields_section} `$key`{$comma}";

			$quote = $this->getQuote($value);
			$value = $this->escapeString($value);
			$values_section = "{$values_section}{$quote}{$value}{$quote}{$comma}";
		});

		return $this::COMMAND_INSERT . " INTO `{$this->tablename}` ($fields_section) VALUES ($values_section)";
	}

	private function getUpdateQuery(): string
	{
		$fields_section = "";
		$this->fields->forEach(function ($key, $value, $count, $counter) use (&$fields_section) {

			$comma = $counter < $count ? ", " : "";
			$quote = $this->getQuote($value);
			$value = $this->escapeString($value);
			$fields_section = "{$fields_section} `$key` = {$quote}{$value}{$quote}{$comma}";
		});

		$where_section = $this->getWhereSection();

		return $this::COMMAND_UPDATE . " `{$this->tablename}` SET {$fields_section} {$where_section}";
	}

	private function getDeleteQuery(): string
	{
		$where_section = $this->getWhereSection();

		return $this::COMMAND_DELETE . " FROM `{$this->tablename}` {$where_section}";
	}

	private function getWhereSection(): String
	{
		if ($this->where) return "WHERE " . $this->escapeString($this->where);
		else return "";
	}

	private function getOrderSection(): string
	{
		switch(gettype($this->orderBy))
		{
			case "string":
				$arr = explode(",", $this->orderBy);			
				$this->orderBy = (new Collection);
				$this->orderBy->add($arr);
				break;
			case "array":
				$tmp = $this->orderBy;
				$this->orderBy = (new Collection);
				$this->orderBy->add($tmp);
				break;
		}
		
		if (!$this->orderBy) return "";
		if (!$this->orderBy->getCount()) return "";

		$order_section = "";
		$this->orderBy->forEach(function ($key, $value, $count, $counter) use (&$order_section) {
			$comma = $counter < $count ? ", " : "";
			if (strpos($value, "DESC") !== false) {
				$value = str_replace("DESC", "", $value);
				$value = trim($value);
				$value = "`$value` DESC";
			} else {
				$value = "`$value`";
			}

			$order_section = "{$order_section} {$value}{$comma}";
		});

		$order_section = str_replace("``", "`", $order_section);

		return " ORDER BY {$order_section}";
	}

	private function escapeString(string $string): string
	{
		return $string;
		//return mysqli_real_escape_string($this->connection, $string);
	}

	private function getQuote($value)
	{

		switch (gettype($value)) {
			case 'string':
				$quote = "'";
				break;
			default:
				$quote = "";
		}

		return $quote;
	}
}
