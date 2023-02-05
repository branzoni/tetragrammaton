<?php

namespace Tet\Security\JWT;

class Token
{
    public ?array $header;
    public ?array $payload;

    function __construct(?array $header = null, ?array $payload = null)
    {
        $this->header = $header;
        $this->payload = $payload;
    }
}
