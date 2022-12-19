<?php

namespace Tetra;

include("common/prop.php");
include("common/params.php");
include("common/result.php");
include("common/utils.php");

include("http/server/server.php");
include("http/server/request.php");
include("http/server/response.php");
include("http/client/client.php");
include("http/client/request.php");
include("http/client/response.php");

include("filesystem/filesystem.php");
include("filesystem/filesystem_object.php");
include("filesystem/file.php");

include("db/table.php");
include("db/row.php");
include("db/mysql.php");
include("db/query.php");

include("mail/mail.php");


use Throwable;
use Exception;
use stdClass;
use Tetra\HTTP\Server;
use Tetra\HTTP\Client;
use Tetra\Mail;
use Tetra\Utils;

/**
 * Обеспечивает необходимый функционал для разработки несложных API:
 * - получение параметров запроса
 * - работа с базой MySQL
 * - работа с файлами
 * - формирование ответа в пользовательской функции
 * - поддержка шаблонизации Twig
 * @author Sergey V. Afanasyev <sergey.v.afanasyev@gmail.com>
 */

class Tetra
{
    private $params;
    private $server;
    private $client;
    private $mysql; // пременная для объекта работы с БД
    private $filesystem;

    public $app_error_report_email;
    public $app_closure;

    function __construct()
    {
        set_error_handler(function ($code, $message, $file, $line) {
            $tmp = new stdClass;
            $tmp->message = $message;
            $tmp->code = $code;
            $tmp->file = $file;
            $tmp->line = $line;
            
            $tmp= $this->error_handler($tmp);

            $this->server()->send_response($tmp);
            exit;
        });
    }

    function params(): Params
    {
        $this->params = $this->params ?? new Params;
        return $this->params;
    }


    function server(): Server
    {
        $this->server = $this->server ?? new Server;
        return $this->server;
    }

    function client(): Client
    {
        $this->client = $this->client ?? new Client;
        return $this->client;
    }

    function mysql(): MySQL
    {
        $this->mysql = $this->mysql ?? new MySQL;
        return $this->mysql;
    }


    function filesystem(): FileSystem
    {
        $this->filesystem = $this->filesystem ?? new FileSystem;
        return $this->filesystem;
    }

    function mail($from = "", $to = "", $subject = "", $message = "", $attachments = "")
    {
        return new Mail($from, $to, $subject, $message, $attachments);
    }

    function utils(): Utils
    {
        return new Utils;
    }

    function about()
    {
        return "Simple library for PHP apps";
    }

    /**
     * функция-заглушка для быстрого ответа на некорректный запрос
     */

    function throw_exception(String $message): Result
    {
        throw new Exception($message);
        return new Result;
    }

    // методы приложения

    function app_error_report_email(): prop
    {
        $this->app_error_report_email = $this->app_error_report_email ?? new Prop;
        return $this->app_error_report_email;
    }

    function app_closure(): prop
    {
        $this->app_closure = $this->app_closure ?? new Prop;
        return $this->app_closure;
        // return $this->try(function(){
        //     return $this->app_closure;
        // });
    }

    /**
     * Возвращает ответ на основе пользовательской функции, указанной при конфигурации движка
     */
    function app_run(): Bool
    {

        $tmp = function () {
            if (!$this->app_closure)  return $this->throw_exception("'app_closure' property not set (#1)");
            if ($this->app_closure == "")  return $this->throw_exception("'app_closure' property not set (#2)");

            return call_user_func($this->app_closure->get(), $this);
        };

        $tmp = $this->try($tmp);
        $this->server()->send_response($tmp);
        return true;
    }

    function try($closure)
    {
        try {
            $tmp = $closure();            
        } catch (Throwable $e) {
            $tmp = new stdClass;
            $tmp->message = $e->getMessage();
            $tmp->code = $e->getCode();
            $tmp->file = $e->getFile();
            $tmp->line = $e->getLine();            
            $tmp = $this->error_handler($tmp);
        }

        return $tmp;
    }

    function error_handler($e)
    {
        $tmp = new Result;
        $tmp->error = true;
        $tmp->result = false;
        $tmp->description = $e->message;
        $tmp->data = (array) $e;
        $tmp->request = $this->server()->request()->params();
        $tmp->url = $this->server()->request()->uri();
        $tmp->method = $this->server()->request()->method();
        $tmp = json_encode($tmp);

        // отправляем отчет
        if (!$this->app_error_report_email || $this->app_error_report_email != "") {
            $this->mail("", $this->app_error_report_email, "tetra error", $tmp);
        };

        return $tmp;
    }
}
