<?php

namespace Tet\Routing;

class Route
{
    public $method;
    public $uri;
    public $callback;

    function __construct(string $method, string $path, $calback)
    {
        $this->method = $method;
        $this->uri = $path;
        $this->callback = $calback;
    }
}


