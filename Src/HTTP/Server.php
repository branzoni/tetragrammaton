<?php

namespace Tet\HTTP;

class Server
{
    public static function getRequest(): ServerRequest
    {
        return new ServerRequest;
    }

    public static function getIP(): string
    {
        return $_SERVER["SERVER_ADDR"];
    }
    public static function getProtocol(): string
    {
        $tmp = $_SERVER["SERVER_PROTOCOL"];
        $tmp = explode("/", $tmp);
        $tmp = $tmp[0];
        $tmp = strtolower($tmp);

        if (!isset($_SERVER["HTTPS"])) return $tmp;
        return "https";
    }

    public static function getHost(): string
    {
        $tmp = $_SERVER["HTTP_HOST"] ?? "";
        $tmp = strtolower($tmp);
        return $tmp;
    }

    public static function getPort(): string
    {
        return $_SERVER["SERVER_PORT"];
    }

    public static function getName(): string
    {
        return $_SERVER["SERVER_NAME"];
    }

    public static function getRoot($local = true): string
    {
        $root = $_SERVER["DOCUMENT_ROOT"];
        if ($local) return $root;
        return basename($root);
    }

    public static  function getRequestedURI(): string
    {
        return $_SERVER["REQUEST_URI"];
    }

    static function sendResponse(Response $response, bool $clean_buffer = false): bool
    {
        if ($clean_buffer && ob_get_level())  ob_end_clean();

        http_response_code($response->code);

        foreach ($response->headers->toArray() as $key => $header) {
            header("$key:$header");
        }

        //if ($response->code <> 200) return true;
        echo $response->body;

        return true;
    }

    // отправить клиенту данные как файл на скачивание
    static function sendDataAsFile(string $filename, string $data, $type = "application/octet-stream"): bool
    {
        $response = new Response;
        $response->code = 200;
        $response->headers->setContentDescription('File Transfer');
        $response->headers->setContentType($type);
        $response->headers->setContentDisposition('attachment; filename=' . $filename);
        $response->headers->setContentTransferEncoding('binary');
        $response->headers->setExpires('0');
        $response->headers->set('Cache-Control', 'must-revalidate');
        $response->headers->setPragma('public');
        $response->headers->setContentLength(strlen($data));
        $response->body = $data;
        self::sendResponse($response, true);

        return true;
    }

    public static function createResponse(int $code): Response
    {
        return new Response(null, $code);
    }

    public static function createQuickResponse(bool $value):Response
    {
        if(!$value) return new Response(null, 500);
        return new Response(null, 200);
    }

    public static function sendAsJson(string $data): Response
    {
        $response = new Response();
        $response->headers->setContentType("application/json");
        $response->body = $data;
        return $response;
    }
}
