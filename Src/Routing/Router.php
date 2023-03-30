<?php

namespace Tet\Routing;

use Tet\Filesystem\Filesystem;
use Tet\Routing\Route;
use Tet\Routing\Routes;
use ArrayObject;
use \Exception;
use Tet\Filesystem\Path;
use Tet\HTTP\Server;

use Tet\HTTP\Response;

class Router
{
    public ArrayObject $routes;
    public int $count = 0;
    public string $root;
    protected Route $curRoute;

    function setRoot(string $root)
    {
        $this->root = $root;
    }

    function getRoutes()
    {
        return $this->routes ?? $this->routes = new Routes;
    }

    function getCurrentRoute(): ?Route
    {
        return $this->curRoute ?? $this->getMatchedRoute();
    }

    function arr(Route ...$router)
    {
        array_merge($this->getRoutes(), $router);
    }

    function any(string $path, $calback, $default = false): Router
    {
        return $this->addRoute(new Route("any", $path, $calback, $default));
    }

    function get(string $path, $calback, $default = false): Router
    {
        return $this->addRoute(new Route("get", $path, $calback, $default));
    }

    function post(string $path, $calback, $default = false): Router
    {
        return $this->addRoute(new Route("post", $path, $calback, $default));
    }

    function put(string $path, $calback, $default = false): Router
    {
        return $this->addRoute(new Route("put", $path, $calback, $default));
    }

    function delete(string $path, $calback, $default = false): Router
    {
        return $this->addRoute(new Route("delete", $path, $calback, $default));
    }


    function options(string $path, $calback, $default = false): Router
    {
        return $this->addRoute(new Route("options", $path, $calback, $default));
    }

    function getMatchedRoute(): ?Route
    {
        $routes = $this->getRoutes();
        if (!$routes) throw new Exception("no router init");
        if ($this->count == 0) throw new Exception("no router setted");

        $requestMethod = strtolower((new Server)->getRequest()->getMethod());

        foreach ($routes as $route) {
            // простое совпадение                      
            if ($route->method != "any" && $requestMethod <> $route->method) continue;
            if ($route->isRequested($this->root)) return $this->curRoute = $route;
        }

        return null;
    }

    function getDefaultRoute(): ?Route
    {
        $routes = $this->getRoutes();
        if (!$routes) throw new Exception("no router init");
        if ($this->count == 0) throw new Exception("no router setted");

        foreach ($routes as $route) {
            if ($route->default) return  $this->curRoute = $route;
        }

        return null;
    }

    function createHTML($destination = "router.html")
    {
        $routes = $this->getRoutes();
        $html = "<title>Router</title>";
        foreach ($routes as $key => $route) {
            $html .= "<a href=\".$route->uri\">$key. $route->uri</a><br>\r\n";
        }
        (new Filesystem)->createFile($destination, $html);
        return "ok";
    }

    private function addRoute(Route $route): Router
    {
        $routes = $this->getRoutes();
        $routes[] = $route;
        $this->count = count($routes);
        //return $route;
        return $this;
    }

    function redirect(string $url): bool
    {
        $location = $url;

        $path = new Path($location);
        if ($path->isLocal()) {
            $location = realpath($this->root) . $location;
            $location = str_replace("//", "/", $location);
            $location = (new Server)->getProtocol() . "://" . (new Path($location))->getRemotePath();
        }

        header("Location: $location");

        return true;
    }
}
