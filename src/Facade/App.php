<?php

namespace PhpRemix\Facade;

use PhpRemix\Foundation\Facade;

class App extends Facade
{
    protected static function getFacadeAliasName(): ?string
    {
        return "app";
    }
}