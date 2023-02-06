<?php

namespace Tet\HTTP;

use Tet\Common\Collection;
use Tet\HTTP\Header;

class ClientRequest
{
    public ?string $method = null;
    public ?string $url = null;    
    public Collection $params;    
    public Headers $headers;
    public ?string $body = null;


    function __construct(?string $url = null)
    {
        $this->headers = new Headers;
        $this->params = new Collection;

        if ($url) $this->url = $url;
    }

    function getResponse(): Response
    {
        $response = new Response;
        $response->body = file_get_contents($this->createRequestQuery(), false, $this->createRequestContext());
        $response->code = http_response_code();
        $response->headers->add($this->getResponseHeaders($http_response_header ?? []));        
        return $response;
    }

    private function createRequestQuery(): string
    {
        return $this->url . "?" . http_build_query($this->params->toArray());
    }

    private function createRequestContext()
    {
        $headers = "";
        foreach ($this->headers->toArray() as $key => $value) {
            $headers .= "$key: $value\r\n";
        }

        return stream_context_create([
            'http' => [
                'ignore_errors' => true,
                'method' => $this->method,
                'header' => $headers,
                'content' => $this->body
            ]
        ]);
    }

    private function getResponseHeaders(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            if ($key > 0) {
                $header = $this->getResponseHeader($value);
                if ($header) $result[$header->name] = $header->value;
            }
        }
        return $result;
    }

    private function getResponseHeader(string $data): ?Header
    {
        $split_pos = strpos($data, ":");
        if ($split_pos === false) return null;
        $header = new Header;
        $header->name = substr($data, 0, $split_pos);
        $header->value = substr($data, $split_pos);
        return $header;
    }
}
