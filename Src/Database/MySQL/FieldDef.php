<?php

namespace Tet\Database\MySQL;

class FieldDef
{
    public string $name;
    public string $type;
    public bool $notNull = false;
    public ?string $default = null;
    public bool $autoIncriment = false;
    public string $comment = "";
}