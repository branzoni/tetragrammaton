<?php

namespace Tet\Database\MySQL;

trait Helpers
{
    function getAsFieldDef(FieldDef $field):FieldDef
    {
        return $field;
    }

    function getAsTable(Table $table):Table
    {
        return $table;
    }
}
