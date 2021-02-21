<?php

use PhpRemix\Application;

if (!function_exists('app')) {
    /**
     * 获取app或者使用工厂模式创建对象
     *
     * @param string|null $abstract
     * @param array $parameters
     * @return mixed|Application
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    function app($abstract = null, array $parameters = [])
    {
        if (is_null($abstract)) {
            return Application::getInstance();
        }

        return Application::getInstance()->make($abstract, $parameters);
    }
}