<?php

namespace PhpRemix\Foundation;

abstract class Facade
{
    /**
     * 执行门面
     *
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public static function __callStatic($name, $arguments)
    {
        $app = Application::getInstance();

        return $app->call([$app->get(static::getFacadeAliasName()), $name], $arguments);
    }

    /**
     * 别名
     *
     * @return string|null
     */
    protected static function getFacadeAliasName() : ?string
    {
        return null;
    }
}