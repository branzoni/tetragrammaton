<?php

namespace Tet\Database;

use Tet\Database\MySQL;
use Tet\Database\TypeDef;
use Tet\Database\ColumnCollection;
use Tet\Database\ColumnDef;

class Table
{
    private MySQL $mySQL;
    private string $name;

    function __construct(MySQL $mySQL, string $name)
    {
        $this->mySQL = $mySQL;
        $this->name = $name;
    }

    function getName()
    {
        return $this->name;
    }

    function getColumnsInfo2(): ColumnCollection
    {
        $data = $this->mySQL->execute("DESCRIBE $this->name");
        if (!$data) return new ColumnCollection([]);

        $columns = [];
        foreach ($data as $value) {
            $column = new ColumnDef($value["Field"], new TypeDef(strtoupper($value["Type"]), null));
            $column->setNotNull($value["Null"] == "NO");
            $column->setAutoincriment($value["Extra"] == "auto_increment");
            $column->setDefault($value["Default"]);
            $columns[$column->getName()] = $column;
        }

        return new ColumnCollection($columns);
    }

    function getColumnsInfo(): ColumnCollection
    {
        $data = $this->mySQL->execute("SELECT * FROM information_schema.COLUMNS WHERE TABLE_NAME = '$this->name' and TABLE_SCHEMA = DATABASE()");
        if (!$data) return new ColumnCollection([]);

        $columns = [];
        foreach ($data as $value) {
            $value = (object) $value;
            $type = new TypeDef($value->DATA_TYPE, null);
            $col = new ColumnDef($value->COLUMN_NAME, $type);
            if ($value->IS_NULLABLE == "NO") $col->setNotNull();
            if ($value->COLUMN_KEY == "UNI") $col->setUnique();
            if ($value->COLUMN_KEY == "PRI") $col->setPrimaryKey();
            if ($value->EXTRA == "auto_increment") $col->setAutoincriment();
            $col->setDefault($value->COLUMN_DEFAULT);
            $columns[$col->getName()] = $col;
        }

        return new ColumnCollection($columns);
    }

    function getColumnDef(string $column): ColumnDef
    {
        return $this->getColumnsInfo()->get($column);
    }

    function setCharset(string $charset = "utf8", string $collate = "utf8_general_ci"): bool
    {
        return $this->mySQL->execute("ALTER TABLE `$this->name` CONVERT TO CHARACTER SET $charset COLLATE  $collate;");
    }

    function hasColumn(string $name): bool
    {
        return boolval($this->mySQL->execute("SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE table_schema = DATABASE() AND table_name='$this->name' AND column_name='$name'"));
    }

    function  hasIndex(string $name): bool
    {
        return boolval($this->mySQL->execute("SELECT 1 res FROM INFORMATION_SCHEMA.STATISTICS WHERE table_schema = DATABASE() AND table_name='$this->name' AND index_name='$name'"));
    }

    function hasPrimaryKey(): bool
    {
        return boolval($this->mySQL->execute("SELECT * FROM INFORMATION_SCHEMA.STATISTICS WHERE table_schema = DATABASE() AND table_name='$this->name' and INDEX_NAME = 'PRIMARY'"));
    }

    function dropColumn(string $name): bool
    {
        return $this->mySQL->execute("ALTER TABLE $this->name DROP COLUMN `$name`;");
    }

    function dropIndex(string $name): bool
    {
        return $this->mySQL->execute("DROP INDEX $name ON $this->name");
    }

    function dropPrimaryKey(): bool
    {
        return $this->mySQL->execute("ALTER TABLE $this->name DROP PRIMARY KEY");
    }

    function addColumn(string $name, string $type): bool
    {
        return $this->mySQL->execute("ALTER TABLE $this->name ADD $name $type;");
    }

    function addIndex(string $name, string $type): bool
    {
        return $this->mySQL->execute("ALTER TABLE $this->name ADD $type INDEX $name($name)");
    }

    function addUniqueIndex(string $name): bool
    {
        return $this->addIndex($name, "UNIQUE");
    }

    function addFulltextIndex(string $name): bool
    {
        return $this->addIndex($name, "FULLTEXT");
    }

    function addSpatialIndex(string $name): bool
    {
        return $this->addIndex($name, "SPATIAL");
    }

    function renameColumn(string $curName, string $newName): bool
    {
        return $this->mySQL->execute("ALTER TABLE $this->name RENAME COLUMN $curName to $newName;");
    }

    function setColumnDataType(string $name, string $type): bool
    {
        return $this->mySQL->execute("ALTER TABLE $this->name MODIFY COLUMN `$name` $type;");
    }

    function setAutoincrimentValue($value = 1): bool
    {
        return $this->mySQL->execute("ALTER TABLE $this->name AUTO_INCREMENT = $value");
    }

    function setColumnDefaultValue(string $name, $value): bool
    {
        return $this->mySQL->execute("ALTER TABLE $this->name ALTER COLUMN $name SET DEFAULT $value");
    }

    function addAutoincriment(string $column): bool
    {
        $column = $this->getColumnDef($column);
        $column->setAutoincriment();
        $column->setPrimaryKey();
        return $this->mySQL->execute("ALTER TABLE $this->name MODIFY $column");
    }

    function trancate(): bool
    {
        return $this->mySQL->execute("TRUNCATE TABLE $this->name;");
    }

    function duplicateRow(string $idColumnName, string $idColumnValue): bool
    {
        $columns = implode(", ", $this->getColumnsInfo()->getKeys());
        return $this->mySQL->execute("INSERT INTO `$this->name` ($columns) SELECT " . str_replace($idColumnName, "NULL", $columns) . " FROM `$this->name` WHERE `$idColumnName` = $idColumnValue");
    }

    function modifyColumn(ColumnDef $column): bool
    {
        $this->setColumnDataType($column->getName(), $column->getType());

        if ($column->isAutoincriment()) {
            if ($this->hasPrimaryKey()) $this->dropPrimaryKey();
            $this->addAutoincriment($column->getName());
        };

        if ($this->hasIndex($column->getName())) $this->dropIndex($column->getName());
        if ($column->isUnique()) $this->addUniqueIndex($column->getName());

        if ($column->hasDefault()) $this->setColumnDefaultValue($column->getName(), $column->getDefault());

        return true;
    }

    function addColumnsFromSchema(TableDef $tableDef): bool
    {
        $tableDef->columns->forEach(function ($key, ColumnDef $column) {

            if ($this->getColumnsInfo()->has($column->getName()))  $this->modifyColumn($column);
            else $this->addColumn($column->getName(), $column->getType());
        });

        return true;
    }

    function deleteOutSchemaColumns(TableDef $tableDef): bool
    {
        $new_columns = $tableDef->columns->getKeys();
        $cur_columns = $this->getColumnsInfo()->getKeys();
        $unneededColumns = array_diff($cur_columns, $new_columns);

        foreach ($unneededColumns as $key => $column) {
            $this->dropColumn($column);
        }
        return true;
    }

	function addUniqueIndexesFromSchema(TableDef $tableDef)
	{
		foreach ($tableDef->indexes as $index) {
			$indexName = 'unique_' . implode("_", $index);
			$indexValue = implode(",", $index);
			$this->dropIndex($indexName);
			$this->mySQL->execute("ALTER TABLE $this->name ADD UNIQUE INDEX `$indexName` ($indexValue)");
		}
		////ALTER TABLE `itisme`.`user` ADD UNIQUE INDEX `INDEX_DDLH` (`login`,`service_uid`)
	}
}
