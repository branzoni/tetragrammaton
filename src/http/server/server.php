<?php

namespace Tetra\HTTP;

use Tetra\HTTP\Server\Request;

class Server
{
    private $request;

    function __construct()
    {
        $this->request = new Request;
    }

    function request()
    {
        return $this->request;
    }

    function protocol()
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

    function host()
    {
        $tmp = $_SERVER["HTTP_HOST"] ?? "";
        $tmp = strtolower($tmp);
        return $tmp;
    }

    function port()
    {
        return $_SERVER["SERVER_PORT"];
    }

    function name()
    {
        return $_SERVER["SERVER_NAME"];
    }

    function root($local = true)
    {
        if ($local) return $_SERVER['DOCUMENT_ROOT'];

        $host = $this->host();
        if ($host == "localhost") return $this->protocol() . "://" . $host . ":" . $this->port();
        if ($host = "localhost") return $this->protocol() . "://" . $host;
    }

    function send_response($content = "", $code = 200, $headers = [])
    {
        http_response_code($code);

        foreach ($headers as $header) {
            header($header);
        }

        echo $content;
    }

    // отправить клиенту данные как файл на скачивание
    function send_file($file_name, $data): Bool
    {

        if (ob_get_level()) ob_end_clean();

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $file_name);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . strlen($data));

        echo $data;

        return true;
    }
}
