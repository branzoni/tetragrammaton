<?php

namespace Tet;

use ArrayObject;
use Tet\Routing\Route;
use Tet\Routing\Routes;
use \Exception;

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

    function createHTML($destination="router.html")
    {    
        $html = "<title>Router</title>";
        foreach ($this->routes as $key=>$route) {
            $html .= "<a href=\".$route->uri\">$key. $route->uri</a><br>\r\n";
        }        
        (new Filesystem)->createFile($destination, $html);
        return "ok";
    }

    private function addRoute(Route $route):Router
    {
        $this->routes[] = $route;
        $this->count = count($this->routes);
        //return $route;
        return $this;
    }

    function any(string $path, $calback): Router
    {
        return $this->addRoute(new Route("any", $path, $calback));
    }

    function get(string $path, $calback): Router
    {
        return $this->addRoute(new Route("get", $path, $calback));        
    }

    function post(string $path, $calback): Router
    {
        return $this->addRoute(new Route("post", $path, $calback));        
    }

    function getMatchedRoute(): ?Route
    {        
        if (!$this->routes) throw new Exception("no router init");
        if ($this->count == 0) throw new Exception("no router setted");
        
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
