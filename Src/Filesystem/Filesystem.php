<?php

namespace Tet\Filesystem;

use Tet\Common\Utils;
use Tet\Filesystem\Path;

class Filesystem
{
    public static function createDirectory(string $path): bool
    {
        if ((new Path($path))->isExists($path)) return true;
        return @mkdir($path, 0777, true);
    }

	public static function createFile(string $path, $data = null): bool
    {
        $file = new File($path);
        if (!self::createDirectory($file->getDirname())) return false;

        $stream = fopen($path, "w");
        if (!$stream) return false;
        if (!fwrite($stream, $data)) return false;
        if (!fclose($stream)) return false;

        return true;
    }

	public static function appendToFile(string $path, $data = null): bool
    {
        $file = new File($path);
        if (!self::createDirectory($file->getDirname())) {
			throw new \Exception("create directory \"{$file->getDirname()}\" failed");
		};

        $stream = fopen($path, "a+");
        if (!$stream) return false;
        if (!fwrite($stream, $data)) return false;
        if (!fclose($stream)) return false;

        return true;
    }

	public static function getPath(string $path): Path
    {
        return new Path($path);
    }

	public static function getFile(string $path): File
    {
        return new File($path);
    }

	public static function getDirectory(string $path): Directory
    {
        return new Directory($path);
    }

	public static function getSystemTempDir(): string
    {
        return sys_get_temp_dir();
    }

	public static function saveUploadedFile(string $source, string $destination)
    {
        $file = new File($destination);
        if (!self::createDirectory($file->getDirname())) return false;

        return move_uploaded_file($source, $destination);
    }

	public static function getCurPath(): string
    {
        return __DIR__;
    }

	public static function getFreeSpace(): float
    {
        return disk_free_space("/");
    }

	public static function getTotalSpace(): float
    {
        return disk_total_space("/");
    }

	public static function getUsedSpace(): float
    {
        $freespace = self::getFreeSpace();
        $totalspace = self::getTotalSpace();
        return $totalspace - $freespace;
    }

	public static function getSpaceInfo($format = "gb"): array
    {
        $freespace = self::getFreeSpace();
        $totalspace = self::getTotalSpace();
        $usedspace = self::getUsedSpace();

        return [
            "total" => Utils::getFormatedBytes($totalspace, $format),
            "use" => Utils::getFormatedBytes($usedspace, $format),
            "free" => Utils::getFormatedBytes($freespace, $format),
            "format" => $format
        ];
    }
}
