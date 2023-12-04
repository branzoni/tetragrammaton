<?php

namespace Tet\Routing;

use Tet\Filesystem\Path;
use Tet\HTTP\Server;
use Tet\HTTP\Response;

class Route
{
    public string $method;
    public string $uri;
    public $callback;
    public bool $default;

    function __construct(string $method, string $path, callable $calback, bool $default)
    {
        $this->method = $method;
        $this->uri = $path;
        $this->callback = $calback;
        $this->default = $default;
    }

    function isEqual(string $root, string $requesteddURI): bool
    {
        return $root . $this->uri == $requesteddURI;
    }

    function isRequested($root): bool
    {
        $route = (new Path($root))->getRelativePath() . $this->uri;
        $route = str_replace("//", "/", $route);
        $requested = Server::getRequestedURI();
        $requested = explode("?", $requested)[0];

        if (substr($requested, -1) == "/" && substr($route, -1) != "/") $route = "$route/";

        if ($route  == $requested) return true;

        $path1 = new Path($route);
        $path2 = new Path($requested);

        if ($path1->getSegmentCount() != $path2->getSegmentCount()) return false;

        $count = $path1->getSegmentCount();
        $path_1_segments = $path1->getSegments();
        $path_2_segments = $path2->getSegments();

        // перебираем сегменты
        for ($i = 0; $i <= $count - 1; $i++) {
            $segment_1 = $path_1_segments[$i];
            $segment_2 = $path_2_segments[$i];
            if (!$this->isVariable($segment_1) && $segment_1 != $segment_2) return false;
        }

        return true;
    }

    function getVariable(string $varname)
    {
        return $this->getVariables()[$varname] ?? null;
    }

    function getVariables(): ?array
    {
        if (!$this->hasVariables()) return null;

        // сравнение структуры запросов
        $path1 = (new Path($this->uri))->getRelativePath();
        $path1 = new Path($path1);

        $path2 = new Path(Server::getRequestedURI());
        $tmp = $path2->getSegmentCount() - $path1->getSegmentCount();
        $tmp = "/" . implode("/", array_slice($path2->getSegments(), $tmp));
        $path2 = new Path($tmp);

        if ($path1->getSegmentCount() != $path2->getSegmentCount()) return null;

        $args = [];
        $count = $path1->getSegmentCount();
        $path_1_segments = $path1->getSegments();
        $path_2_segments = $path2->getSegments();

        // перебираем сегменты
        for ($i = 0; $i <= $count - 1; $i++) {
            $segment_1 = $path_1_segments[$i];
            $segment_2 = $path_2_segments[$i];
            if ($this->isVariable($segment_1)) {
                $arg_name =  $this->getVarialbeName($segment_1);
                $args[$arg_name] = $segment_2;
            } else {
                if ($segment_1 != $segment_2) return null;
            }
        }

        return $args;
    }

    private function hasVariables(): bool
    {
        return preg_match('/{*}/', $this->uri);
    }


    private function isVariable(string $segment): bool
    {
        return preg_match('/{*}/', $segment);
    }

    private function getVarialbeName(string $segment)
    {
        return str_replace(["{", "}"], "", $segment);
    }

    function getResponse(): ?Response
    {
        // вызываем колбек        
        switch (gettype($this->callback)) {
            case 'object':
            case 'array':
                $result = call_user_func($this->callback);
                break;
            default:
                $result = $this->callback;
        };

        if (!$result) return null;

        // отдаем респонс
        switch (gettype($result)) {
            case 'string':
                $response = new Response($result, 200);
                break;
            default:
                $response = $result;
        }

        return $response;
    }
}
