<?php

namespace Tet;

include("common/params.php");
include("common/result.php");
include("common/utils.php");

include("http/header.php");
include("http/headers.php");

include("http/server.php");
include("http/serverrequest.php");

include("http/client.php");
include("http/clientrequest.php");
include("http/Response.php");

include("filesystem/filesystem.php");
include("filesystem/path.php");
include("filesystem/file.php");
include("filesystem/directory.php");

include("Database/table.php");
include("Database/row.php");
include("Database/mysql.php");
include("Database/query.php");

include("Mail/Mail.php");
include("Common/Core.php");

include("Routing/Router.php");
include("Routing/Routes.php");
include("Routing/Route.php");


use stdClass;
use Tet\Core;
use Tet\HTTP\Client;
use Tet\HTTP\Server;
use Tet\Mail;
use Tet\Utils;

/**
 * Обеспечивает необходимый функционал для разработки несложных API:
 * - получение параметров запроса
 * - работа с базой MySQL
 * - работа с файлами
 * - формирование ответа в пользовательской функции
 * @author Sergey V. Afanasyev <sergey.v.afanasyev@gmail.com>
 */


class Tet
{

    public Params $params;
    public FileSystem $fiesystem;
    public Router $router;
    public Server $server;
    public Client $client;
    public MySQL $mysql;


    function autoload(string $path)
    {
        $fs = $this->fiesystem;
        $files = $fs->getDirectory($path)->getFileList(["*.php"]);
        foreach ($files as $key => $file) {
            echo "$file<br>";
            //include($file);
        }
    }

    function __construct()
    {
        (new Core)->setErrorHandler(function ($code, $message, $file, $line) {
            $tmp = new stdClass;
            $tmp->message = $message;
            $tmp->code = $code;
            $tmp->file = $file;
            $tmp->line = $line;
            echo "!!!!!!!!!!!!!!!1";
            $tmp = (new Core)->getDefaultErrorHandler($tmp);
            (new Server)->sendResponse($tmp);
            exit;
        });

        $this->params = new Params;
        $this->fiesystem = new FileSystem;
        $this->server = new Server;
        $this->client = new Client;
        $this->router = new Router;
    }

    function Mail(): Mail
    {
        return new Mail;
    }

    function Utils(): Utils
    {
        return new Utils;
    }

    function run(): bool
    {
        $requestedURI = $this->router->getRequestedURI();
        foreach ($this->router->routes as $route) {
            if ($route->uri == $requestedURI) {
                switch (gettype($route->callback)) {
                    case 'object':
                    case 'array':
                        echo call_user_func($route->callback, $this);
                        break;
                    default:
                        echo $route->callback;
                };
                return true;
            }
        }

        return true;
    }

    /**
     * Возвращает ответ на основе пользовательской функции, указанной при конфигурации движка
     */
    function run2($closure = null): Bool
    {
        $tmp = (new Core)->try(function () use ($closure) {
            return call_user_func($closure, $this);
        });

        (new Server)->sendResponse($tmp);

        return true;
    }
}
