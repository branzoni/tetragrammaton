<?php

namespace Tet\Database;
use Tet\Common\Collection;

class Fields extends Collection
{
	function __set(string $name, $value)
	{
		$this->set($name, $value);
	}

	function __get(string $name)
	{
		return $this->get($name);
	}
}