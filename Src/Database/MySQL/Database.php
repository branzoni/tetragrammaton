<?php

namespace Tet\Database\MySQL;


use Tet\Database\MySQL;
use Tet\Database\MySQL\TableDef;

class Database
{
    private MySQL $mySQL;
    private ?string $name = null;

    function __construct(MySQL $mySQL)
    {
        $this->mySQL = $mySQL;
    }

    function getName()
    {   
        if(!$this->name){
            $data = $this->mySQL->execute("SELECT DATABASE() as name");        
            $this->name= $data[0]["name"];        
        }        

        return $this->name;        
    }

    function setCharset(string $charset = "utf8"): bool
    {
        return $this->mySQL->execute("ALTER DATABASE DATABASE() charset=$charset;");
    }

    function getTable(string $name):Table
    {
        return new Table($this->mySQL, $name);
    }

    function getTables(): TableCollection
    {
        //select * from information_schema.TABLES WHERE TABLE_NAME='oc_order
        $query = "select  TABLE_NAME as name, TABLE_COLLATION as collation, AUTO_INCREMENT as auto_incriment from information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE();";
        $result = $this->mySQL->execute($query);
        if (!$result) return null;        
        $result = array_column($result, "name");     
        
        $tables = [];
        foreach ($result as $key => $tablename) {            
            $tables[$tablename] = $this->getTable($tablename);
        }
        
        return new TableCollection($tables);        
    }    

    function ctreateTable(TableDef $table)
    {      
        $fields_tmp = [];
        $table->fields->forEach(function($key, FieldDef $field) use(&$fields_tmp) {            
            if ($field->default === null || strtolower($field->default) == "null") {                
                $field->default = !$field->notNull ? "DEFAUL NULL" : "";
            } else {
                $field->default = (gettype($field->default) == "string") ? "'$field->default'"  : $field->default;
                $field->default = "DEFAULT $field->default";
            }

            $field->type = strtoupper($field->type);
            $field->notNull = $field->notNull ? "NOT NULL" : "";
            $field->comment = $field->comment ? "COMMENT '$field->comment'" : "";
            $field->autoIncriment = $field->autoIncriment ? "AUTO_INCREMENT" : "";

            $fields_tmp[] = "  $field->name $field->type $field->notNull $field->default $field->autoIncriment $field->comment";
        });
        
        $fields_tmp[] = $table->primary_key ? "  PRIMARY KEY ($table->primary_key)" : "";

        $query = "CREATE TABLE IF NOT EXISTS $table->name (\r\n";
        $query .= implode(",\r\n", $fields_tmp) . "\r\n";
        $query .= ")\r\n";
        $query .= "ENGINE = INNODB,\r\n";
        $query .= "ENGINE = INNODB,\r\n";
        $query .= "CHARACTER SET $table->->charset\r\n";
        
        if(!$this->mySQL->execute($query)) throw new \Exception($this->mySQL->getError());
    }

    function getScheme():DbScheme
    {
        return new DbScheme($this, $this->mySQL);
    }
}
