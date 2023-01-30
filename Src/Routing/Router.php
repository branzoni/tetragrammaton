<?php

namespace Tet;

use ArrayObject;
use Tet\HTTP\Server;
use Tet\Routing\Route;
use Tet\Routing\Routes;

class Router
{
    public ArrayObject $routes;
    public int $count = 0;
    public string $root;
    function __construct($root)
    {        
        $this->routes = new Routes;
        $this->root = $root;
    }

    private function addRoute(Route $route):Route
    {
        $this->routes[] = $route;
        $this->count = count($this->routes);
        return $route;
    }

    function any(string $path, $calback): Route
    {
        return $this->addRoute(new Route("any", $path, $calback));
    }

    function get(string $path, $calback): Route
    {
        return $this->addRoute(new Route("get", $path, $calback));        
    }

    function post(string $path, $calback): Route
    {
        return $this->addRoute(new Route("post", $path, $calback));        
    }

    function getMatchedRoute(): ?Route
    {        
        if (!$this->routes) (new ErrorHandler)->throwException("no router init");
        if ($this->count == 0) (new ErrorHandler)->throwException("no router setted");
        
        foreach ($this->routes as $route) {
            // простое совпадение            
            if ($route->isRequested($this->root)) return $route;
            // получение аргументов включает проверку сложного совпадения роутов
            $args = $route->getArguments();            
            if ($args) return $route;
        }

        return null;
    }
}
