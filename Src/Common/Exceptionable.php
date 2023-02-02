<?php

namespace Tet;

trait Exceptionable
{
	protected string $err;
	
	public static function exception($err)
	{
		throw new \Exception(static::class . " Object: $err");
	}
}
