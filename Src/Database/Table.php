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

    function setCharset(string $charset = "utf8", string $collate = "utf8_general_ci"): void
    {
        $this->mySQL->execute("
			ALTER TABLE `$this->name`
			    CONVERT TO CHARACTER SET $charset COLLATE  $collate;
		");
    }

    function hasColumn(string $name): bool
    {
        return boolval($this->mySQL->execute("
			SELECT *
			FROM INFORMATION_SCHEMA.COLUMNS
		 	WHERE 
		 	    table_schema = DATABASE() AND
		 	    table_name = '$this->name' AND
		 	    column_name='$name'
		"));
    }

    function  hasIndex(string $name): bool
    {
        return boolval($this->mySQL->execute("
			SELECT 1 res 
			FROM INFORMATION_SCHEMA.STATISTICS
			WHERE
			    table_schema = DATABASE() AND
			    table_name='$this->name' AND
			    index_name like'$name'
		"));
    }

    function hasPrimaryKey(): bool
    {
        return boolval($this->mySQL->execute("
			SELECT * 
			FROM INFORMATION_SCHEMA.STATISTICS 
			WHERE 
			    table_schema = DATABASE() AND
			    table_name='$this->name' AND
			    INDEX_NAME = 'PRIMARY'
			"));
    }

    function dropColumn(string $name): void
    {
        $this->mySQL->execute("
			ALTER TABLE $this->name
				DROP COLUMN IF EXISTS `$name`;
		 ");
    }

    function dropIndex(string $name): void
    {
        $this->mySQL->execute("
			DROP INDEX $name ON $this->name
		");
    }

    function dropPrimaryKey(): void
    {
        $this->mySQL->execute("
			ALTER TABLE $this->name
				DROP PRIMARY KEY
		");
    }

    function addColumn(string $name, string $type): void
    {
        $this->mySQL->execute("
			ALTER TABLE $this->name
			    ADD $name $type
			");
    }

    function addIndex(string $name, string $type): void
    {
        $this->mySQL->execute("
			ALTER TABLE $this->name
			    ADD $type INDEX $name($name)
		");
    }

    function addUniqueIndex(string $name): void
    {
        $this->addIndex($name, "UNIQUE");
    }

    function addFulltextIndex(string $name): void
    {
        $this->addIndex($name, "FULLTEXT");
    }

    function addSpatialIndex(string $name): void
    {
        $this->addIndex($name, "SPATIAL");
    }

    function renameColumn(string $curName, string $newName): void
    {
        $this->mySQL->execute("
			ALTER TABLE $this->name
				RENAME COLUMN $curName to $newName;
			");
    }

    function setColumnDataType(string $name, string $type): void
    {
        $this->mySQL->execute("
			ALTER TABLE $this->name 
			    MODIFY COLUMN `$name` $type;
			");
    }

    function setAutoincrimentValue($value = 1): void
    {
        $this->mySQL->execute("
			ALTER TABLE $this->name
				AUTO_INCREMENT = $value
		");
    }

    function setColumnDefaultValue(string $name, $value): void
    {
        $this->mySQL->execute("
			ALTER TABLE $this->name
				ALTER COLUMN $name
				    SET DEFAULT $value
		");
    }

    function addAutoincriment(string $column): void
    {
        $column = $this->getColumnDef($column);
        $column->setAutoincriment();
        $column->setPrimaryKey();
        $this->mySQL->execute("
			ALTER TABLE $this->name
			    MODIFY $column
		");
    }

    function trancate(): void
    {
        $this->mySQL->execute("
        	TRUNCATE TABLE $this->name
		");
    }

    function duplicateRow(string $idColumnName, string $idColumnValue): void
    {
        $columns = implode(", ", $this->getColumnsInfo()->getKeys());
        $this->mySQL->execute("
			INSERT INTO `$this->name` ($columns)
				SELECT " . str_replace($idColumnName, "NULL", $columns) . "
				FROM `$this->name`
			 	WHERE `$idColumnName` = $idColumnValue
		");
    }

    function modifyColumn(ColumnDef $column): void
    {
        $this->setColumnDataType($column->getName(), $column->getType());

        if ($column->isAutoincriment()) {
            if ($this->hasPrimaryKey()) $this->dropPrimaryKey();
            $this->addAutoincriment($column->getName());
        };

        if ($this->hasIndex($column->getName())) $this->dropIndex($column->getName());
        if ($column->isUnique()) $this->addUniqueIndex($column->getName());

        if ($column->hasDefault()) $this->setColumnDefaultValue($column->getName(), $column->getDefault());
    }

    function addColumnsFromSchema(TableDef $tableDef): void
    {
        $tableDef->columns->forEach(function ($key, ColumnDef $column) {

            if ($this->getColumnsInfo()->has($column->getName()))  $this->modifyColumn($column);
            else $this->addColumn($column->getName(), $column->getType());
        });
    }

    function deleteOutSchemaColumns(TableDef $tableDef): void
    {
        $new_columns = $tableDef->columns->getKeys();
        $cur_columns = $this->getColumnsInfo()->getKeys();
        $unneededColumns = array_diff($cur_columns, $new_columns);

        foreach ($unneededColumns as $key => $column) {
			if (!$this->hasColumn($column)) continue;

			$in = $this->mySQL->execute("SELECT index_name FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '$this->name' AND COLUMN_NAME = '" . $column . "'");
			$in = array_column($in, 'index_name');
			if ($in) {
				foreach ($in as $indexName) {
					$this->dropIndex($indexName);
				}
			}

            $this->dropColumn($column);
        }
    }

	function addUniqueIndexesFromSchema(TableDef $tableDef): void
	{
		foreach ($tableDef->indexes as $index) {
			$indexName = 'unique_' . implode("_", $index);
			$indexValue = implode(",", $index);
			if ($this->hasIndex($indexName)) {
				$this->dropIndex($indexName);
			}

			$this->mySQL->execute("
				ALTER TABLE $this->name
				    ADD UNIQUE INDEX `$indexName` ($indexValue)
			");
		}
	}
}
