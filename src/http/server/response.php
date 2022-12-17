<?php

namespace Tetra\HTTP\Server;

class Response
{
    function __construct($content = "", $code = 200, $headers = [])
    {
        http_response_code($code);
        foreach ($headers as $header) {
            header($header);
        }
        echo $content;
    }
}
