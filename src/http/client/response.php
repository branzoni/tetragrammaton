<?php

namespace Tetra\HTTP\Client;

use Tetra\Prop;

class Response
{
    private $body;
    private $headers;
    private $code;

    function __construct()
    {
        $this->body = new Prop;
        $this->code = new Prop;
    }

    function body(){
        return $this->body;
    }

    function code(){
        return $this->code;
    }

    function headers(){
        return $this->headers;
    }
}
