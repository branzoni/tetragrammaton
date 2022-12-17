<?php

namespace Tetra\HTTP\Server;

use Tetra\Params;

class Request
{

    private $params;
    private $headers;

    function __construct()
    {
        $this->params = new Params;
        $this->params->load($this->get_data());
        $this->headers = $this->get_headers();
    }

    function params()
    {
        return $this->params;
    }

    function headers()
    {
        return $this->headers;
    }

    function files()
    {
        if (isset($_FILES)) return $_FILES;
    }

    function is_post()
    {
        return $this->get_method() == "POST";
    }

    function is_get()
    {
        return $this->get_method() == "GET";
    }

    function is_options()
    {
        return $this->get_method() == "OPTIONS";
    }

    function get_method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }


    function has($names)
    {
    }

    function query_params(): Params
    {
        $tmp = new Params;
        $tmp->load($_GET);
        return $tmp;
    }


    private function get_data()
    {

        switch ($this->get_method()) {
            case "POST":
                return $_POST;
                break;
            case "GET":
                return $_GET;
                break;
        }

        return false;
    }

    private function get_headers()
    {
        return getallheaders();
    }
}
