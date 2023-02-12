<?php

namespace Tet\Database;

use Exception;
use Tet\Database\MySQL\Database;
use Tet\Database\MySQL\Query;

class MySQL
{
    public $name;
    private $connection;

    function open(string $hostname,  string $database, string $user, string $password, string $charset = "utf8"): bool
    {
        $this->connection = mysqli_connect($hostname, $user, $password, $database);
        if (!$this->connection) return null;
        $this->setCharset($charset);

        return boolval($this->connection);
    }

    function close(): bool
    {
        return mysqli_close($this->connection);
    }

    function isConnected(): bool
    {
        return boolval($this->connection);
    }

    function setCharset(string $charset = "utf8"):bool
    {
        return mysqli_set_charset($this->connection, $charset);
    }

    function getError(): string
    {
        return mysqli_error($this->connection);
    }    

    function execute(string $query)
    {
        $result = mysqli_query($this->connection, $query);
        if ($result === false) throw new Exception($this->getError());
        if ($result === true) return true;
        return mysqli_fetch_all($result,  MYSQLI_ASSOC);
    }
    
    function getCurrentDb():Database
    {
        return new Database($this);
    }

    function selectDB(string $database): bool
    {
        return mysqli_select_db($this->connection, $database);
    }

    function createDB(string $name): bool
    {
        return $this->execute("CREATE DATABASE IF NOT EXISTS $name");
    }

   function getQuery():Query
   {
     return new  Query($this->connection);
   }
}