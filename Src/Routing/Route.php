<?php

namespace Tet\Routing;

use Tet\Path;
use Tet\HTTP\Server;

class Route
{
    public $method;
    public $uri;
    public $callback;

    function __construct(string $method, string $path, callable $calback)
    {
        $this->method = $method;
        $this->uri = $path;
        $this->callback = $calback;
    }

    function isEqual(string $root, string $requesteddURI): bool
    {
        // echo "$root<br>";
        // echo "{$this->uri}<br>";
        // echo "{$requesteddURI}<br>";

        return $root . $this->uri == $requesteddURI;
    }

    function isRequested($root): bool
    {
        $root = (new Path($root))->getRelativePath();
        return $root . $this->uri == (new Server)->getRequestedURI();
    }

    function getArguments(): ?array
    {
        if (!$this->hasVariables()) return null;

        // сравнение структуры запросов
        $path1 = new Path(($this->uri));
        $path2 = new Path((new Server)->getRequestedURI());
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
        return str_replace(['{', '}'], '', $segment);
    }
}
