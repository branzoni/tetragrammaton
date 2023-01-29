<?php

namespace Tet;

use Tet\Query;


class Table extends TableEntity
{
    public static MySQL $mySQL;

    function __construct($tablename, MySQL $mySQL)
    {
        $this::$mySQL = $mySQL;
		$this::$tablename = $tablename;
    }
}