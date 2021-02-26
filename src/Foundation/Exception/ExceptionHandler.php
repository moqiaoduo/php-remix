<?php

namespace PhpRemix\Foundation\Exception;

use Monolog\Logger;
use PhpRemix\Facade\App;
use Throwable;

/**
 * 默认的异常处理器
 */
class ExceptionHandler
{
    public $memoryReserveSize = 262144;//备用内存大小

    private $_memoryReserve;//备用内存

    public function register()
    {
        ini_set('display_errors', 0);
        set_exception_handler(array($this, 'handleException'));//截获未捕获的异常
        set_error_handler(array($this, 'handleError'));//截获各种错误 此处切不可掉换位置
        //留下备用内存 供后面拦截致命错误使用
        $this->memoryReserveSize > 0 && $this->_memoryReserve = str_repeat('x', $this->memoryReserveSize);
        register_shutdown_function(array($this, 'handleFatalError'));//截获致命性错误
    }

    public function unregister()
    {
        restore_error_handler();
        restore_exception_handler();
    }

    public function handleException($exception)
    {
        $this->unregister();

        $this->render($exception);

        $this->report($exception);

        exit(1);
    }

    public function handleFatalError()
    {
        unset($this->_memoryReserve);//释放内存供下面处理程序使用
        $error = error_get_last();//最后一条错误信息
        if (ErrorHandlerException::isFatalError($error)) {//如果是致命错误进行处理
            $exception = new ErrorHandlerException($error['message'], $error['type'], $error['type'], $error['file'], $error['line']);
            $this->report($exception);
            exit(1);
        }

        // 正常情况下，应该执行terminated
        App::terminated();
    }

    public function handleError($code, $message, $file, $line)
    {
        //该处思想是将错误变成异常抛出 统一交给异常处理函数进行处理
        if ((error_reporting() & $code) && !in_array($code,
                [E_NOTICE, E_WARNING, E_USER_NOTICE, E_USER_WARNING, E_DEPRECATED])) {
            //此处只记录严重的错误 对于各种WARNING NOTICE不作处理
            $exception = new ErrorHandlerException($message, $code, $code, $file, $line);
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            array_shift($trace);//trace的第一个元素为当前对象 移除
            foreach ($trace as $frame) {
                if ($frame['function'] == '__toString') {//如果错误出现在 __toString 方法中 不抛出任何异常
                    $this->handleException($exception);
                    exit(1);
                }
            }
            throw $exception;
        }
        return false;
    }

    /**
     * 渲染错误页面
     *
     * @param $exception
     */
    public function render($exception)
    {
        echo "***System error***\n";
        echo $exception->getMessage() . "\n\n";
        debug_print_backtrace();
    }

    /**
     * 报告错误
     *
     * @param Throwable $exception
     */
    public function report(Throwable $exception)
    {
        if (!App::has('Logger')) return;

        $logger = App::get('Logger');

        if ($logger instanceof Logger) {
            $logger->error("message: " . $exception->getMessage());
        }
    }
}