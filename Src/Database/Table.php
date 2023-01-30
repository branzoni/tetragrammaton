<?php

namespace Tet;

class Table extends TableEntity
{
    public static MySQL $mySQL;

    function __construct($tablename, MySQL $mySQL)
    {
        $this::$mySQL = $mySQL;
		$this::$tablename = $tablename;
    }
}