<?php

namespace Tetra\HTTP\Client;

class Request
{
    public $url;
    public $method;
    public $headers;
    public $params;
    public $body;

    function __construct(){
        $this->headers = [];
        $this->params = [];        
    }
    
    function set_headers($value){
        $this->headers = $value;
    }

    function set_header($name, $value){    
        $this->headers[$name] = $value;
    }


    function set_params($value){
        $this->params = $value;
    }

    function set_param($name, $value){    
        $this->param[$name] = $value;
    }


    
}
