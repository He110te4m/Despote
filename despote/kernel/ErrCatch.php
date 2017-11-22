<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 错误处理类，对错误/异常捕获并处理
 * @author      He110 (i@he110.top)
 * @namespace   despote\kernel
 */
namespace despote\kernel;

use \despote\base\Service;
use \Utils;

class ErrCatch extends Service
{
    public static function register()
    {
        $obj = new static;
        // 自定义异常处理
        set_exception_handler([$obj, 'onException']);
        // 自定义错误处理
        set_error_handler([$obj, 'onError']);
        // 自定义致命错误处理
        register_shutdown_function([$obj, 'onShutdown']);
    }

    public static function unregister()
    {
        restore_error_handler();
        restore_exception_handler();
    }

    /**
     * 异常处理
     * @param  Object $exception 异常对象
     */
    public function onException($exception)
    {
        // 输出错误信息
        $this->display('Exception', $exception->getMessage(), $exception->getFile(), $exception->getLine());
    }

    public function onError($errno, $errstr, $errfile, $errline)
    {
        $this->display('Error', $errstr, $errfile, $errline);
    }

    /**
     * 当程序停止运行时调用，尝试捕获错误
     * @return [type] [description]
     */
    public function onShutdown()
    {
        // 获取异常信息
        $error = error_get_last();

        if ($error) {
            $this->display('Error', $error['message'], $error['file'], $error['line']);
        }
    }

    /**
     * 显示错误信息
     * @param  String  $type    错误的类型，是 Error 还是 Exception
     * @param  String  $errstr  错误类型
     * @param  String  $errfile 错误发生的文件的绝对路径
     * @param  integer $errline 错误发生的行数
     */
    private function display($type, $errstr, $errfile, $errline)
    {
        // 获取错误追踪
        $contents = Utils::getFileLine($errfile, [$errline - 5, $errline + 5]);
        $trace    = '<li> ' . implode(' </li><li> ', $contents) . ' </li>';
        // 获取错误代码行内容
        $code = Utils::getFileLine($errfile, $errline);

        // 输出错误信息
        echo <<<EOF
<div style="font-family: 'Consolas'; width: 100%; border: 1px solid #000;">
    <h1 style="margin: 0; padding: 5px 10px; font-size: 18px; border-bottom: 1px solid #000;">
        An $type occurred while Despote running.
    </h1>
    <ul style="list-style: none; padding: 5px 10px; margin: 0; font-size: 16px; background-color: #000; color: #fff;">
        <li>Despote Framework [Version 3.0]. Copyright (c) 2017 He110. All rights reserved.</li>
        <li>Copyright (c) 2017 He110. All rights reserved.</li>
        <li style="color: green;">[root@He110 ~] $type Code ：$code </li>
        <li style="color: red;">[root@He110 ~] $type Info ：$errstr </li>
        <li style="color: blue;">[root@He110 ~] $type File ：$errfile </li>
        <li style="color: yellow;">[root@He110 ~] $type Line ：$errline </li>
        <li>&nbsp;</li>
        $trace
    </ul>
</div>
EOF;
        // 开了错误捕获就是在开发阶段，那么出了 BUG 就不该继续执行，方便知道哪一步出错了
        die;
    }
}
