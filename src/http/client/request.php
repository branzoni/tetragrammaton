<?php

namespace Tetra;

namespace Tetra\HTTP\Client;

use Tetra\Prop;

class Request
{
    private $url;
    private $method;
    private $headers;
    private $params;
    private $body;

    function __construct()
    {
        $this->headers = [];
        $this->params = [];
        $this->url = new Prop;
        $this->method = new Prop;
        $this->body = new Prop;
    }

    function set_headers($headers)
    {
        $this->headers = $headers;
    }

    function set_header($name, $value)
    {
        $this->headers[$name] = $this->headers[$name] ?? new Prop;
        $this->headers[$name] = $value;
    }

    function header($name):Prop
    {
        $this->headers[$name] = $this->headers[$name] ?? new Prop;
        return $this->headers[$name];
    }

    function set_params($value)
    {
        $this->params = $value;
    }

    function set_param($name, $value)
    {
        $this->params[$name] = $value;
    }

    function url()
    {
        return $this->url;
    }

    function method()
    {
        return $this->method;
    }

    function body()
    {
        return $this->body;
    }
}
