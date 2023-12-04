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
	public static string $accessControlAllowOrigin;
	public static string $accessControlAllowMethods;
	public static string $accessControlAllowHeaders;

    public static ArrayObject $routes;
    public static int $count = 0;
    public static string $root;
    protected static Route $curRoute;

    static function setRoot(string $root)
    {
        self::$root = $root; // часть пути, относительно которой определяется динамическая часть
    }

    static function getRoutes(): ArrayObject
    {
        return self::$routes ?? self::$routes = new Routes;
    }

    static function getCurrentRoute(): ?Route
    {
        return self::$curRoute ?? self::getMatchedRoute();
    }

    static function arr(Route ...$router)
    {
        array_merge(self::getRoutes(), $router);
    }

    static function any(string $path, $calback, $default = false): Router
    {
        return self::addRoute(new Route("any", $path, $calback, $default));
    }

    static function get(string $path, $calback, $default = false): Router
    {
        return self::addRoute(new Route("get", $path, $calback, $default));
    }

    static function post(string $path, $calback, $default = false): Router
    {
        return self::addRoute(new Route("post", $path, $calback, $default));
    }

    static function put(string $path, $calback, $default = false): Router
    {
        return self::addRoute(new Route("put", $path, $calback, $default));
    }

    static function delete(string $path, $calback, $default = false): Router
    {
        return self::addRoute(new Route("delete", $path, $calback, $default));
    }

    static function options(string $path, $calback, $default = false): Router
    {
        return self::addRoute(new Route("options", $path, $calback, $default));
    }

    static function patch(string $path, $calback, $default = false): Router
    {
        return self::addRoute(new Route("patch", $path, $calback, $default));
    }

    static function getMatchedRoute(): ?Route
    {
        $routes = self::getRoutes();
        if (!$routes) throw new Exception("no router init");
        if (self::$count == 0) throw new Exception("no router setted");

        $requestMethod = strtolower(Server::getRequest()->getMethod());

        foreach ($routes as $route) {
            // простое совпадение                      
            if ($route->method != "any" && $requestMethod <> $route->method) continue;
            if ($route->isRequested(self::$root)) return self::$curRoute = $route;
        }

        return null;
    }

    static function getDefaultRoute(): ?Route
    {
        $routes = self::getRoutes();
        if (!$routes) throw new Exception("no router init");
        if (self::$count == 0) throw new Exception("no router setted");

        foreach ($routes as $route) {
            if ($route->default) return  self::$curRoute = $route;
        }

        return null;
    }

    static function createHTML($destination = "router.html")
    {
        $routes = self::getRoutes();
        $html = "<title>Router</title>";
        foreach ($routes as $key => $route) {
            $html .= "<a href=\".$route->uri\">$key. $route->uri</a><br>\r\n";
        }
        Filesystem::createFile($destination, $html);
        return "ok";
    }

    private static function addRoute(Route $route): Router
    {
        $routes = self::getRoutes();
        $routes[] = $route;
        self::$count = count($routes);
        //return $route;
        return new self;
    }

    static function redirect(string $url): bool
    {
        $location = $url;

        $path = new Path($location);
        if ($path->isLocal()) {
            $location = realpath(self::$root) . $location;
            $location = str_replace("//", "/", $location);
            $location = Server::getProtocol() . "://" . (new Path($location))->getRemotePath();
        }

        header("Location: $location");

        return true;
    }

	public static function run(): bool
	{
		// сначала отрабатываем возможный запрос OPTIONS
		if (Server::getRequest()->isOptions()) return self::sendResponse(new Response(" ", 200));

		// попытка штатно отработать роутинг
		$route = self::getCurrentRoute();
		if ($route) return self::sendResponse($route->getResponse());

		// попытка отработать дефолтный роут, если он был указан
		$route = self::getDefaultRoute();
		if ($route) return self::redirect($route->uri);

		// возврат ответа 404
		return self::sendResponse(new Response(null, 404));
	}

	private static function sendResponse(Response $response): bool
	{
		$response->headers->set('Access-Control-Allow-Origin', self::$accessControlAllowOrigin);
		$response->headers->set('Access-Control-Allow-Methods', self::$accessControlAllowMethods);
		$response->headers->set('Access-Control-Allow-Headers', self::$accessControlAllowHeaders);
		return Server::sendResponse($response);
	}
}
