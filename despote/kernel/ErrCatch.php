<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 错误处理类，对错误/异常捕获并处理，在非 Debug 状态下会自动记录到日志
 * @author      He110 (i@he110.info)
 * @namespace   despote\kernel
 */
namespace despote\kernel;

use \Despote;
use \despote\base\Service;
use \Event;
use \Exception;
use \Utils;

class ErrCatch extends Service
{
    public static function register($exception = 'onException', $error = 'onError', $shutdown = 'onShutdown', $class = 'this')
    {
        // 兼容处理
        empty($exception) && $exception = 'onException';
        empty($error) && $error = 'onError';
        empty($shutdown) && $shutdown = 'onShutdown';

        if ($class == 'this') {
            $obj = new static;
        } else {
            try {
                $obj = new $class;
            } catch (Exception $e) {
                die('注册错误处理方式时出错，请检查错误处理配置');
            }
        }

        // 自定义异常处理
        set_exception_handler([$obj, $exception]);
        // 自定义错误处理
        set_error_handler([$obj, $error]);
        // 自定义致命错误处理
        register_shutdown_function([$obj, $shutdown]);
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
        $debug = Utils::config('error_catch', true);
        if ($debug) {
            // 输出错误信息
            $this->display('Exception', $exception->getMessage(), $exception->getFile(), $exception->getLine());
        } else {
            $msg = <<<EOF
异常：<span style="color: #00F;">{$exception->getMessage()}</span> 发生在 <span style="color: #00F;">{$exception->getFile()}</span> 中的第 <span style="color: #00F;">{$exception->getLine()}</span> 行
EOF;
            Despote::logger()->log('warn', $msg);
        }
    }

    /**
     * 错误处理
     * @param  Integer   $errno    错误码
     * @param  String    $errstr   错误信息
     * @param  String    $errfile  发生错误的文件
     * @param  Integger  $errline  发生错误的行
     */
    public function onError($errno, $errstr, $errfile, $errline)
    {
        $debug = Utils::config('error_catch', true);
        if ($debug) {
            $this->display('Error', $errstr, $errfile, $errline);
        } else {
            $msg = <<<EOF
错误：<span style="color: #00F;">{$errstr}</span> 发生在 <span style="color: #00F;">{$errfile}</span> 中的第 <span style="color: #00F;">{$errline}</span> 行
EOF;
            $level = ($errno == E_USER_ERROR || $errno == E_ERROR) ? 'error' : 'fatal';
            Despote::logger()->log($level, $msg);
        }
    }

    /**
     * 当程序停止运行时调用，尝试捕获错误
     */
    public function onShutdown()
    {
        // 获取异常信息
        $error = error_get_last();

        if ($error) {
            $this->display('error', $error['message'], $error['file'], $error['line']);
        }

        // 脚本运行中止，触发事件写入日志
        Event::trigger('LOGGER');
    }

    /**
     * 显示错误信息
     * @param  String  $type    错误的类型，是 Error 还是 Exception
     * @param  String  $errstr  错误类型
     * @param  String  $errfile 错误发生的文件的绝对路径
     * @param  Integer $errline 错误发生的行数
     */
    private function display($type, $errstr, $errfile, $errline)
    {
        // 获取错误追踪
        $contents = Despote::file()->getLine($errfile, [$errline - 5, $errline + 5]);
        $trace    = '<li> ' . implode(' </li><li> ', $contents) . ' </li>';
        // 获取错误代码行内容
        $code = Despote::file()->getLine($errfile, $errline);

        // 输出错误信息
        echo <<<EOF
<div style="font-family: 'Consolas'; border: 1px solid #000; z-index: 99999; position: fixed; top: 0; left: 0; right: 0;">
    <h1 style="margin: 0; padding: 5px 10px; font-size: 18px; border-bottom: 1px solid #000; background-color: #fff;">
        An $type occurred while Despote running.
    </h1>
    <ul style="list-style: none; padding: 5px 10px; margin: 0; font-size: 16px; background-color: #000; color: #fff;">
        <li>Despote Framework [Version 3.0]. Copyright (c) 2017 He110. All rights reserved.</li>
        <li>Copyright (c) 2017 He110. All rights reserved.</li>
        <li style="color: green;">[root@He110 ~] $type Code ：<b>$code</b> </li>
        <li style="color: red;">[root@He110 ~] $type Info ：<b>$errstr</b> </li>
        <li style="color: blue;">[root@He110 ~] $type File ：<b>$errfile</b> </li>
        <li style="color: yellow;">[root@He110 ~] $type Line ：<b>$errline</b> </li>
        <li>&nbsp;</li>
        $trace
    </ul>
</div>
EOF;
    }
}
