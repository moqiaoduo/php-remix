<?php

namespace Test\Macro\Test;

class MacroClass
{
    public static function ps()
    {
        return function () {
            var_dump($this);

            return 'xbox';
        };
    }
}