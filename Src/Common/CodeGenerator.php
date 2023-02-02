<?php

namespace Tet\Common;

class CodeGenerator
{
    const EOF = "\r\n";
    const TAB_SYMBOL = " ";
    const TAB_LENGTH = 4;
    public $stream;

    function open(string $filename)
    {
        $this->stream = fopen($filename, 'w');
    }

    function close()
    {
        fclose($this->stream);
    }

    function startTag()
    {
        $this->line("<?php");
    }

    function namespace($name)
    {
        $this->line("namespace $name;");
    }

    function function($name, $body)
    {

        $this->line("function $name()", 1);
        $this->line("{", 1);
        $this->line($body, 2);
        $this->line("}", 1);
    }

    function class($name, $body)
    {
        $this->line("class $name()", 1);
        $this->line("{", 1);
        $this->line($body, 2);
        $this->line("}", 1);
    }

    function line($line, int $tabCount = 0)
    {
        fwrite($this->stream, str_repeat($this::TAB_SYMBOL, $this::TAB_LENGTH * $tabCount) .  $line . $this::EOF);
    }
}
