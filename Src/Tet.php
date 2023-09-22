<?php

namespace Tet;

use \Exception;
use Tet\Common\Logger;
use \Throwable;
use \stdClass;

use Tet\HTTP\Response;
use Tet\HTTP\Server;
use Tet\Routing\Router;
use Tet\Filesystem\File;

use Tet\HTTP\Client;
use Tet\Mail\Mailer;
use Tet\Database\MySQL;
use Tet\Filesystem\Filesystem;
use Tet\Security\Auth;

use Tet\Common\Collection;
use Tet\Common\Log;
use Tet\Common\Utils;


class Tet
{
    public static function router(): Router
    {
        return new Router();
    }

    public static function logger(): Logger
    {
        return new Logger;
    }

    public static function server(): Server
    {
        return new Server;
    }

    public static function utils(): Utils
    {
        return new Utils;
    }

    public static function filesystem(): Filesystem
    {
        return new Filesystem;
    }

    public static function auth(): Auth
    {
        return new Auth;
    }

    public static function mailer(): Mailer
    {
        return new Mailer;
    }

    public static function mySQL(): MySQL
    {
        return new MySQL;
    }

    public static function client(): Client
    {
        return new Client;
    }

    //----- КОНЕЦ СТАТИКИ -------

    public static function _require($path): bool
    {
        $file = new File($path);
        if (!$file->isExists()) throw new Exception("Required $path not found");
        require($path);
        return true;
    }

    public static function _setErrorHandler()
    {
        set_error_handler(function ($code, $message, $file, $line) {
            self::error_callback($code, $message, $file, $line);
            exit;
        });
    }

    public static function _setExeptionHandler()
    {
        set_exception_handler(function (Throwable $e) {
            self::error_callback($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
        });
    }

    private static function error_callback($code, $message, $file, $line)
    {
        $tmp = new stdClass;
        $tmp->message = $message;
        $tmp->code = $code;
        $tmp->file = $file;
        $tmp->line = $line;
        $tmp->request = self::server()::getRequest();
        $tmp->url = $tmp->request->getURI();
        $tmp->method = $tmp->request->getMethod();


        $levels = [
            "0" => "Undefined",
            "1" => "Error",
            "2" => "Warning",
            "4" => "Parse",
            "8" => "Notice"
        ];

        self::logger()::add($levels[$code], "$message in line $line of $file, $tmp->method, $tmp->url");
        return Server::sendResponse(new Response(json_encode($tmp), 500));
    }
}