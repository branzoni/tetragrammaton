<?php

namespace Tetra;

class FileSystem
{
    function mkdir($path)
    {
        if (file_exists($path)) return true;
        return @mkdir($path, 0777, true);
    }

    function rename($from, $to)
    {
        return @rename($from, $to);
    }

    function sys_get_temp_dir()
    {
        return sys_get_temp_dir();
    }

    function save_remote_file($url, $to)
    {
        $file = new File($to);
        if (!$this->mkdir($file->dirname())) return false;

        // скачиваем файл по ссылке и сохраняем по указанному пути

        // считываем содержимое исходного файла
        $source = fopen($url, "rb");
        if (!$source) return false;

        // открываем на запись целевой файл
        $destination = fopen($to, "wb");
        if (!$destination) return false;

        // пишем данные блоками
        while (!feof($source)) {
            fwrite($destination, fread($source, 4096));
        }

        fclose($destination);
        fclose($source);

        return true;
    }

    function save_uploaded_file($from, $to)
    {
        $file = new File($to);
        if (!$this->mkdir($file->dirname())) return false;

        return @move_uploaded_file($from, $to);
    }

    // возвращает массив с путями к файлам из указанной папки и соответствующие маске
    function get_files($path, $patterns): array
    {
        $files = [];
        foreach ($patterns as $pattern) $files = array_merge($files, $this->glob_recursive($path . $pattern));
        return $files;
    }

    // получаем файлы в выбранной папке, включая подпапки
    private function glob_recursive($pattern)
    {
        // получаем файлы в выбранном каталоге
        $tmp = glob($pattern, 0);

        // получаем список подпапок
        $sub_folders = glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT);

        // рекурсивно перебираем подпапки
        foreach ($sub_folders as $folder) {
            $tmp = array_merge($tmp, $this->glob_recursive($folder . '/' . basename($pattern)));
        }

        // отдаем результат
        return $tmp;
    }
}
