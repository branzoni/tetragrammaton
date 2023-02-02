<?php

namespace Tet\Filesystem;

use Tet\Common\Utils;
use Tet\Filesystem\Path;

class Filesystem
{
    function createDirectory(string $path): bool
    {
        if ((new Path($path))->isExists($path)) return true;
        return @mkdir($path, 0777, true);
    }

    function createFile(string $path, $data = null): bool
    {
        $file = new File($path);
        if (!$this->createDirectory($file->getDirname())) return false;

        $stream = fopen($path, "w");        
        if(!$stream) return false;
        if(!fwrite($stream, $data)) return false;
        if(!fclose($stream)) return false;
   
        return true;
    }

    function getPath(string $path): Path
    {
        return new Path($path);
    }

    function getFile(string $path):File
    {
        return new File($path);
    }

    function getDirectory(string $path): Directory
    {
        return new Directory($path);
    }

    function getSystemTempDir(): string
    {
        return sys_get_temp_dir();
    }

    function saveUploadedFile(string $source, string $destination)
    {
        $file = new File($destination);
        if (!$this->createDirectory($file->getDirname())) return false;

        return move_uploaded_file($source, $destination);
    }

    function getCurPath(): string
    {
        return __DIR__;
    }

    function getFreeSpace(): float
    {
        return disk_free_space("/");
    }

    function getTotalSpace(): float
    {
        return disk_total_space("/");
    }

    function getUsedSpace(): float
    {
        $freespace = $this->getFreeSpace();
        $totalspace = $this->getTotalSpace();
        return $totalspace - $freespace;
    }

    function getSpaceInfo($format = "gb")
    {
        $freespace = $this->getFreeSpace();
        $totalspace = $this->getTotalSpace();
        $usedspace = $this->getUsedSpace();

        return [
            "total" => Utils::getFormatedBytes($totalspace, $format),
            "use" => Utils::getFormatedBytes($usedspace, $format),
            "free" => Utils::getFormatedBytes($freespace, $format),
            "format" => $format
        ];
    }
}
