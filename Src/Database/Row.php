<?php

namespace Tet;

class Row
{
	private $fields = [];

	function __construct()
	{
		$this->fields = [];
	}

	function __set($name, $value)
	{
		$this->fields[$name] = $value;
	}

	function __get($name)
	{
		return $this->fields[$name];
	}

	function setFields($value)
	{
		$this->fields = $value;
	}

	function getFields()
	{
		return $this->fields;
	}

	function getFieldsCount()
	{
		return count($this->fields);
	}

	function add(array $values)
	{
		foreach ($values as $key => $value) {
			$this->fields[$key] = $value;
		}
	}

	function add2(...$values)
	{
		foreach ($values as $key => $value) {
			$this->fields[$key] = $value;
		}
	}
}
