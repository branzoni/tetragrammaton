<?php

namespace Tet;

use Tet\HTTP\Server;

class Path
{
    protected string $path;

    function __construct($path)
    {
        $this->path = realpath($path);
    }

    function __toString()
    {
        return $this->path;
    }

    /*
     * путь к родительской папке файла
    */
    function getDirname(): string
    {
        $tmp = pathinfo($this->path, PATHINFO_DIRNAME);
        return $tmp;
    }


    /*
     * имя файла без расширения
    */
    function getFilename(): string
    {
        return pathinfo($this->path, PATHINFO_FILENAME);
    }

    /*
     * имя файла с расширением
    */
    function getBasename(): string
    {
        return pathinfo($this->path, PATHINFO_BASENAME);
    }

    function isRemote(): bool
    {
        $string = mb_strtolower($this->path);
        if (preg_match('/^(https|http|ftp)\:\/\//', $string) === 1) return true;
        if (preg_match('/^(localhost)/', $string) === 1) return true;
        if (preg_match('/\/.*/', $string) === 1) return true; //подходит только для файлов и страниц, где в адресе явно прописан документ вплоть до расширения
        return false;
    }

    function isLocal(): bool
    {
        $string = mb_strtolower($this->path);
        if (preg_match('/^[a-z]:\\\/', $string) === 1) return true;
        if (preg_match('/.*\\\/', $string) === 1) return true;
    }

    function getLocalPath(): string
    {
        return $this->getPath(true);
    }

    function getRemotePath(): string
    {
        return $this->getPath(false);
    }

    function getRelativePath(): string
    {
        $server = new Server;
        $tmp = $this->path;
        $tmp = str_replace("\\", "/", $tmp);
        $tmp = str_replace([$server->getRoot(), $server->getRoot(false)], "", $tmp);
        return $tmp;
    }

    function getRealPath(): string
    {
        return realpath($this->path);
    }

    private function getPath(bool $local = true): string
    {
        $tmp = $this->getRelativePath();
        $tmp = (new Server)->getRoot($local) . $tmp;
        return $tmp;
    }

    function unlink(): bool
    {
        if (!$this->isExists()) return true;
        return @unlink($this->path);
    }

    function delete(): bool
    {
        return $this->unlink();
    }

    function rename(string $destination): bool
    {
        return @rename($this->path, $destination);
    }

    function move(string $destination): bool
    {
        return $this->rename($destination);
    }


    function isExists(): bool
    {
        return (file_exists($this->path));
    }


    function copy(string $destination): bool
    {
        $file = new File($this->path);
        if (!(new FileSystem)->createDirectory($file->getDirname())) return false;

        // скачиваем файл по ссылке и сохраняем по указанному пути

        // считываем содержимое исходного файла
        $source = fopen($this->path, "rb");
        if (!$source) return false;

        // открываем на запись целевой файл
        $destination = fopen($destination, "wb");
        if (!$destination) return false;

        // пишем данные блоками
        while (!feof($source)) {
            fwrite($destination, fread($source, 4096));
        }

        fclose($destination);
        fclose($source);

        return true;
    }

    function getSegments(): array
    {
        $tmp = $this->path;
        if ($tmp[0] = "/") $tmp = substr($tmp, 1);
        return explode("/", $tmp);
    }

    function getSegmentCount(): int
    {
        return count($this->getSegments());
    }
}
