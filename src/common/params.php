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

    function __set($propname, $value)
    {
        throw new Exception("try to set unknown property name '$propname'");
    }

    function __get($propname)
    {        
        throw new Exception("try to get unknown property name '$propname'");
    }

    function set($propname, $value)
    {
        $this->data[$propname] = $value;
    }

    function get($propname)
    {
        if (!$this->is_set($propname)) throw new Exception("unknown property name '$propname'");
        return $this->data[$propname];
    }

    /**
     *  Проверяет наличие в массиве элемента с указанным именем
     */
    function is_set($propname)
    {
        return array_key_exists($propname, $this->data);
    }

    /**Функция проверяет наличие параметра и что его значение находится в списке, если один из пунктов не выполняется, возвращает false*/
    function is_valid($propname, $values)
    {
        if (!$this->is_set($propname)) return false;        
        if (array_search($this->$propname, $values) === false)  return false;
        return true;
    }

    /**
     *  Возвращает количество свойств в массиве свойств
     */
    function count()
    {
        return count($this->data);
    }

    /**
     *  Заполняет массив свойсв данными, переданными в параметре
     */
    function load($data)
    {
        $this->data = $data;
    }
}
