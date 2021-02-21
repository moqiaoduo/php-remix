<?php

namespace Test\Framework;

use PhpRemix\Foundation\Application;
use PHPUnit\Framework\TestCase;

class ExceptionTest extends TestCase
{
    public function testTerminated()
    {
        $app = new Application();

        $app->addTerminated(['type' => 'callable', 'callable' => function () {
            echo "test";
        }]);

        $this->assertTrue(true);
    }
}