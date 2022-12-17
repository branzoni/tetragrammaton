<?php

namespace Tetra\HTTP;

use Tetra\HTTP\Server\Request;
use Tetra\HTTP\Server\Response;

class Server
{

    private $request;
    private $response;

    function __construct()
    {
        $this->request = new Request;
        $this->response = new Response;
    }

    function request(){
        return $this->request;
    }

    function protocol()
    {
        $tmp = $_SERVER["SERVER_PROTOCOL"];
        $tmp = explode("/", $tmp);
        $tmp = $tmp[0];
        $tmp = strtolower($tmp);

        if (isset($_SERVER["HTTPS"])) {
            if ($_SERVER["HTTPS"] != "") $tmp = "https";
        }

        return $tmp;
    }

    function host()
    {
        $tmp = $_SERVER["HTTP_HOST"] ?? "";
        $tmp = strtolower($tmp);
        return $tmp;
    }

    function port()
    {
        return $_SERVER["SERVER_PORT"];
    }

    function name()
    {
        return $_SERVER["SERVER_NAME"];
    }

    function root($local = true)
    {
        if ($local) return $_SERVER['DOCUMENT_ROOT'];

        $host = $this->host();
        if ($host == "localhost") return $this->protocol() . "://" . $host . ":" . $this->port();
        if ($host = "localhost") return $this->protocol() . "://" . $host;
    }
}
