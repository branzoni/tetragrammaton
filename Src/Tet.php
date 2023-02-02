<?php
namespace Tet;

use Tet\HTTP\Response;
use Tet\Routing\Route;
use Tet\Router;
use Tet\Fasade;
use Tet\HTTP\Server;

class Tet
{
    protected Router $router;
    protected Fasade $fasade;

    function router($root): Router
    {
        if (!isset($this->router)) $this->router = new Router($root);
        return $this->router;
    }

    function fasade(): Fasade
    {
        if (!isset($this->fasade)) $this->fasade = new Fasade;
        return $this->fasade;
    }

    function run(): bool
    {
        $route = $this->router->getMatchedRoute();
        if (!$route) return false;

        $response = $this->executeRouteCallback($route);
        if (!$response) return true;

        (new Server)->sendResponse($response);
        return true;
    }

    private function executeRouteCallback(Route $route): ?Response
    {
        // вызываем колбек        
        switch (gettype($route->callback)) {
            case 'object':
            case 'array':
                $result = call_user_func_array($route->callback, array($this->Fasade(), $route->getArguments()));
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
}
