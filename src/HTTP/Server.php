<?php

namespace Tet\HTTP;

class Server
{

    public function getRequest()
    {
        return new ServerRequest;
    }

    public function getIP()
    {
        return $_SERVER['SERVER_ADDR'];
    }
    public function getProtocol()
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

    public function getHost()
    {
        $tmp = $_SERVER["HTTP_HOST"] ?? "";
        $tmp = strtolower($tmp);
        return $tmp;
    }

    public function getPort()
    {
        return $_SERVER["SERVER_PORT"];
    }

    public function getName()
    {
        return $_SERVER["SERVER_NAME"];
    }



    public function getRoot($local = true)
    {
        if ($local) return $_SERVER['DOCUMENT_ROOT'];

        $host = $this->getHost();
        if ($host == "localhost") return $this->getProtocol() . "://" . $host . ":" . $this->getPort();
        //if ($host = "localhost") return $this->getProtocol() . "://" . $host;
    }

    function getRequestedURI(): string
    {
        return $_SERVER['REQUEST_URI'];
    }

    //function sendResponse(string $body = "", $code = 200, $headers = [])

    function sendResponse($content = "", $code = 200, $headers = [])
    {


        http_response_code($code);

        foreach ($headers as $header) {
            header($header);
        }

        echo $content;
    }

    // отправить клиенту данные как файл на скачивание
    function sendFile($file_name, $data): Bool
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
