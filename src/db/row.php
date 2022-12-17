<?php

namespace Tetra;

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

	function set_all($value)
	{
		$this->fields = $value;
	}

	function get_all()
	{
		return $this->fields;
	}

	function get_all_count()
	{
		return count($this->fields);
	}

	function add($value)
	{
		foreach ($value as $key=> $value) {
			$this->fields[$key] = $value;
		}
	}
}