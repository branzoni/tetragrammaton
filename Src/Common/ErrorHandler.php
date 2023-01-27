<?php

namespace Tet;

use stdClass;
use Tet\HTTP\Response;
use Throwable;
use Tet\Result;
use Tet\HTTP\Server;

class ErrorHandler
{

    function setErrorHandler()
    {
        set_error_handler(function ($code, $message, $file, $line) {
            $this->error_callback($code, $message, $file, $line);
            exit;
        });
    }

    function setExeptionHandler()
    {
        set_exception_handler(function (Throwable $e) {
            $this->error_callback($e->getCode(),  $e->getMessage(), $e->getFile(), $e->getLine());
        });
    }

    private function error_callback($code, $message, $file, $line)
    {
        $tmp = new stdClass;
        $tmp->message = $message;
        $tmp->code = $code;
        $tmp->file = $file;
        $tmp->line = $line;

        $srv = new Server;
        $tmp->request = $srv->getRequest();
        $tmp->url = $srv->getRequest()->getURI();
        $tmp->method = $srv->getRequest()->getMethod();

        $response = new Response();
        $response->body = json_encode($tmp);
        $srv->sendResponse($response);
    }

    /**
     * функция-заглушка для быстрого ответа на некорректный запрос
     */
    function throwException(String $message): Result
    {
        throw new \Exception($message);
        return new Result;
    }
}
