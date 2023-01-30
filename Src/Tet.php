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

    static function Router($root): Router
    {

        if (!isset(self::$router)) self::$router = new Router($root);
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
        if(!$response) return true;
        (new Server)->sendResponse($response);
        return true;
    }

    static private function executeRouteCallback(Route $route): ?Response
    {
        switch (gettype($route->callback)) {
            case 'object':
            case 'array':
                $result = call_user_func_array($route->callback, array(self::Fasades(), $route->getArguments()));
                if(!$result) return null;
                switch(gettype($result))
                {                    
                    case 'string':
                        $response = new Response;
                        $response->body = $result;
                        $response->code = 200;
                        return $response;
                        break;
                    default:
                        return $result;
                }

                break;
            default:
                return $route->callback;
        };
    }
}
