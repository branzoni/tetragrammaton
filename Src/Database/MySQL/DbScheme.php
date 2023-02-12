<?php

namespace Tet\Database\MySQL;

use Tet\Common\CodeGenerator;
use Tet\Filesystem\Filesystem;
use Tet\Database\MySQL\Table;

class DbScheme
{
    private Database $db;

    function __construct(Database $db)
    {
        $this->db = $db;
    }

    function saveAsClass($destination = "./", $namespace = ""): bool
    {

        $dbname =  $this->db->getName();
        $tables = $this->db->getTables();

        if($namespace !="") $namespace = $namespace . "\\";

        (new Filesystem)->createDirectory("$destination/$dbname");
        (new Filesystem)->createDirectory("$destination/$dbname/Tables");
        (new Filesystem)->createDirectory("$destination/$dbname/RowCollections");

        $this->createDatabaseClass($destination, $namespace, $dbname, $tables);

        $tables->forEach(function ($tablename, Table $table) use ($destination, $namespace, $dbname) {
            $this->createTableClass($destination, $namespace, $dbname, $table);
            $this->createRowClass($destination, $namespace, $dbname, $table);
            $this->createRowCollectionClass($destination, $namespace, $dbname, $table);
            return true;
        });


        return true;
    }

    function toArray(): array
    {
        $tables = $this->db->getTables();

        $result = [];
        $tables->forEach(function ($key, Table $table) use (&$result) {
            $result[$key] = $table->getFields()->getKeys();
        });

        return $result;
    }

    private function createDatabaseClass(string $destination, $namespace, string $dbname, TableCollection $tables): bool
    {
        $cg = new CodeGenerator();
        $cg->open("$destination/$dbname/$dbname.php");
        $cg->startTag();
        $cg->line("");
        $cg->line("namespace $namespace$dbname;");
        $cg->line("");
        $cg->line("use Tet\Database\MySQL;");
        $cg->line("");
        $tables->forEach(function ($key, Table $table) use ($cg, $namespace, $dbname) {
            $cg->line("use $namespace$dbname\Tables\\" . $table->getName() . ";");
        });
        $cg->line("");
        $cg->line("class $dbname");
        $cg->line("{");
        $tables->forEach(function ($key, Table $table) use ($cg) {
            $cg->line("public {$table->getName()} \${$table->getName()};", 1);
        });
        $cg->line("");
        $cg->line("function __construct(MySQL \$mySQL)", 1);
        $cg->line("{", 1);
        $tables->forEach(function ($key, Table $table) use ($cg) {
            $cg->line("\$this->{$table->getName()} =  new {$table->getName()}(\$mySQL);", 2);
        });
        $cg->line("}", 1);
        $cg->line("}");
        $cg->close();
        return true;
    }


    private function createRowClass(string $destination, $namespace, $dbname, Table $table): bool
    {
        (new Filesystem)->createDirectory("$destination/$dbname/Rows");

        $tablename = $table->getName();

        $cg = new CodeGenerator();
        $cg->open("$destination/$dbname/Rows/$tablename.php");
        $cg->startTag();
        $cg->line("");
        $cg->line("namespace $namespace$dbname\Rows;");
        $cg->line("");
        $cg->line("class {$tablename}");
        $cg->line("{");
        $table->getFields()->forEach(function ($fieldname, FieldDef $field) use ($cg) {
            $propName  = str_replace('-', "_tet_minus_", $field->name);
            $cg->line("public \$$propName;", 1);
        });

        $cg->line("");
        $cg->line("function __construct(\$row)", 1);
        $cg->line("{", 1);
        $cg->line("foreach (\$row as \$field_name => \$field_value) {", 2);
        $cg->line("\$this->\$field_name = \$field_value;", 3);
        $cg->line("}", 2);
        $cg->line("}", 1);

        $cg->line("}");
        $cg->close();
        return true;
    }

    private function createRowCollectionClass(string $destination, $namespace, $dbname, Table $table): bool
    {
        $tablename = $table->getName();

        $cg = new CodeGenerator();
        $cg->open("$destination/$dbname/RowCollections/$tablename.php");

        $cg->startTag();
        $cg->line("");
        $cg->line("namespace $namespace$dbname\RowCollections;");
        $cg->line("");
        $cg->line("use Tet\Common\CollectionReadOnly;");
        $cg->line("use {$namespace}snab_market_new\Rows\\$tablename as {$tablename}_row;");
        $cg->line("");

        $cg->line("class $tablename extends CollectionReadOnly");
        $cg->line("{");
        $cg->line("function get(string \$name):{$tablename}_row", 1);
        $cg->line("{", 1);
        //$cg->line("return \$this->values[\$name];", 2);
        $cg->line("return new {$tablename}_row(\$this->values[\$name]);", 2);        
        $cg->line("}", 1);
        $cg->line("}");
        $cg->close();
        return true;
    }

    private function createTableClass(string $destination, $namespace, $dbname, Table $table): bool
    {
        $tablename = $table->getName();

        $cg = new CodeGenerator();
        $cg->open("$destination/$dbname/Tables/$tablename.php");

        $cg->startTag();
        $cg->line("");
        $cg->line("namespace $namespace$dbname\Tables;");
        $cg->line("");
        $cg->line("use Tet\Database\TableEntity;");
        $cg->line("use Tet\Database\MySQL;");
        $cg->line("use {$namespace}snab_market_new\Rows\\$tablename as {$tablename}_row;");
        $cg->line("use {$namespace}snab_market_new\RowCollections\\$tablename as {$tablename}_row_collection;");


        $cg->line("");
        $cg->line("class $tablename extends TableEntity");
        $cg->line("{");
        $cg->line("public static string \$tablename = '$tablename';", 1);
        $cg->line("public static MySQL \$mySQL;", 1);
        $cg->line("");
        $cg->line("const TABLE_NAME = '{$tablename}';", 1);

        $table_field_list = $table->getFields()->toArray();

        $table->getFields()->forEach(function ($fieldname, FieldDef $field) use ($cg) {
            $propName  = "COLNAME_" . str_replace('-', "_TET_MINUS_", $field->name);
            $propName = strtoupper($propName);
            $cg->line("const {$propName} = '{$field->name}';", 1);
        });

        foreach ($table_field_list as $value) {
        }
        $cg->line("");
        $cg->line("function __construct(MySQL \$mySQL)", 1);
        $cg->line("{", 1);
        $cg->line("\$this::\$mySQL = \$mySQL;", 2);
        $cg->line("}", 1);

        $cg->line("");
        $cg->line("private function getRowsArray(\$rows): array", 1);
        $cg->line("{", 1);
        $cg->line("\$result = [];", 2);
        $cg->line("foreach (\$rows as \$row) \$result[] = new {$tablename}_row(\$row);", 2);
        $cg->line("return \$result;", 2);
        $cg->line("}", 1);
        
        $cg->line("");
        $cg->line("function getRows(): ?{$tablename}_row_collection", 1);
        $cg->line("{", 1);
        $cg->line("\$tmp = \$this->execute();", 2);
        $cg->line("\$tmp = \$this->getRowsArray(\$tmp);", 2);
        $cg->line("return  new {$tablename}_row_collection(\$tmp);", 2);
        $cg->line("}", 1);
        $cg->line("}");

        $cg->close();
        return true;
    }
}
