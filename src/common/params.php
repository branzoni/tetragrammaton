<?php

namespace Tetra;

use Exception;

/**
 * Обеспечивает единообразную работу с ассоциированными массивами:
 * - установка
 * - получение
 * - проверка наличия ключа
 * - количество ключей
 * @author Sergey Afanasyev <sergey.v.afanasyev@gmail.com>
 */
class Params
{
    private $data = [];

    
    function set($propname, $value):bool
    {
        $this->data[$propname] = $value;
        return true;
    }

    function get($propname):mixed
    {
        if (!$this->is_set($propname)) throw new Exception("unknown property name '$propname'");
        return $this->data[$propname];
    }

    /**
     *  Проверяет наличие в массиве элемента с указанным именем
     */
    function is_set($propname):bool
    {
        return array_key_exists($propname, $this->data);
    }

    /**Функция проверяет наличие параметра и что его значение находится в списке, если один из пунктов не выполняется, возвращает false*/
    function is_valid($propname, $values):bool
    {
        if (!$this->is_set($propname)) return false;
        if (array_search($this->$propname, $values) === false)  return false;
        return true;
    }

    /**
     *  Возвращает количество свойств в массиве свойств
     */
    function count():Int
    {
        return count($this->data);
    }

    /**
     *  Заполняет массив свойсв данными, переданными в параметре
     */
    function load($data):Bool
    {
        $this->data = $data;
        return true;
    }


    function to_json():String
    {
        return json_encode($this->data);
    }

    function keys():Array
    {
        return array_keys($this->data);
    }
}
