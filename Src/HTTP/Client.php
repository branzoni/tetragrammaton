<?php

namespace Tet\HTTP;

class Client
{
    function newRequest(string $url = ""): ClientRequest
    {
        $cr = new ClientRequest;
        $cr->url = $url;
        return $cr;
    }
}
