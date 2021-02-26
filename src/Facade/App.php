<?php

namespace PhpRemix\Facade;

use PhpRemix\Foundation\Application;
use PhpRemix\Foundation\Facade;

/**
 * @method static mixed get(string $name)
 * @method static bool has(string $name)
 * @method static mixed make(string $name, $param = [])
 * @method static void terminated()
 *
 * @see Application
 */
class App extends Facade
{
    protected static function getFacadeAliasName(): ?string
    {
        return "app";
    }
}