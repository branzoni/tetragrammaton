<?php

namespace Tet\Database;

use Exception;
use Tet\Database\Database;
use Tet\Database\DatabaseDef;
use Tet\Database\Query;
use Tet\Database\TypesDef;

class MySQL
{
    public static string $name;
    private static \mysqli $connection;

	static function open(string $hostname = null,  string $database = null, string $user = null, string $password = null, string $charset = "utf8"): bool
	{
		self::$connection = mysqli_connect($hostname, $user, $password, $database);
		if (!self::$connection) return false;
		self::setCharset($charset);

		return boolval(self::$connection);
	}
    static function open2(string $hostname, string $user, string $password, string $database = null, string $charset = "utf8"): bool
    {
		return self::open($hostname, $user, $password, $database, $charset);
    }

    static function close(): bool
    {
        return mysqli_close(self::$connection);
    }

    /**
     * @throws Exception
     */
    static function execute(string $query)
    {
        $result = mysqli_query(self::$connection, $query);
        if ($result === false) throw new Exception(self::getError());
        if ($result === true) return true;
        return mysqli_fetch_all($result,  MYSQLI_ASSOC);
    }


    static function getRecord(string $query, int $index = 0): ?object
    {
        $result = self::execute($query);
        if (!$result) return null;
        $result =  $result[$index];
        $result = (object)  $result;
        return  $result;
    }

    static function  getLastInsertId()
    {
        return mysqli_insert_id(self::$connection);
    }

    static function getError(): string
    {
        return mysqli_error(self::$connection);
    }

    static function isConnected(): bool
    {
        return boolval(self::$connection);
    }

    static function setCharset(string $charset = "utf8"): bool
    {
        return mysqli_set_charset(self::$connection, $charset);
    }

    static function createDatabase(string $name): bool
    {
        return self::execute("CREATE DATABASE IF NOT EXISTS $name");
    }

    static function selectDatabase(string $database): bool
    {
        return mysqli_select_db(self::$connection, $database);
    }

    static function getCurrentDb(): Database
    {
        return new Database(new self);
    }

    static function createDatabase2(string $name): Database
    {
        self::createDatabase($name);
        self::selectDatabase($name);
        return self::getCurrentDb();
    }

    static function createDatabaseFromSchema(DatabaseDef $databaseDef): bool
    {
        // создаем структуру базы        
        $db = self::createDatabase2($databaseDef->name);
        $db->createTablesFromSchema($databaseDef);
        $db->deleteOutSchemaTables($databaseDef);
        return true;
    }

    static function modifyDatabaseFromSchema(DatabaseDef $databaseDef): bool
    {
        // создаем структуру базы
        $db = self::createDatabase2($databaseDef->name);
        $db->createTablesFromSchema($databaseDef);
        return true;
    }

    static function getQuery(): Query
    {
        return new  Query(self::$connection);
    }

    static function types(): TypesDef
    {
        return  new TypesDef;
    }

    static function escapeString(string ...$strings)
    {
        if (count($strings) == 1) return self::$connection->escape_string($strings[0]);

        $results = [];
        foreach ($strings as $string) {
            $results[] = self::$connection->escape_string($string);
        }

        return $results;
    }
}
