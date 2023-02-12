<?php

namespace Tet\Common;

use Tet\Filesystem\Filesystem;

class Log
{
    protected string $filePath;

    const LVL_EMERGENCY = "Emergency";
    const LVL_ALERT = "Alert";
    const LVL_CRITICAL = "Critical";
    const LVL_ERROR = "Error"; //1
    const LVL_WARNING = "Warning"; //2
    const LVL_PARSE = "Parse"; //4
    const LVL_NOTICE = "Notice"; //8
    const LVL_INFO = "Info";
    const LVL_DEBUG = "Debug";

    public function setFile(string $path)
    {
        $this->filePath = $path;
    }

    public function emergency($data)
    {
        $this->add($this::LVL_EMERGENCY, $data);
    }

    public function alert($data)
    {
        $this->add($this::LVL_ALERT, $data);
    }

    public function critical($data)
    {
        $this->add($this::LVL_CRITICAL, $data);
    }

    public function error($data)
    {
        $this->add($this::LVL_ERROR, $data);
    }

    public function warning($data)
    {
        $this->add($this::LVL_WARNING, $data);
    }

    public function notice($data)
    {
        $this->add($this::LVL_NOTICE, $data);
    }

    public function info($data)
    {
        $this->add($this::LVL_INFO, $data);
    }

    public function  debug($data)
    {
        $this->add($this::LVL_DEBUG, $data);
    }

    public function add(string $level, string $message): bool
    {
        if (!$this->filePath) return false;

        $data = [
            "[" . date("Y-m-d H-i-s") . "]",
            "[$level]",
            "[$message]",
            "\r\n"
        ];

        $data = implode(" ", $data);

        return (new Filesystem)->appendToFile($this->filePath, $data);
    }
}
