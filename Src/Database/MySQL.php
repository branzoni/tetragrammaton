<?php

namespace Tet\Database;
use Tet\Common\Result;
use Tet\Common\Collection;
use Tet\Common\CodeGenerator;
use Tet\Common\Fasade;
use Tet\Filesystem\Filesystem;

class MySQL
{
    private $connection;
    private $name;
    
    function open(string $hostname, string $name, string $user, string $password, string $charset = "utf8"): bool
    {
        $this->name = $name;
        $this->connection = mysqli_connect($hostname, $user, $password, $name);
        if (!$this->connection) return null;
        mysqli_set_charset($this->connection, $charset);
        return boolval($this->connection);
    }

    function close(): bool
    {
        return mysqli_close($this->connection);
    }

    private function escapeString(string $string): string
    {
        return mysqli_real_escape_string($this->connection, $string);
    }

    function execute(string $query): Result
    {
        $result = new Result;
        $data = mysqli_query($this->connection, $query);
        $result->error = (mysqli_error($this->connection) != "");
        $result->description =  mysqli_error($this->connection);
        if (!$data) return $result;
        $result->data = mysqli_fetch_all($data,  MYSQLI_ASSOC);;
        return $result;
    }

    function isConnected(): bool
    {
        return boolval($this->connection);
    }

    function getError(): string
    {
        return mysqli_error($this->connection);
    }

    function getTableList(): array
    {
        //select * from information_schema.TABLES WHERE TABLE_NAME='oc_order
        $result = $this->execute("SHOW TABLES")->data;
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
        $data = $this->execute("DESCRIBE " . $tablename)->data;
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

    function createTableClass(string $destination, string $tablename): bool
    {
        $ts = $this->getTableFieldList($tablename);

        $destination = "$destination/{$this->name}/Tables";
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

    function createDatabaseClass(string $destination, string $name, array $enums): bool
    {

        $destination = "$destination/{$this->name}";
        (new Filesystem)->createDirectory($destination);

        $cg = new CodeGenerator();
        $cg->open("$destination/{$this->name}.php");
        $cg->startTag();
        $cg->line("");
        foreach ($enums as $value) {
            $cg->line("include(\"Tables/$value.php\");", 0);
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


    function pullDbScheme($destination): bool
    {

        $tables = $this->getTableList();

        $this->createDatabaseClass($destination, $this->name, $tables);

        foreach ($tables as $table) {
            $this->createTableClass($destination, $table);
        }

        return true;
    }

    function pushDbScheme($source): bool
    {
        return true;
    }

    function getDatabaseStructure(Fasade $fasade)
    {        
        $tables = $this->getTableList();

        $result = [];
        foreach ($tables as $table) {
            $fields = $this->getTableFieldList($table)->toArray();
            foreach ($fields as $field) {
                $result[$table][] = $field->name;
            }
        }

        $fasade->filesystem()->createFile("db_structure.json", json_encode($result));

        return "ok";
    }
}
