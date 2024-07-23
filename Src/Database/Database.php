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

    function setCharset(string $charset = "utf8"): void
    {
        $this->execute("ALTER DATABASE DATABASE() charset=$charset;");
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

    function createTable(TableDef $tableDef): void
    {
        $this->execute($tableDef);
    }

    function dropTable(string $name): void
    {
        $this->execute("DROP TABLE $name");
    }

    function modifyTable(string $name, TableDef $tableDef): void
    {
        $table = $this->getTable($name);
        $table->deleteOutSchemaColumns($tableDef);//
		$table->addColumnsFromSchema($tableDef);
		$table->addUniqueIndexesFromSchema($tableDef);
    }

    function createTablesFromSchema(DatabaseDef $databaseDef): void
    {
        $databaseDef->getTables()->forEach(function ($key, TableDef $newTable) {
            if ($this->hasTable($newTable->name)) $this->modifyTable($newTable->name, $newTable);
            else $this->createTable($newTable);
        });
    }

    function deleteOutSchemaTables(DatabaseDef $databaseDef): void
    {        
        // удаляем таблицы, которых нет в структуре
        $newTables = $databaseDef->getTables()->getKeys();
        $curTables = $this->getTables()->getKeys();
        $outSchemaTables = array_diff($curTables, $newTables);
        foreach ($outSchemaTables as $key => $table) {            
            $this->dropTable($table);
        }
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

    function getSchemaAsCode($destination = "./", $namespace = ""): void
    {
        $structure = [
            "name" => $this->getName(),
            "tables" => $this->getTables()
        ];
        $scheme = new DatabaseScheme($this, $this->mySQL);        
        $scheme->createCode($structure, $destination, $namespace);
    }
}
