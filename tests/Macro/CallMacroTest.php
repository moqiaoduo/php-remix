<?php

namespace Test\Macro;

use PHPUnit\Framework\TestCase;
use Test\Macro\Test\DiffClass;
use Test\Macro\Test\MacroClass;
use Test\Macro\Test\MainClass;

class CallMacroTest extends TestCase
{
    public function testCallSameClassMethod()
    {
        MainClass::addMacro('ps', MacroClass::ps());

        $main = new MainClass();

        $this->assertSame("xbox", $main->ps());
    }

    public function testCallDiffClassMethod()
    {
        MainClass::addMacro('ps', MacroClass::ps());

        DiffClass::addMacro('ps4', MacroClass::ps());

        $main = new MainClass();
        $diff = new DiffClass();

        $this->assertSame("xbox", $main->ps());

        $this->assertSame("xbox", $diff->ps4());
    }
}