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

	public static function connect(string $hostname, string $user, string $password) {
		self::$connection = mysqli_connect($hostname, $user, $password);
		if (!self::$connection) {
			throw new Exception('Could not connect to database.');
		}
	}

	public static function open(string $hostname = null,  string $database = null, string $user = null, string $password = null, string $charset = "utf8"): void
	{
		self::$connection = mysqli_connect($hostname, $user, $password, $database);
		if (!self::$connection) throw new Exception(self::getError());
		self::setCharset($charset);
	}

	public static function open2(string $hostname, string $user, string $password, string $database = null, string $charset = "utf8"): void
    {
		self::open($hostname, $user, $password, $database, $charset);
    }

	public static function close(): void
    {
        mysqli_close(self::$connection);
    }

    /**
     * @throws Exception
     */
	public static function execute(string $query)
    {
        $result = mysqli_query(self::$connection, $query);
        if ($result === false) throw new Exception(self::getError());
        if ($result === true) return true;
        return mysqli_fetch_all($result,  MYSQLI_ASSOC);
    }


	public static function getRecord(string $query, int $index = 0): ?object
    {
        $result = self::execute($query);
        if (!$result) return null;
        $result =  $result[$index];
        $result = (object)  $result;
        return  $result;
    }

	public static function  getLastInsertId()
    {
        return mysqli_insert_id(self::$connection);
    }

	public static function getError(): string
    {
        return mysqli_error(self::$connection);
    }

	public static function isConnected(): bool
    {
        return boolval(self::$connection);
    }

	public static function setCharset(string $charset = "utf8"): void
    {
        mysqli_set_charset(self::$connection, $charset);
    }

	public static function createDatabase(string $name): void
    {
        self::execute("CREATE DATABASE IF NOT EXISTS $name");
    }

	public static function selectDatabase(string $database): void
    {
        mysqli_select_db(self::$connection, $database);
    }

	public static function getCurrentDb(): Database
    {
        return new Database(new self);
    }

	public static function createDatabase2(string $name): Database
    {
        self::createDatabase($name);
        self::selectDatabase($name);
        return self::getCurrentDb();
    }

	public static function createDatabaseFromSchema(DatabaseDef $databaseDef): void
    {
        // создаем структуру базы        
        $db = self::createDatabase2($databaseDef->name);
        $db->createTablesFromSchema($databaseDef);
        $db->deleteOutSchemaTables($databaseDef);
    }

	public static function modifyDatabaseFromSchema(DatabaseDef $databaseDef): void
    {
        // создаем структуру базы
        $db = self::createDatabase2($databaseDef->name);
        $db->createTablesFromSchema($databaseDef);
    }

	public static function getQuery(): Query
    {
        return new  Query(self::$connection);
    }

	public static function types(): TypesDef
    {
        return  new TypesDef;
    }

	public static function escapeString(string ...$strings)
    {
        if (count($strings) == 1) return self::$connection->escape_string($strings[0]);

        $results = [];
        foreach ($strings as $string) {
            $results[] = self::$connection->escape_string($string);
        }

        return $results;
    }
}
