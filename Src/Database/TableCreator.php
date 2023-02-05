<?php

namespace Tet\Database;

use Exception;

class TableCreator
{
    private string $database;
    private string $tablename;
    private string $charset;
    private array $fields = [];
    private string $primary_key;
    private array $indexes = [];

    function __construct(string $database, string $tablename, string $primary_key = "", $charset = "utf8")
    {
        $this->charset = $charset;
        $this->database = $database;
        $this->tablename = $tablename;
        $this->primary_key = $primary_key;
        $this->charset = $charset;
    }

    function addField(string $name, string $type, bool $notNull = false, $default = null, bool $autoIncriment = false, string $comment = "")
    {
        $this->fields[] = [
            "name" => $name,
            "type" => $type,
            "notNull" => $notNull,
            "default" => $default,
            "autoIncriment" => $autoIncriment,
            "comment" => $comment,
        ];
    
    }

    function addIndex($name)
    {
        $this->indexes[] = $name;
    }

    private function ctreateTable()
    {
        $fields_tmp = [];
        foreach ($this->fields as $field) {
            $field = (object) $field;
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
        }
        
        $fields_tmp[] = $this->primary_key ? "  PRIMARY KEY ($this->primary_key)" : "";



        $query = "CREATE TABLE IF NOT EXISTS $this->database.$this->tablename (\r\n";
        $query .= implode(",\r\n", $fields_tmp) . "\r\n";
        $query .= ")\r\n";
        $query .= "ENGINE = INNODB,\r\n";
        $query .= "ENGINE = INNODB,\r\n";
        $query .= "CHARACTER SET $this->charset\r\n";
        return $query;
    }

    function create(MySQL $db):bool
    {   
        
        $query = $this->ctreateTable();
        
        if(!$db->execute($query)) throw new Exception($db->getError());
        
        foreach ($this->indexes as $index) {
            if($db->indexIsExists($this->database, $this->tablename, $index)) continue;
            $query = "ALTER TABLE $this->database.$this->tablename ADD INDEX $index($index);";
            if(!$db->execute($query))  throw new Exception($db->getError());
        }
        return true;
    }
}
