<?php

namespace Tet\HTTP;

class Server
{
    public function getRequest(): ServerRequest
    {
        return new ServerRequest;
    }

    public function getIP(): string
    {
        return $_SERVER['SERVER_ADDR'];
    }
    public function getProtocol(): string
    {
        $tmp = $_SERVER["SERVER_PROTOCOL"];
        $tmp = explode("/", $tmp);
        $tmp = $tmp[0];
        $tmp = strtolower($tmp);

        if (isset($_SERVER["HTTPS"])) {
            if ($_SERVER["HTTPS"] != "") $tmp = "https";
        }

        return $tmp;
    }

    public function getHost(): string
    {
        $tmp = $_SERVER["HTTP_HOST"] ?? "";
        $tmp = strtolower($tmp);
        return $tmp;
    }

    public function getPort(): string
    {
        return $_SERVER["SERVER_PORT"];
    }

    public function getName(): string
    {
        return $_SERVER["SERVER_NAME"];
    }

    public function getRoot($local = true): string
    {
        $root = $_SERVER['DOCUMENT_ROOT'];
        if ($local) return $root;
        return basename($root);
    }

    function getRequestedURI(): string
    {
        return $_SERVER['REQUEST_URI'];
    }

    function sendResponse(Response $response, bool $clean_buffer = false): bool
    {
        if ($clean_buffer && ob_get_level())  ob_end_clean();

        http_response_code($response->code);

        foreach ($response->headers->toArray() as $key => $header) {
            header("$key:$header");
        }
        
        echo $response->body;

        return true;
    }

    // отправить клиенту данные как файл на скачивание
    function sendDataAsFile(string $filename, string $data): bool
    {
        $response = new Response;
        $response->code = 200;
        $response->headers->setContentDescription('File Transfer');
        $response->headers->setContentType('application/octet-stream');
        $response->headers->setContentDisposition('attachment; filename=' . $filename);
        $response->headers->setContentTransferEncoding('binary');
        $response->headers->setExpires('0');
        $response->headers->set('Cache-Control', 'must-revalidate');
        $response->headers->setPragma('public');
        $response->headers->setContentLength(strlen($data));
        $response->body = $data;
        $this->sendResponse($response, true);

        return true;
    }
}
