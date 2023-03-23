<?php

namespace Tet;

use Exception;
use \Throwable;
use \stdClass;

use Tet\HTTP\Response;
use Tet\HTTP\Server;
use Tet\Routing\Router;
use Tet\Filesystem\File;

use Tet\HTTP\Client;
use Tet\Mail\Mailer;
use Tet\Database\MySQL;
use Tet\Filesystem\Filesystem;
use Tet\Security\Auth;

use Tet\Common\Collection;
use Tet\Common\Log;
use Tet\Common\Utils;


class Tet
{
    private Router $router;
    protected Collection $params;
    protected MySQL $mySQL;
    protected Auth $auth;
    protected Log $log;
    protected Mailer $mailer;

    public string $AccessControlAllowOrigin;
    public string $AccessControlAllowMethods;
    public string $AccessControlAllowHeaders;

    public function auth(): Auth
    {
        return $this->auth ?? $this->auth = new Auth;
    }

    public function log(): Log
    {
        return $this->log ?? $this->log = new Log;
    }

    public function params(): Collection
    {
        return $this->params ??  $this->params = new Collection;
    }

    public function mySQL(): MySQL
    {
        return $this->mySQL ?? $this->mySQL = new MySQL;
    }

    public function filesystem(): Filesystem
    {
        return new Filesystem;
    }

    public function server(): Server
    {
        return new Server;
    }

    public function client(): Client
    {
        return new Client;
    }

    public function utils(): Utils
    {
        return new Utils;
    }

    public function mailer(): Mailer
    {
        return $this->mailer ?? $this->mailer = new Mailer;
    }

    function router(): Router
    {
        return $this->router ?? $this->router = new Router();
    }

    function _require($path): bool
    {
        $file = new File($path);
        if (!$file->isExists()) throw new Exception("Required $path not found");
        require($path);
        return true;
    }

    public function _setErrorHandler()
    {
        set_error_handler(function ($code, $message, $file, $line) {
            $this->error_callback($code, $message, $file, $line);
            exit;
        });
    }

    public function _setExeptionHandler()
    {
        set_exception_handler(function (Throwable $e) {
            $this->error_callback($e->getCode(),  $e->getMessage(), $e->getFile(), $e->getLine());
        });
    }

    private function error_callback($code, $message, $file, $line)
    {
        $tmp = new stdClass;
        $tmp->message = $message;
        $tmp->code = $code;
        $tmp->file = $file;
        $tmp->line = $line;

        $srv = new Server;
        $tmp->request = $srv->getRequest();
        $tmp->url = $srv->getRequest()->getURI();
        $tmp->method = $srv->getRequest()->getMethod();


        $levels = [
            "0" => "qqq",
            "1" => "Error",
            "2" => "Warning",
            "4" => "Parse",
            "8" => "Notice"
        ];

        $this->log()->add($levels[$code], "$message in line $line of $file, $tmp->method, $tmp->url");
        return $this->sendResponse(new Response(json_encode($tmp), 200));
    }

    function _run(): bool
    {
        // сначала отрабатываем возможный запрос OPTIONS
        if (strtolower($this->server()->getRequest()->getMethod()) == "options") {
            return $this->sendResponse(new Response(" ", 200));
        }

        // попытка штатно отработать роутинг
        $route = $this->router->getCurrentRoute();

        if ($route) {
            $response = $route->getResponse();
            if (!$response) return true;
            return $this->sendResponse($response);
        }

        // попытка отработать дефолтный роут, если он был указан
        $route = $this->router->getDefaultRoute();
        if ($route) return $this->router->redirect($route->uri);

        // возврат ответа 404
        return $this->sendResponse(new Response(null, 404));
    }

    private function sendResponse(Response $response): bool
    {
        $response->headers->set('Access-Control-Allow-Origin', $this->AccessControlAllowOrigin);
        $response->headers->set('Access-Control-Allow-Methods', $this->AccessControlAllowMethods);
        $response->headers->set('Access-Control-Allow-Headers', $this->AccessControlAllowHeaders);
        return $this->server()->sendResponse($response);
    }
}
