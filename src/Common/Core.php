<?php

namespace Tet;

use stdClass;
use Throwable;
use Tet\Result;
use Tet\HTTP\Server;

class Core{
    
    function setErrorHandler($closue){
        set_error_handler($closue);
    }

    /**
     * функция-заглушка для быстрого ответа на некорректный запрос
     */
     function throwException(String $message): Result
     {
         throw new \Exception($message);
         return new Result;
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
            $tmp = $this->getDefaultErrorHandler($tmp);
        }

        return $tmp;
    }

    function getDefaultErrorHandler($e)
    {
        $tmp = new Result;
        $tmp->error = true;
        $tmp->result = false;
        $tmp->description = $e->message;
        $tmp->data = (array) $e;
        $srv = new Server;
        $tmp->request = $srv->getRequest();
        $tmp->url = $srv->getRequest()->getURI();
        $tmp->method = $srv->getRequest()->getMethod();
        $tmp = json_encode($tmp);

        return $tmp;
    }
}