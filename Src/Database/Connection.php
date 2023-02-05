<?php

namespace Tet\Database;

class Connection
{
    private $connection;

    function open(string $hostname, string $name, string $user, string $password, string $charset = "utf8"): bool
    {
        //$this->name = $name;
        $this->connection = mysqli_connect($hostname, $user, $password, $name);
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

}