<?php

namespace Tet\HTTP;

class Client
{
    static function newRequest(string $url = ""): ClientRequest
    {
        $cr = new ClientRequest;
        $cr->url = $url;
        return $cr;
    }
}
