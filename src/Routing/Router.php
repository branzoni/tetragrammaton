<?php

namespace Tet;

use Tet\HTTP\Server;
use Tet\Routing\Route;
use Tet\Routing\Routes;

class Router
{
    public Routes $routes;
    public Path $root;
    public int $count;

    function __construct()
    {
        $this->routes = new Routes;
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

    
    function getRequestedURI(): string
    {
        $tmp = (new Server)->getRequestedURI();
        return str_replace($this->root->getRelativePath(), "", $tmp);
    }
}
