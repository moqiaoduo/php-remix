<?php

namespace Test\Macro\Test;

use PhpRemix\Foundation\Macro;

class MainClass
{
    use Macro;

    public function hello()
    {
        return "Hello, World";
    }
}