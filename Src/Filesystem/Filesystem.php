<?php

namespace Tet\Filesystem;

use Tet\Common\Utils;
use Tet\Filesystem\Path;

class Filesystem
{
    public static function createDirectory(string $path): void
    {
        if ((new Path($path))->isExists($path)) return;
        if (@mkdir($path, 0777, true)) {
			throw new \Exception("Create directory '$path' failed");
		};
    }

	public static function createFile(string $path, $data = null): void
    {
        $file = new File($path);
        self::createDirectory($file->getDirname()));

        $stream = fopen($path, "w");
        if (!$stream) throw new \Exception("File opening '$path' failed");
        if (!fwrite($stream, $data)) throw new \Exception("File writing '$path' failed");
        if (!fclose($stream)) throw new \Exception("File closing '$path' failed");;
    }

	public static function appendToFile(string $path, $data = null): void
    {
        self::createFile($path, $data);
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
