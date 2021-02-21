<?php

namespace PhpRemix\Foundation;

use PhpRemix\Foundation\Exception\NotCallableForMacroException;
use PhpRemix\Foundation\Exception\NotFoundMacroException;

/**
 * 自定义宏
 * 借用的是Laravel的那套思想，动态绑定方法
 * 由于某些原因，不允许静态调用macro
 */
trait Macro
{
    /**
     * 保存macro，value均为callable
     * 经测试，不会“串味”，即addMacro不会互相影响
     *
     * @var array
     */
    protected static $macros = [];

    /**
     * 绑定$this给macro
     *
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws NotFoundMacroException
     */
    public function __call($name, $arguments)
    {
        if (!isset(self::$macros[$name]))
            throw new NotFoundMacroException("[$name] method is not found");

        $callable = self::$macros[$name];

        return $callable->call($this, ...$arguments);
    }

    /**
     * 添加宏
     *
     * @param string $method
     * @param callable $callable
     */
    public static function addMacro(string $method, callable $callable)
    {
        self::$macros[$method] = $callable;
    }
}