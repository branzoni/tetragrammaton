<?php

namespace Tet\Database;

use Tet\Database\MySQL;
use Tet\Database\TableCollection;
use Tet\Database\Table;
use Tet\Database\TableDef;
use Tet\Database\ColumnDef;

class Database
{
    private MySQL $mySQL;
    private ?string $name = null;

    function __construct(MySQL $mySQL)
    {
        $this->mySQL = $mySQL;
    }

    function execute(string $query)
    {
        return $this->mySQL->execute($query);
    }

    function getName(): ?string
    {
        if (!$this->name) $this->name = $this->execute("SELECT DATABASE() as name")[0]["name"];
        return $this->name;
    }

    function setCharset(string $charset = "utf8"): bool
    {
        return $this->execute("ALTER DATABASE DATABASE() charset=$charset;");
    }

    function getTableNames(): array
    {
        $data = $this->execute("SELECT  TABLE_NAME AS name FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE()");
        if (!$data) return [];
        return array_column($data, "name");
    }

    function getTables(): TableCollection
    {
        $tablenames = $this->getTableNames();
        if ($tablenames == []) return new TableCollection([]);

        $tables = [];
        foreach ($tablenames as $tablename) {
            $tables[$tablename] = $this->getTable($tablename);
        }

        return new TableCollection($tables);
    }

    function getTable(string $name): Table
    {
        return new Table($this->mySQL, $name);
    }

    function hasTable(string $name): bool
    {
        return $this->getTables()->has(($name));
    }

    function createTable(TableDef $tableDef): bool
    {
        return $this->execute($tableDef);
    }

    function dropTable(string $name): bool
    {
        return $this->execute("DROP TABLE $name");
    }

    function modifyTable(string $name, TableDef $tableDef): bool
    {
        $table = $this->getTable($name);   
        $table->addColumnsFromSchema($tableDef);
        $table->deleteOutSchemaColumns($tableDef);

        return true;
    }

    function createTablesFromSchema(DatabaseDef $databaseDef):bool
    {
        $databaseDef->getTables()->forEach(function ($key, TableDef $newTable) {
            if ($this->hasTable($newTable->name)) $this->modifyTable($newTable->name, $newTable);
            else $this->createTable($newTable);
        });

        return true;
    }

    function deleteOutSchemaTables(DatabaseDef $databaseDef):bool
    {        
        // удаляем таблицы, которых нет в структуре
        $newTables = $databaseDef->getTables()->getKeys();
        $curTables = $this->getTables()->getKeys();
        $outSchemaTables = array_diff($curTables, $newTables);
        foreach ($outSchemaTables as $key => $table) {            
            $this->dropTable($table);
        }    
        return true;
    }

    function getSchemaAsArray(): array
    {

        $tables = $this->getTables();
        $result = [];
        $result["name"] = $this->getName();
        $tables->forEach(function ($key, Table $table) use (&$result) {
            $result["tables"][$key] = $table->getColumnsInfo()->getKeys();
        });

        return $result;
    }

    function getSchemaAsJSON(): string
    {
        return json_encode($this->getSchemaAsArray());
    }

    function getSchemaAsCode($destination = "./", $namespace = ""): bool
    {
        $structure = [
            "name" => $this->getName(),
            "tables" => $this->getTables()
        ];
        $scheme = new DatabaseScheme($this, $this->mySQL);        
        return $scheme->createCode($structure, $destination, $namespace);
    }
}
