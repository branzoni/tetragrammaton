<?php

namespace Tet;

interface DbInterface
{
    public function open($address, $name, $user, $password);
    public function close();
    public function error();
    public function connected();
    public function execute(string $query): Result;
    public function table(?string $name = null);
    function select(string $tablename, array $values, $where = "", $orderBy = ""): Result;
    function insert(string $tablename, array $values): Result;
    function update(string $tablename, array $values, string $where): Result;
    function delete(string $tablename, string $where): Result;
    function getTableList(): array;
    function getTableScheme(string $tablename): TableScheme;
    function createTableSchemeClass(string $destination, string $tablename):bool;
}
