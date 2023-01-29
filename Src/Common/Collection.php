<?php

namespace Tet;

class Collection
{
    protected array $values = [];

    function set(string $name, $value)
    {
        $this->values[$name] = $value;
    }

    function get(string $name)
    {
        return $this->values[$name];
    }

    function add(...$values)
    {
        if (count($values) == 1) $values = $values[0];

        foreach ($values as $name => $value) {
            $this->set($name, $value);
        }
    }

    function getCount(): int
    {
        return count($this->values);
    }

    function getKeys(): array
    {
        return array_keys($this->values ?? []);
    }

    function toJSON(): string
    {
        return json_encode($this->values ?? []);
    }

    function toArray(): array
    {
        return $this->values ?? [];
    }

    function has(string $name): bool
    {
        return $this->isExists($name);
    }

    /**
     *  Проверяет наличие в массиве элемента с указанным именем
     */
    function isSet(string $name): bool
    {
        return isset($this->values[$name]);
    }

    function isExists(string $name): bool
    {
        return array_key_exists($name, $this->values);
    }

    /**Функция проверяет наличие параметра и что его значение находится в списке, если один из пунктов не выполняется, возвращает false*/
    function isOneOf(string $name, array $names): bool
    {
        return array_search($this->$name, $names) !== false;
    }

    function forEach($callback)
    {
        $count = $this->getCount();
        $index = 0;
        foreach ($this->values as $key => $value) {
            $index += 1;
            $callback($key, $value, $count, $index);
        }
    }
}
