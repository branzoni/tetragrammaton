<?php

namespace Tet\Database\MySQL;


use Tet\Database\MySQL;
use Tet\Database\MySQL\FieldDef;

use Tet\Database\MySQL\Fields;

class Table
{
    private MySQL $mySQL;
    private string $name;

    function __construct(MySQL $mySQL, string $name)
    {
        $this->mySQL = $mySQL;
        $this->name = $name;
    }

    function getName()
    {   
        return $this->name;        
    }

    function getFields(): Fields
    {
        //select * from information_schema.COLUMNS WHERE TABLE_NAME='oc_order

        $data = $this->mySQL->execute("DESCRIBE $this->name");
        if (!$data) return null;

        $fields = [];
        foreach ($data as $key => $value) {
            $field = new FieldDef;
            $field->name = $value["Field"];
            $field->type = $value["Type"];
            $field->notNull = ($value["Null"] == "NO");
            //$tb-> = $value["Key"];
            $field->default = $value["Default"];
            $field->autoIncriment = ($value["Extra"] == "auto_increment");
            
            $fields[$field->name] = $field;
        }

        return new Fields($fields);
    }

    function setCharset(string $charset = "utf8", string $collate = "utf8_general_ci"): bool
    {
        return $this->mySQL->execute("ALTER TABLE `$this->name` CONVERT TO CHARACTER SET $charset COLLATE  $collate;");
    }

    function  hasIndex(string $index)
    {
        return $this->mySQL->execute("SELECT COUNT(1) res FROM INFORMATION_SCHEMA.STATISTICS WHERE table_schema = DATABASE() AND table_name='$this->name' AND index_name='$index';");
    }

    function createIndex(string $index): bool
    {
        if ($this->hasIndex($index)) return true;
        if (!$this->mySQL->execute("ALTER TABLE $this->name ADD INDEX $index($index);"))  throw new \Exception($this->mySQL->getError());
        return true;
    }

    function duplicateRow(string $idFieldName, string $idFieldValue)
	{
		$fieldsString = implode(", ", $this->getFields()->getKeys());
		$query = "INSERT INTO `$this->name` ($fieldsString) SELECT " . str_replace($idFieldName, "NULL", $fieldsString) . " FROM `$this->name` WHERE `$idFieldName` = $idFieldValue";
		return $this->mySQL->execute($query);
	}
}