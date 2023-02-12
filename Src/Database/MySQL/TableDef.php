<?php

namespace Tet\Database\MySQL;

use Tet\Common\Collection;

class TableDef
{
    public string $name;
    public string $charset;
    public Fields $fields = [];
    public string $primary_key;
    //public array $indexes = [];

    function __construct(string $name, string $primary_key = "", $charset = "utf8")
    {
        $this->charset = $charset;        
        $this->name = $name;
        $this->primary_key = $primary_key;
        $this->charset = $charset;
    }

    function addField(string $name, string $type, bool $notNull = false, $default = null, bool $autoIncriment = false, string $comment = ""):bool
    {
        $field = new FieldDef;        
        $field->$name = $name;
        $field->type = $type;
        $field->notNull = $notNull;
        $field->default = $default;
        $field->autoIncriment = $autoIncriment;
        $field->comment = $comment;
        $this->fields->set($name, $field);

        return true;
    }
}
