<?php

namespace Tet\Database;

use Exception;

class MySQL
{
    private $connection;
    public $name;

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

    function selectDatabase(string $database): bool
    {
        return mysqli_select_db($this->connection, $database);
    }

    private function escapeString(string $string): string
    {
        return mysqli_real_escape_string($this->connection, $string);
    }

    function execute(string $query)
    {
        $result = mysqli_query($this->connection, $query);
        if ($result === false) throw new Exception($this->getError());
        if ($result === true) return true;
        return mysqli_fetch_all($result,  MYSQLI_ASSOC);
    }

    function isConnected(): bool
    {
        return boolval($this->connection);
    }

    function getError(): string
    {
        return mysqli_error($this->connection);
    }

    function createDatabase(string $name): bool
    {
        return $this->execute("CREATE DATABASE IF NOT EXISTS $name");
    }

    function setTableCharset(string $database, string $tablename, string $charset = "utf8", string $collate = "utf8_general_ci"): bool
    {
        return $this->execute("ALTER TABLE `$database`.`$tablename` CONVERT TO CHARACTER SET $charset COLLATE  $collate;");
    }

    function indexIsExists(string $database, string $tablename, string $index)
    {
        return $this->execute("SELECT COUNT(1) res FROM INFORMATION_SCHEMA.STATISTICS WHERE table_schema = '$database' AND table_name='$tablename' AND index_name='$index';");
    }
}
