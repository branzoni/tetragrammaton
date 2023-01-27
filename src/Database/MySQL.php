<?php

namespace Tet;

class TableScheme extends Collection
{
}

class TableField
{
    public string $name;
    public string $type;
    public string $null;
    public string $key;
    public $default;
    public $extra;
}

class MySQL implements DbInterface
{
    private $connection;

    function open($hostname, $name, $user, $password)
    {
        $this->connection = mysqli_connect($hostname, $user, $password, $name);
        if (!$this->connection) return null;
        mysqli_set_charset($this->connection, "utf8");
        return $this->connection;
    }

    function close()
    {
        return mysqli_close($this->connection);
    }

    function error()
    {
        return mysqli_error($this->connection);
    }

    private function escapeString($string)
    {
        return mysqli_real_escape_string($this->connection, $string);
    }

    function connected()
    {
        if (!$this->connection) return false;
        return true;
    }

    function execute(string $query): Result
    {
        $result = new Result;
        $data = mysqli_query($this->connection, $query);
        $result->error = (mysqli_error($this->connection) != "");
        $result->description =  mysqli_error($this->connection);
        if (!$data) return $result;
        $result->data = mysqli_fetch_all($data,  MYSQLI_ASSOC);;
        return $result;
    }

    function select(string $tablename, $values, $where = null, $orderBy = null): Result
    {
        $query = new Query();
        $query->command = $query::COMMAND_SELECT;
        $query->tablename = $tablename;
        $query->fields->add($values);
        $query->where = $where;
        $query->orderBy = $orderBy;
        echo $query;
        return $this->execute($query);
    }


    function insert(string $tablename, $values): Result
    {
        $query = new Query();
        $query->command = $query::COMMAND_INSERT;
        $query->tablename = $tablename;
        $query->fields->add($values);
        return $this->execute($query);
    }

    function update(string $tablename, $values, $where): Result
    {
        $query = new Query();
        $query->command = $query::COMMAND_UPDATE;
        $query->tablename = $tablename;
        $query->fields->add($values);
        $query->where = $where;
        return $this->execute($query);
    }

    function delete(string $tablename, $where): Result
    {
        $query = new Query();
        $query->command = $query::COMMAND_DELETE;
        $query->tablename = $tablename;
        $query->where = $where;
        return $this->execute($query);
    }

    function table(?string $name = null)
    {
        $table = new Table();
        $table->name = $name;
        $table->connection = $this->connection;
        return $table;
    }

    function getTableList(): array
    {
        $result = $this->execute("SHOW TABLES")->data;
        if (!$result) return null;
        $result = array_values($result);

        $tmp = [];
        foreach ($result as $row) {
            $row = array_values($row);
            $tmp[] = $row[0];
        }
        return $tmp;
    }

    function getTableScheme(string $tablename): TableScheme
    {
        $tbl = new TableScheme;
        $data = $this->execute("DESCRIBE " . $tablename)->data;
        if (!$data) return null;

        foreach ($data as $key => $value) {
            $tb = new TableField();
            $tb->name = $value["Field"];
            $tb->type = $value["Type"];
            $tb->null = $value["Null"];
            $tb->key = $value["Key"];
            $tb->default = $value["Default"];
            $tb->extra = $value["Extra"];

            $tbl->set($key, $tb);
        }

        return $tbl;
    }

    function createTableSchemeClass(string $destination, string $tablename): bool
    {
        $ts = $this->getTableScheme($tablename);

        $fn = "$tablename.php";

        $f = fopen("$destination\\$fn", 'w');

        fwrite($f, "<?php\r\n");
        //fwrite($f, "\r\n");
        //fwrite($f, "use Tet\TableField;\r\n");
        fwrite($f, "\r\n");
        fwrite($f, "class $tablename\r\n");
        fwrite($f, "{\r\n");
        foreach ($ts->toArray() as $key => $value) {
            //fwrite($f, "    public TableField \${$value->name};\r\n");
            fwrite($f, "    public \${$value->name};\r\n");
        }
                
        // fwrite($f, "\r\n");
        // fwrite($f, "    function __construct()\r\n");
        // fwrite($f, "    {\r\n");
        //     foreach ($ts->toArray() as $value) {                
        //         fwrite($f, "        \$this->{$value->name} = new TableField;\r\n");
        //         fwrite($f, "        \$this->{$value->name}->name = '{$value->name}';\r\n");
        //         fwrite($f, "        \$this->{$value->name}->type = '{$value->type}';\r\n");
        //         fwrite($f, "        \$this->{$value->name}->null = '{$value->null}';\r\n");
        //         fwrite($f, "        \$this->{$value->name}->key = '{$value->key}';\r\n");
        //         fwrite($f, "        \$this->{$value->name}->default = '{$value->default}';\r\n");
        //         fwrite($f, "        \$this->{$value->name}->extra = '{$value->extra}';\r\n");                
        //         fwrite($f, "\r\n");
        //     }
        // fwrite($f, "    }\r\n");        
        fwrite($f, "}\r\n");
        fclose($f);
        return true;
    }
}
