<?php

namespace Tet\Database;

use Tet\Common\CodeGenerator;
use Tet\Filesystem\Filesystem;
use Tet\Database\Table;
use Tet\Database\ColumnDef;
use Tet\Database\TableCollection;

class DatabaseScheme
{
    function createCode($structure, $destination = "./", $namespace = ""): void
    {
        $dbname = $structure["name"];
        $tables = $structure["tables"];

        $this->createDirectoryStructure("$destination", "$dbname");

        if ($namespace != "") $namespace = $namespace . "\\";
        
        $this->createDatabaseClass($destination, $namespace, $dbname, $tables);

        $tables->forEach(function ($tablename, Table $table) use ($destination, $namespace, $dbname) {
            $this->createTableClass($destination, $namespace, $dbname, $table);
            $this->createRowClass($destination, $namespace, $dbname, $table);
            $this->createRowCollectionClass($destination, $namespace, $dbname, $table);
            return true;
        });
    }

    private function createDirectoryStructure(string $destination, string $dbname): void
    {
        Filesystem::createDirectory("$destination/$dbname");
        Filesystem::createDirectory("$destination/$dbname/Tables");
        Filesystem::createDirectory("$destination/$dbname/RowCollections");
    }

    private function createDatabaseClass(string $destination, $namespace, string $dbname, TableCollection $tables): void
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
    }


    private function createRowClass(string $destination, $namespace, $dbname, Table $table): void
    {
        Filesystem::createDirectory("$destination/$dbname/Rows");

        $tablename = $table->getName();

        $cg = new CodeGenerator();
        $cg->open("$destination/$dbname/Rows/$tablename.php");
        $cg->startTag();
        $cg->line("");
        $cg->line("namespace $namespace$dbname\Rows;");
        $cg->line("");
        $cg->line("class {$tablename}");
        $cg->line("{");
        $table->getColumnsInfo()->forEach(function ($columnname, ColumnDef $column) use ($cg) {
            $propName  = str_replace('-', "_tet_minus_", $column->getName());
            $cg->line("public \$$propName;", 1);
        });

        $cg->line("");
        $cg->line("function __construct(\$row)", 1);
        $cg->line("{", 1);
        $cg->line("foreach (\$row as \$column_name => \$column_value) {", 2);
        $cg->line("\$this->\$column_name = \$column_value;", 3);
        $cg->line("}", 2);
        $cg->line("}", 1);

        $cg->line("}");
        $cg->close();
    }

    private function createRowCollectionClass(string $destination, $namespace, $dbname, Table $table): void
    {
        $tablename = $table->getName();

        $cg = new CodeGenerator();
        $cg->open("$destination/$dbname/RowCollections/$tablename.php");
        $cg->startTag();
        $cg->line("");
        $cg->line("namespace $namespace$dbname\RowCollections;");
        $cg->line("");
        $cg->line("use Tet\Common\CollectionReadOnly;");
        $cg->line("use {$namespace}$dbname\Rows\\$tablename as {$tablename}_row;");
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
    }

    private function createTableClass(string $destination, $namespace, $dbname, Table $table): void
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
        $cg->line("use {$namespace}$dbname\Rows\\$tablename as {$tablename}_row;");
        $cg->line("use {$namespace}$dbname\RowCollections\\$tablename as {$tablename}_row_collection;");


        $cg->line("");
        $cg->line("class $tablename extends TableEntity");
        $cg->line("{");
        $cg->line("public static string \$tablename = '$tablename';", 1);
        $cg->line("public static MySQL \$mySQL;", 1);
        $cg->line("");
        $cg->line("const TABLE_NAME = '{$tablename}';", 1);

        $table_column_list = $table->getColumnsInfo()->toArray();

        $table->getColumnsInfo()->forEach(function ($columnname, ColumnDef $column) use ($cg) {
            $propName  = "COLNAME_" . str_replace('-', "_TET_MINUS_", $column->getName());
            $propName = strtoupper($propName);
            $cg->line("const {$propName} = '{$column->getName()}';", 1);
        });

        foreach ($table_column_list as $value) {
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
    }
}
