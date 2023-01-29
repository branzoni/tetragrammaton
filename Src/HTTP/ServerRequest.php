<?php

namespace Tet\HTTP;

use Tet\Collection;

class ServerRequest
{
    public function getHeaders():Headers
    {
        $tmp = new Headers;
        $tmp->add(getallheaders());
        return $tmp;
    }

    public function getParams():Collection
    {
        $tmp = new Collection;
        $tmp->add($_GET);
        return $tmp;
    }

    public function getFormData()
    {
        return $_POST;
    }

    public function getBody()
    {
        return @file_get_contents('php://input');
    }

    public function getFiles(): array
    {
        return $_FILES;
    }

    public function getMethod(): string
    {
        return $_SERVER["REQUEST_METHOD"];
    }

    public function getURI(): string
    {
        return $_SERVER["REQUEST_URI"];
    }

    public function isPost(): bool
    {
        return $this->getMethod() == "POST";
    }

    public function isGet(): bool
    {
        return $this->getMethod() == "GET";
    }

    public function isOptions(): bool
    {
        return $this->getMethod() == "OPTIONS";
    }

    public function isPut(): bool
    {
        return $this->getMethod() == "PUT";
    }

    public function isDelete(): bool
    {
        return $this->getMethod() == "DELETE";
    }
}
