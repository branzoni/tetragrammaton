<?php

namespace Tet\Database;

use Exception;
use Tet\Database\Database;
use Tet\Database\DatabaseDef;
use Tet\Database\Query;
use Tet\Database\TypesDef;

class MySQL
{
    public $name;
    private \mysqli $connection;

    function open(string $hostname,  string $database, string $user, string $password, string $charset = "utf8"): bool
    {
        $this->connection = mysqli_connect($hostname, $user, $password, $database);
        if (!$this->connection) return false;
        $this->setCharset($charset);

        return boolval($this->connection);
    }

    function close(): bool
    {
        return mysqli_close($this->connection);
    }

    /**
     * @throws Exception
     */
    function execute(string $query)
    {
        //print_r($query . "\r\n");
        $result = mysqli_query($this->connection, $query);
        if ($result === false) throw new Exception($this->getError());
        if ($result === true) return true;
        return mysqli_fetch_all($result,  MYSQLI_ASSOC);
    }


    function getRecord(string $query, int $index = 0): ?object
    {
        $result = $this->execute($query);
        if (!$result) return null;
        $result =  $result[$index];
        $result = (object)  $result;
        return  $result;
    }

    function  getLastInsertId()
    {
        return mysqli_insert_id($this->connection);
    }

    function getError(): string
    {
        return mysqli_error($this->connection);
    }

    function isConnected(): bool
    {
        return boolval($this->connection);
    }

    function setCharset(string $charset = "utf8"): bool
    {
        return mysqli_set_charset($this->connection, $charset);
    }

    function createDatabase(string $name): bool
    {
        return $this->execute("CREATE DATABASE IF NOT EXISTS $name");
    }

    function selectDatabase(string $database): bool
    {
        return mysqli_select_db($this->connection, $database);
    }

    function getCurrentDb(): Database
    {
        return new Database($this);
    }

    function createDatabase2(string $name): Database
    {
        $this->createDatabase($name);
        $this->selectDatabase($name);
        return $this->getCurrentDb();
    }

    function createDatabaseFromSchema(DatabaseDef $databaseDef): bool
    {
        // создаем структуру базы        
        $db = $this->createDatabase2($databaseDef->name);
        $db->createTablesFromSchema($databaseDef);
        $db->deleteOutSchemaTables($databaseDef);
        return true;
    }

    function modifyDatabaseFromSchema(DatabaseDef $databaseDef): bool
    {
        // создаем структуру базы
        $db = $this->createDatabase2($databaseDef->name);
        $db->createTablesFromSchema($databaseDef);
        //$db->deleteOutSchemaTables($databaseDef);
        return true;
    }

    function getQuery(): Query
    {
        return new  Query($this->connection);
    }

    static function types(): TypesDef
    {
        return  new TypesDef;
    }

    function escapeString(string ...$strings)
    {
        if (count($strings) == 1) return $this->connection->escape_string($strings[0]);

        $results = [];
        foreach ($strings as $string) {
            $results[] = $this->connection->escape_string($string);
        }

        return $results;
    }
}
