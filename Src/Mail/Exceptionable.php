<?php

namespace Tet;

trait Exceptionable
{
	public static function exception($message)
	{
		throw new \Exception(static::class . " Object: $message");
	}
}
