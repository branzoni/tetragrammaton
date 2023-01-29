<?php

use Tet\HTTP\Response;
use Tet\Routing\Route;
use Tet\Router;
use Tet\ErrorHandler;
use Tet\Fasades;
use Tet\HTTP\Server;

class Tet
{
    protected static Router $router;
    protected static Fasades $fasades;

    function __construct()
    {
        (new ErrorHandler)->setErrorHandler();
        (new ErrorHandler)->setExeptionHandler();
    }

    static function Router(): Router
    {
        if (!isset(self::$router)) self::$router = new Router;
        return self::$router;
    }

    static function Fasades(): Fasades
    {
        if (!isset(self::$fasades)) self::$fasades = new Fasades;
        return self::$fasades;
    }

    static function run(): bool
    {
        $route = self::$router->getMatchedRoute();
        if (!$route) return false;
        $response =self::executeRouteCallback($route);
        (new Server)->sendResponse($response);
        return true;
    }

    private function executeRouteCallback(Route $route): Response
    {
        switch (gettype($route->callback)) {
            case 'object':
            case 'array':
                return call_user_func_array($route->callback, array($this->fasades, $route->getArguments()));
                break;
            default:
                return $route->callback;
        };
    }
}
