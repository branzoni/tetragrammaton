<?php

namespace Tetra\HTTP\Server;

use Tetra\Params;

class Request
{

    private $params;

    function __construct()
    {
        $this->params = new Params;
        $this->params->load($this->get_data());
    }

    function params(): Params
    {
        return $this->params;
    }

    function query_params(): Params
    {
        $tmp = new Params;
        $tmp->load($_GET);
        return $tmp;
    }

    function formdata(): array
    {
        return $_POST;
    }

    function body()
    {
        return file_get_contents('php://input');
    }

    function headers()
    {
        return getallheaders();
    }

    function files()
    {
        if (isset($_FILES)) return $_FILES;
    }

    function is_post(): bool
    {
        return $this->method() == "POST";
    }

    function is_get(): bool
    {
        return $this->method() == "GET";
    }

    function is_options(): bool
    {
        return $this->method() == "OPTIONS";
    }

    function is_put(): bool
    {
        return $this->method() == "PUT";
    }

    function is_delete(): bool
    {
        return $this->method() == "DELETE";
    }

    function method(): string
    {
        return $_SERVER["REQUEST_METHOD"];
    }

    function uri(): string
    {
        return $_SERVER["REQUEST_URI"];
    }

    private function get_data()
    {
        $method = $this->method();
        if ($method == "POST") return $_POST;
        if ($method == "GET") return $_GET;
        return false;
    }
}
