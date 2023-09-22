<?php

namespace Tet\Common;

use Tet\Filesystem\Filesystem;

class Logger
{
    protected static ?string $filePath = null;

    const LVL_EMERGENCY = "Emergency";
    const LVL_ALERT = "Alert";
    const LVL_CRITICAL = "Critical";
    const LVL_ERROR = "Error"; //1
    const LVL_WARNING = "Warning"; //2
    const LVL_PARSE = "Parse"; //4
    const LVL_NOTICE = "Notice"; //8
    const LVL_INFO = "Info";
    const LVL_DEBUG = "Debug";


    public static function get(){
        return static::class;
    }

    public static function setFile(string $path)
    {
        self::$filePath = $path;
    }

    public static  function emergency($data)
    {
        self::add(self::LVL_EMERGENCY, $data);
    }

    public  static function alert($data)
    {
        self::add(self::LVL_ALERT, $data);
    }

    public  static function critical($data)
    {
        self::add(self::LVL_CRITICAL, $data);
    }

    public  static function error($data)
    {
        self::add(self::LVL_ERROR, $data);
    }

    public  static function warning($data)
    {
        self::add(self::LVL_WARNING, $data);
    }

    public  static function notice($data)
    {
        self::add(self::LVL_NOTICE, $data);
    }

    public  static function info($data)
    {
        self::add(self::LVL_INFO, $data);
    }

    public  static function  debug($data)
    {
        self::add(self::LVL_DEBUG, $data);
    }

    public  static function add(?string $level, ?string $message): bool
    {
        if (!self::$filePath) return false;

        $data = [
            "[" . date("Y-m-d H-i-s") . "]",
            "[$level]",
            "[$message]",
            "\r\n"
        ];

        $data = implode(" ", $data);

        return Filesystem::appendToFile(self::$filePath, $data);
    }
}
