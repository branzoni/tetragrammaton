<?php

namespace Tetra\HTTP;

use Tetra\HTTP\Client\Request;
use Tetra\HTTP\Client\Response;

class Client
{
    private $request;
    private $response;

    function __construct()
    {
        $this->request = new Request;
        $this->response = new Response;
    }

    function request(): Request
    {
        return $this->request;
    }

    function response(): Response
    {
        return $this->response;
    }


    function send()
    {
        $url = $this->get_url();
        $context = $this->get_context();

        $this->response->body()->set(file_get_contents($url, false, $context));
        $this->response->headers =  $http_response_header;
        $this->response->code()->set($this->get_code());
    }

    private function get_url()
    {
        return $this->request->url()->get() .  "?" . http_build_query($this->request->params);
    }

    private function get_context()
    {
        $headers = "";
        foreach ($this->request->headers as $key => $value) {
            $headers .= "$key: $value\r\n";
        }

        $context = stream_context_create([
            'http' => [
                'ignore_errors' => true,
                'method' => $this->request->method()->get(),
                'header' => $headers,
                'content' => $this->request->body()->get()
            ]
        ]);

        return $context;
    }

    private function get_code()
    {
        if (!$this->response->headers) return false;
        $tmp = $this->response->headers[0];
        $tmp = explode(" ", $tmp);
        $tmp = $tmp[1];
        return $tmp;
    }
}
