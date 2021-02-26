<?php

namespace PhpRemix\Foundation;

use DI\Container;
use DI\ContainerBuilder;
use PhpRemix\Foundation\Exception\ExceptionHandler;
use PhpRemix\Foundation\Exception\NotAllowReinitializeException;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
use function DI\create;
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
     * 运行指令
     *
     * @var array
     */
    private $run = [];

    /**
     * 应用结束指令
     *
     * @var array
     */
    private $terminated = [];

    /**
     * 异常处理器
     *
     * @var ExceptionHandler
     */
    private $exceptionHandler;

    /**
     * @var string
     */
    private $basePath;

    /**
     * 应用初始化
     *
     * @param string|null $basePath
     * @throws \Exception
     */
    public function __construct($basePath = null)
    {
        if (!is_null(self::$instance))
            throw new NotAllowReinitializeException("Application has been already initial.");

        $this->basePath = $basePath;

        $builder = new ContainerBuilder();

        /**
         * 依赖加载顺序：
         * 1. 框架内部
         * 2. di.php
         * 3. 外部依赖
         */

        $builder->addDefinitions([
            Application::class => $this,
            'app' => get(Application::class),
        ]);

        if (file_exists($diFile = $this->getConfigPath('di.php'))) {
            $builder->addDefinitions($diFile); // 自定义依赖注入
        }

        $this->container = $builder->build();

        self::$instance = $this;

        $this->exceptionHandler = new ExceptionHandler();
        $this->exceptionHandler->register();
    }

    /**
     * 重新设置ExceptionHandler
     *
     * @param $handler
     */
    public function setExceptionHandler($handler)
    {
        $this->exceptionHandler->unregister();
        $this->exceptionHandler = $handler;
        $this->exceptionHandler->register();
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

    /**
     * 添加运行插件
     *
     * @param $run
     */
    public function addRun($run)
    {
        if (empty($run['type']) || !in_array($run['type'], ['DI', 'callable'])) {
            throw new \InvalidArgumentException("not allow param");
        }

        $this->run[] = $run;
    }

    /**
     * 添加销毁插件
     *
     * @param $terminated
     */
    public function addTerminated($terminated)
    {
        if (empty($terminated['type'])) {
            throw new \InvalidArgumentException("Empty type param");
        }

        if (!in_array($terminated['type'], ['DI', 'callable'])) {
            throw new \InvalidArgumentException("Type [{$terminated['type']}] is not allow");
        }

        $this->terminated[] = $terminated;
    }

    /**
     * 是否存在依赖
     *
     * @param $name
     * @return bool
     */
    public function has($name): bool
    {
        return $this->container->has($name);
    }

    /**
     * 设置DI
     *
     * @param string $name
     * @param $value
     */
    public function set(string $name, $value)
    {
        $this->container->set($name, $value);
    }

    /**
     * 带依赖注入的调用
     *
     * @param $callable
     * @param array $parameters
     * @return mixed
     */
    public function call($callable, array $parameters = [])
    {
        return $this->container->call($callable, $parameters);
    }

    /**
     * 应用结束或中止触发
     * 不需要手动调用
     *
     * @throws \DI\DependencyException
     * @throws \DI\NotFoundException
     */
    public function terminated()
    {
        // 运行指令
        foreach ($this->terminated as $terminated) {
            /**
             * terminated格式：
             * ['type' => 'DI', 'name' => ClassOrAlias, 'method' => '']
             * ['type' => 'callable', 'callable' => [new Class, dispatch]]
             *
             * type 目前仅支持 DI 或 callable
             * 调用提供参数：$app
             */

            $type = $terminated['type'];

            switch ($type) {
                case 'DI':
                    $this->container->call([$this->get($terminated['name']), $terminated['method']]);
                    break;
                case 'callable':
                    $this->container->call($terminated['callable']);
                    break;
            }
        }
    }

    /**
     * 运行依赖
     * 需要手动调用
     *
     * @throws
     */
    public function run()
    {
        // 运行指令
        foreach ($this->run as $run) {
            /**
             * run格式：
             * ['type' => 'DI', 'name' => ClassOrAlias, 'method' => '']
             * ['type' => 'callable', 'callable' => [new Class, dispatch]]
             *
             * type 目前仅支持 DI 或 callable
             * 调用提供参数：$app
             */

            $type = $run['type'];

            switch ($type) {
                case 'DI':
                    $this->container->call([$this->get($run['name']), $run['method']]);
                break;
                case 'callable':
                    $this->container->call($run['callable']);
                break;
            }
        }
    }

    /**
     * 取根路径
     *
     * @return string|null
     */
    public function getBasePath(): ?string
    {
        return $this->basePath;
    }

    /**
     * 取config路径
     *
     * @param string $path
     * @return string
     */
    public function getConfigPath($path = ''): string
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'config' .
            ($path == '' ? $path : DIRECTORY_SEPARATOR . $path);
    }

    /**
     * 取app路径
     *
     * @param string $path
     * @return string
     */
    public function getAppPath($path = ''): string
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'app' .
            ($path == '' ? $path : DIRECTORY_SEPARATOR . $path);
    }

    /**
     * 取bootstrap路径
     *
     * @param string $path
     * @return string
     */
    public function getBootstrapPath($path = ''): string
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'bootstrap' .
            ($path == '' ? $path : DIRECTORY_SEPARATOR . $path);
    }

    /**
     * 取databases路径
     *
     * @param string $path
     * @return string
     */
    public function getDatabasesPath($path = ''): string
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'storage' .
            ($path == '' ? $path : DIRECTORY_SEPARATOR . $path);
    }

    /**
     * 取storage路径
     *
     * @param string $path
     * @return string
     */
    public function getStoragePath($path = ''): string
    {
        return $this->basePath . DIRECTORY_SEPARATOR . 'storage' .
            ($path == '' ? $path : DIRECTORY_SEPARATOR . $path);
    }
}