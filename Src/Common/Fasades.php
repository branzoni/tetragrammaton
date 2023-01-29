<?php

namespace Tet;

use Tet\HTTP\Client;
use Tet\HTTP\Server;
use Tet\Mail;
use Tet\Utils;

class Fasades
{
    protected static Collection $params;
    protected static MySQL $mySQL;    

    static function Params(): Collection
    {
        if (!isset(self::$params)) self::$params = new Collection;
        return self::$params;
    }

    static function MySQL(): MySQL
    {
        if (!isset(self::$mySQL)) self::$mySQL = new MySQL;
        return self::$mySQL;
    }

    static function Filesystem(): FileSystem
    {
        return new FileSystem;        
    }

    static function Server(): Server
    {
        return new Server;
    }

    static function Client(): Client
    {
        return new Client;
    }

    static function Utils(): Utils
    {
        return new Utils;
    }

    static function Mail(): Mail
    {
        return new Mail;        
    }
}
