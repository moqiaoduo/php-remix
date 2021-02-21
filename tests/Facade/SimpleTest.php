<?php

namespace Test\Facade;

use Monolog\Logger;
use PhpRemix\Application;
use PhpRemix\Facade\App;
use PHPUnit\Framework\TestCase;
use function DI\create;

class SimpleTest extends TestCase
{
    public function testApp()
    {
        $app = new Application([
            'Logger' => create(Logger::class)->constructor('test')
        ]);

        var_dump(App::get('Logger'));

        $this->assertTrue(true);
    }
}