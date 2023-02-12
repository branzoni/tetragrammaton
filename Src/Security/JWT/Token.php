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


    function isNotBefore(): bool
    {
        if (!isset($this->payload["nbf"])) return false;
        return ($this->payload["nbf"] > time());
    }

    function isExpired(): bool
    {
        if (!isset($this->payload["exp"])) return false;
        return ($this->payload["exp"] < time());
    }
}
