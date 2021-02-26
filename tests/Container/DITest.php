<?php

namespace Test\Container;

use Monolog\Logger;
use PhpRemix\Foundation\Application;
use PHPUnit\Framework\TestCase;
use function DI\create;
use function DI\factory;

class DITest extends TestCase
{
    public function testMake()
    {
        $app = new Application();

        $app->set('test', create(Logger::class)->constructor('test'));

        $obj1 = $app->make('test');
        $obj2 = $app->make('test');

        $this->assertTrue($obj1 === $obj2);
    }

    public function testGet()
    {
        $app = new Application();

        $app->set('test', create(Logger::class)->constructor('test'));

        $obj1 = $app->get('test');
        $obj2 = $app->get('test');

        $this->assertTrue($obj1 === $obj2);
    }

    public function testFactory()
    {
        $app = new Application();

        $app->set('test', factory(function () {
            return new Logger('test');
        }));

        $obj1 = $app->get('test');
        $obj2 = $app->get('test');

        $this->assertTrue($obj1 === $obj2);
    }
}