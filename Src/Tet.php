<?php

namespace Tet;

use Exception;
use Tet\Common\Fasade;
use Tet\HTTP\Response;
use Tet\HTTP\Server;
use Tet\Routing\Router;
use Tet\Routing\Route;
use \Throwable;
use \stdClass;
use Tet\Filesystem\File;

class Tet
{
    private Router $router;
    private Fasade $fasade;

    function router($root): Router
    {
        return $this->router ?? $this->router = new Router($root);
    }

    function fasade(): Fasade
    {
        return $this->fasade ?? $this->fasade = new Fasade;
    }

    function run(): bool
    {
        $route = $this->router->getMatchedRoute();

        if ($route) {
            $response = $this->executeRouteCallback($route);
            if (!$response) return true;
            return (new Server)->sendResponse($response);
        } else {
            $route = $this->router->getDefaultRoute();
            if ($route) return $this->router->redirect($route->uri);
        }

        return (new Server)->sendResponse(new Response(null, 404));
    }

    private function executeRouteCallback(Route $route): ?Response
    {
        // вызываем колбек        
        switch (gettype($route->callback)) {
            case 'object':
            case 'array':
                $result = call_user_func_array($route->callback, array($this->fasade(), $route));
                break;
            default:
                $result = $route->callback;
        };

        if (!$result) return null;

        // отдаем респонс
        switch (gettype($result)) {
            case 'string':
                $response = new Response;
                $response->body = $result;
                $response->code = 200;
                return $response;
                break;
            default:
                return $result;
        }
    }

    function require($path):bool
    {
        $env = new File($path);
        if(!$env->isExists()) throw new Exception("Required $path not found");
        require($path);
        return true;
    }

    public function setErrorHandler()
    {
        set_error_handler(function ($code, $message, $file, $line) {
            $this->error_callback($code, $message, $file, $line);
            exit;
        });
    }

    public function setExeptionHandler()
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


        $levels = [
            "0" => "qqq",
            "1" => "Error",
            "2" => "Warning",
            "4" => "Parse",
            "8" => "Notice"
        ];
              
        $this->fasade()->log()->add($levels[$code], "$message in line $line of $file, $tmp->method, $tmp->url");
        $this->fasade()->server()->sendResponse(new Response(json_encode($tmp), 200));
    }
}
