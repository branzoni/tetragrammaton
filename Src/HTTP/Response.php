<?php

namespace Tet\HTTP;

class Response
{
    public Headers $headers;
    public ?int $code = null;
    public ?string $body = null;

    public function __construct(string $body = null, int $code = 200)
    {
        $this->code = $code;
        $this->body = $body;
        $this->headers = new Headers;        
    }

    public function __toString()
    {
        // http_response_code($this->code);

        // foreach ($this->headers->toArray() as $key => $header) {            
        //    header("$key:$header");
        // }
        // return $this->body;
    }
}
