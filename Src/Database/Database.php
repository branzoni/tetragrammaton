<?php

namespace Tet\Database;

use Tet\Common\CodeGenerator;
use Tet\Common\Collection;
use Tet\Filesystem\Filesystem;

class Database
{
    private MySQL $mySQL;

    function __construct(MySQL $mySQL)
    {
        $this->mySQL = $mySQL;
    }

    function setCharset(string $database, string $charset= "utf8"):bool
    {
        return $this->mySQL->execute("ALTER DATABASE $database charset=$charset;");
    }


    function getTableList(): array
    {
        //select * from information_schema.TABLES WHERE TABLE_NAME='oc_order
        $result = $this->mySQL->execute("SHOW TABLES");
        if (!$result) return null;
        $result = array_values($result);

        $tmp = [];
        foreach ($result as $row) {
            $row = array_values($row);
            $tmp[] = $row[0];
        }
        return $tmp;
    }

    function getTableFieldList(string $tablename): Collection
    {
        //select * from information_schema.COLUMNS WHERE TABLE_NAME='oc_order

        $tbl = new Collection;
        $data = $this->mySQL->execute("DESCRIBE " . $tablename);
        if (!$data) return null;

        foreach ($data as $key => $value) {
            $tb = new Field();
            $tb->name = $value["Field"];
            $tb->type = $value["Type"];
            $tb->null = $value["Null"];
            $tb->key = $value["Key"];
            $tb->default = $value["Default"];
            $tb->extra = $value["Extra"];
            $tbl->set($key, $tb);
        }

        return $tbl;
    }

    function createDatabaseClass(string $destination, string $name, array $enums): bool
    {

        $destination = "$destination/{$this->mySQL->name}";
        (new Filesystem)->createDirectory($destination);

        $cg = new CodeGenerator();
        $cg->open("$destination/{$this->mySQL->name}.php");
        $cg->startTag();
        $cg->line("");
        foreach ($enums as $value) {
            $cg->line("require(\"Tables/$value.php\");", 0);
        }

        $cg->line("");
        $cg->line("class $name");
        $cg->line("{");
        foreach ($enums as $value) {
            $cg->line("public $value \$$value;", 1);
        }
        $cg->line("");
        $cg->line("function __construct(Tet\MySQL \$mySQL)", 1);
        $cg->line("{", 1);
        foreach ($enums as $value) {
            $cg->line("\$this->$value =  new $value(\$mySQL);", 2);
        }
        $cg->line("}", 1);
        $cg->line("}");
        $cg->close();
        return true;
    }


    function createTableClass(string $destination, string $tablename): bool
    {
        $ts = $this->getTableFieldList($tablename);
        $destination = "$destination/{$this->mySQL->name}/Tables";
        (new Filesystem)->createDirectory($destination);

        $cg = new CodeGenerator();
        $cg->open("$destination/$tablename.php");

        $cg->startTag();
        $cg->line("");
        $cg->line("class $tablename extends Tet\TableEntity");
        $cg->line("{");
        $cg->line("public static string \$tablename = '$tablename';", 1);
        $cg->line("public static Tet\MySQL \$mySQL;", 1);
        $cg->line("");
        $cg->line("const TABLE_NAME = '{$tablename}';", 1);
        foreach ($ts->toArray() as $value) {
            $propName  = "COLLUMN_NAME_" . str_replace('-', "_TET_MINUS_", $value->name);
            $propName = strtoupper($propName);
            $cg->line("const {$propName} = '{$value->name}';", 1);
        }
        $cg->line("");
        $cg->line("function __construct(Tet\MySQL \$mySQL)", 1);
        $cg->line("{", 1);
        $cg->line("\$this::\$mySQL = \$mySQL;", 2);
        $cg->line("}", 1);
        $cg->line("}");
        $cg->close();

        return true;
    }

    function pullDbScheme($destination): bool
    {

        $tables = $this->getTableList();

        $this->createDatabaseClass($destination, $this->mySQL->name, $tables);

        foreach ($tables as $table) {
            $this->createTableClass($destination, $table);
        }

        return true;
    }

    function pushDbScheme($source): bool
    {
        return true;
    }

    function getStructure(): array
    {        
        $tables = $this->getTableList();

        $result = [];
        foreach ($tables as $table) {
            $fields = $this->getTableFieldList($table)->toArray();
            foreach ($fields as $field) {
                $result[$table][] = $field->name;
            }
        }        

        return $result;
    }

    function createTable(TableCreator $tableCreator)
    {
        return $tableCreator->create($this->mySQL, $tableCreator);
    }

}