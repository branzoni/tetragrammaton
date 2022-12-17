<?php

namespace Tetra;

class MySQL
{
    // класс-обертка для функций mysql
    public $db_adress;
    public $db_user;
    public $db_password;
    public $db_name;
    private $connection;
    
    /**
     *Открываем подключение, подключаемся к базе 
    */
    function open()
    {       
        $this->connection = mysqli_connect($this->db_adress, $this->db_user, $this->db_password, $this->db_name);
        if (!$this->connection) return false;
        mysqli_set_charset($this->connection, "utf8");
        return $this->connection;
    }

    /**
     *Закрываем подключение 
    */
    function close()
    {
        return mysqli_close($this->connection);
    }

    /**
     *Возвращает последнюю ошибку 
    */
    function error()
    {        
        return mysqli_error($this->connection);
    }

    /**
     *Возвращает экранированную строку 
    */
    private function escape_string($string)
    {
        return mysqli_real_escape_string($this->connection, $string);
    }

    /**
     *Возвращает признак подключения 
    */
    function connected()
    {
        if (!$this->connection) return false;
        return true;
    }

    /**
     *Выполняет запрос к базе 
    */
    
    function execute($query, int $mode = MYSQLI_NUM)
    {        
        return $this->table()->execute($query, $mode);
    }

    function table($name = false)
    {
        $table = new Table();
        $table->name = $name;
        $table->connection = $this->connection;
        return $table;
    }
}
