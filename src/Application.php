<?php

namespace PhpRemix;

use DI\Container;
use DI\ContainerBuilder;
use PhpRemix\Foundation\Exception\NotAllowReinitializeException;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
use function DI\factory;
use function DI\get;

/**
 * 主应用入口
 */
class Application
{
    /**
     * 容器管理器
     *
     * @var Container
     */
    private $container;

    /**
     * 只能初始化一次
     *
     * @var Application
     */
    private static $instance;

    /**
     * 应用初始化
     *
     * @param string|array $configs DI注入配置
     * @throws \Exception
     */
    public function __construct($configs = [])
    {
        if (!is_null(self::$instance))
            throw new NotAllowReinitializeException("Application has been already initial.");

        $builder = new ContainerBuilder();

        $builder->addDefinitions([
            Application::class => factory(function () {
                return $this;
            }),
            'app' => get(Application::class),
        ]);

        $builder->addDefinitions($configs);

        $this->container = $builder->build();

        self::$instance = $this;
    }

    /**
     * 取唯一实例
     *
     * @return Application
     */
    public static function getInstance(): Application
    {
        return self::$instance;
    }

    /**
     * 获取依赖
     * 这个方法只有第一次调用类才会创建对象，之后直接从容器内获取示例
     *
     * @param string $name
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function get(string $name)
    {
        return $this->container->get($name);
    }

    /**
     * 创建依赖
     * 这个方法每次都会新建一个对象，而不是获取之前在容器内的示例
     *
     * @param string $name
     * @param array $param
     * @return mixed
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function make(string $name, $param = [])
    {
        return $this->container->make($name, $param);
    }

    /**
     * 初始化whoops
     * 这个是外部调用的，内部无任何启动机制
     */
    public function initWhoops()
    {
        $whoops = new Run;

        $whoops->pushHandler(new PrettyPageHandler);

        $whoops->register();
    }
}