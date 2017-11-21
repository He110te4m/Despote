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
        // 获取错误信息
        $msg = $this->getLine($exception->getFile(), $exception->getLine() - 5, $exception->getLine() + 5);
        // 获取错误代码
        $code = $msg['code'];
        // 获取错误追踪
        $trace = $msg['trace'];

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
     * 错误追踪
     * @param  String  $filename  文件名，包含文件路径
     * @param  integer $startLine 起始代码行
     * @param  integer $endLine   结束代码行
     * @param  string  $mode      以什么模式打开文件
     * @return Array              错误代码行(code) 和 错误追踪(trace)
     */
    private function getLine($filename, $startLine = 1, $endLine = 20, $mode = 'rb')
    {
        $content = [];
        $count   = $endLine - $startLine;
        $fp      = new \SplFileObject($filename, $mode);
        $half    = ($startLine + $endLine) / 2;
        // 转到第N行, seek方法参数从0开始计数
        $fp->seek($startLine - 1);
        for ($i = 0; $i <= $count; ++$i) {
            $nowline = $startLine + $i;
            // current()获取当前行内容
            if ($nowline == (($startLine + $endLine) / 2)) {
                $msg['code'] = trim($fp->current());
                $content[]   = sprintf("<li> <span style=\"color: red;\">%s</span> </li>", $msg['code']);
            } else {
                $content[] = sprintf("<li> %s </li>", $fp->current());
            }
            // 下一行
            $fp->next();
            if ($fp->eof()) {
                array_pop($content);
                break;
            }
        }
        $msg['trace'] = implode('', array_filter($content));

        return $msg;
    }

    // 下次更新使用模板引擎做
    private function display($type, $errstr, $errfile, $errline)
    {
        // 获取错误信息
        $msg = $this->getLine($errfile, $errline - 5, $errline + 5);
        // 获取错误代码
        $code = $msg['code'];
        // 获取错误追踪
        $trace = $msg['trace'];

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
