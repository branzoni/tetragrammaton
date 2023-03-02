<?php

namespace Tet\Database;

class ColumnDef
{
    protected string $name;
    private TypeDef $type;
    private ?bool $notNull = null;
    private ?string $default = null;
    private bool $primarykey = false;
    private bool $autoincriment = false;
    private bool $unique = false;
    private ?string $comment = "";

    function __construct(string $name, TypeDef $type)
    {
        $this->name = $name;
        $this->type = $type;   
    }

    function getName():string
    {
        return $this->name;
    }

    function getType():TypeDef
    {
        return $this->type;
    }

    function getDefault()
    {
        return $this->default;
    }

    function isAutoincriment()
    {
        return $this->autoincriment === true;
    }

    function isUnique()
    {
        return $this->unique === true;
    }

    function hasDefault():bool
    {
        return $this->default != null;
    }


    function setPrimaryKey()
    {
        $this->primarykey = true;
        return $this;
    }

    function setAutoincriment()
    {
        $this->autoincriment = true;
        return $this;
    }

    function setNotNull()
    {
        $this->notNull = true;
        return $this;
    }

    function setUnique()
    {
        $this->unique = true;
        return $this;
    }

    function setDefault($value)
    {
        if($value == null) $value = "NULL";
        if($value === false) $value = "FALSE";
        if($value === true) $value = "TRUE";
        $this->default = $value;
        return $this;
    }

    function setComment($value)
    {
        $this->comment = $value;
        return $this;
    }

    function __toString()
    {       

        // первичные проверки
        if ($this->autoincriment)
        {
            if($this->type->getName() != "int"  && $this->type->getName() !="float") throw new \Exception("Autoincriment needs INT or FLOAT data type. {$this->type->getName()} given");
            if($this->notNull === false)  throw new \Exception("Autoincriment needs NOT NULL");
            if($this->notNull == null) $this->notNull = true;
        }

        if ($this->primarykey)
        {         
            if($this->notNull === false)  throw new \Exception("Primary key needs NOT NULL");
            if($this->notNull == null) $this->notNull = true;
        }


        $result = "";
        $result .= $this->name;
        $result .= " ";
        $result .= $this->type;
        $result .= $this->notNull ? " NOT NULL" : "";
        $result .= $this->unique ? " UNIQUE" : "";        
        $result .= $this->default != null ? " DEFAULT $this->default" : "";
        $result .= $this->autoincriment ? " AUTO_INCREMENT" : "";
        $result .= $this->primarykey ? " PRIMARY KEY" : "";        
        $result .= $this->comment ? " COMMENT '$this->comment'" : "";

        return  $result;
    }
}
