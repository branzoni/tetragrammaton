<?php

namespace Tet;

use Tet\Path;

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

        $stream = @fopen($path, "w");

        if($data)
        {
            @fwrite($stream, $data);
        }

        @fclose($stream);
   
        return true;
    }

    function getPath(string $path): Path
    {
        return new Path($path);
    }

    function getFile(string $path)
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

        return @move_uploaded_file($source, $destination);
    }

    function getCurPath(): string
    {
        return __DIR__;
    }
}
