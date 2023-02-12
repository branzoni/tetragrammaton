<?php

namespace Tet\Database\MySQL;

class TableDef
{
    public string $name;
    public string $charset;
    public Fields $fields = [];
    public string $primary_key;

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

        $tmp = $this->fields->toArray();
        $tmp[$name] = $field;
        $this->fields = new Fields($tmp);
        return true;
    }
}
