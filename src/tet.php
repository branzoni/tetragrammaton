<?php

namespace Tetra;

use Throwable;
use Exception;


include("common/properless.php");
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
    private $params; // переменная для хранения настроек двжика
    private $mysql; // пременная для объекта работы с БД

    private $server;
    private $client;

    public $report_email;
    public $response_function;

    function __construct()
    {
        $this->params = new Params;
        
        $this->server = new Server;
        $this->client = new Client;

        $this->mysql = new MySQL;        
    }

    function params(): Params
    {
        return $this->params;
    }

    function server(): Server
    {

        return $this->server;
    }

    function client(): Client
    {
        return $this->client;
    }

    function mysql(): MySQL
    {
        return $this->mysql;
    }

    
    function filesystem(): FileSystem
    {
        return new FileSystem;
    }

    function mail($from="", $to="", $subject="", $message="", $attachments=""){
        return new Mail($from, $to, $subject, $message, $attachments);
    }

    function utils(){
        return new Utils;
    }
    


    /**
     * функция-заглушка для быстрого ответа на некорректный запрос
     */

    function throw_exception(String $message): Result
    {
        throw new Exception($message);
        return new Result;
    }

    /**
     * Возвращает ответ на основе пользовательской функции, указанной при конфигурации движка
     */
    function response(): Bool
    {
        try {
            $tmp = $this->call_user_func();
        } catch (Throwable $e) {
            $tmp = $this->get_error_report($e);

            // отправляем отчет
            if (!$this->report_email || $this->report_email != "") $this->send_error_report($tmp);
        }

        echo $tmp;

        return true;
    }

    private function call_user_func(): String
    {
        // функция проверки параметров, необходимых для работы движка
        if (!$this->response_function)  return $this->throw_exception("'response_function' property not set (#1)");
        if ($this->response_function == "")  return $this->throw_exception("'response_function' property not set (#2)");
        return call_user_func($this->response_function, $this);
    }

    private function get_error_report(Throwable $e): String
    {
        $tmp = new Result;
        $tmp->error = true;
        $tmp->result = false;
        $tmp->description = $e->getMessage();
        $tmp->data = (array) $e;
        //$tmp->request = $this->request()->params();
        $tmp->request = $this->server()->request()->params();
        $tmp->url = $_SERVER["REQUEST_URI"];
        $tmp->method = $_SERVER["REQUEST_METHOD"];
        $tmp = json_encode($tmp);
        return $tmp;
    }

    private function send_error_report($text): Bool
    {
        return @mail(
            $this->report_email,
            "Tetra error",
            $text,
            "Content-Type: text/html; charset=UTF-8"
        );
    }

    function about(){
        return "Simple library for PHP apps";
    }
}
