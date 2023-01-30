<?php

namespace Tet;

class Directory extends Path
{
    // возвращает массив с путями к файлам из указанной папки и соответствующие маске
    function getFileList($patterns = ["*.*"], bool $recursive = true): array
    {
        $files = [];
        switch (gettype($patterns)) {
            case "array";
                foreach ($patterns as $pattern) {
                    $files = array_merge($files, (new Directory($this->path))->getFileList($pattern, $recursive));
                }
                break;
            case "string":
                // получаем файлы в выбранном каталоге                
                $files = glob($this->path . "/" . $patterns, 0);
                if (!$recursive) return $files;

                // получаем список подпапок
                $sub_folders = $this->getDirectoryList();



                // рекурсивно перебираем подпапки
                foreach ($sub_folders as $folder) {
                    $files = array_merge($files, (new Directory($folder))->getFileList($patterns, $recursive));
                }
                break;
        }

        return $files;
    }

    function getDirectoryList(bool $recursive = true): array
    {
        // получаем подпапки в выбранном каталоге                
        $folders = glob($this->path . '/*', GLOB_ONLYDIR | GLOB_NOSORT);

        // получаем подпапки
        foreach ($folders as $folder) {
            if (!$recursive) return $folders;
            $sub_folders = (new Directory($folder))->getDirectoryList($recursive);
            $folders = array_merge($folders, $sub_folders);
        }

        return $folders;
    }

    function getFreeSpace(): float
    {
        return disk_free_space($this->path);
    }

    function getTotalSpace(): float
    {
        return disk_total_space($this->path);
    }

    function getUsedSpace(): float
    {
        $freespace = $this->getFreeSpace();
        $totalspace = $this->getTotalSpace();
        return $totalspace - $freespace;
    }

    function getSpaceInfo($format = "gb")
    {
        $freespace = $this->getFreeSpace($this->path);
        $totalspace = $this->getTotalSpace($this->path);
        $usedspace = $this->getUsedSpace($this->path);

        return [
            "path" => $this->path,
            "total" => $this->getFormatedSize($totalspace, $format),
            "use" => $this->getFormatedSize($usedspace, $format),
            "free" => $this->getFormatedSize($freespace, $format),
            "format" => $format
        ];
    }

    function getFormatedSize($value, $format = "gb")
    {
        $tmp = $value;
        if ($format == "kb")  $tmp = $tmp / 1024;
        if ($format == "mb") $tmp = $tmp / 1024 / 1024;
        if ($format == "gb") $tmp = $tmp / 1024 / 1024 / 1024;
        $tmp = round($tmp, 2);
        return $tmp;
    }
}
