<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 日志记录类
 * @author      He110 (i@he110.top)
 * @namespace   despote\kernel
 */
namespace despote\kernel;

use \despote\base\Service;

class Logger extends Service
{
    /////////////
    // 日志属性 //
    /////////////

    // 日志存储路径
    protected $path;
    // 记录日志的级别，默认全显示
    protected $limit = 5;
    // 日志等级划分
    private $level = [
        // 操作日志，比如访问某页面等
        'info'  => 5,
        // 调试时输出的信息
        'debug' => 4,
        // 有安全风险的操作，如：登陆等
        'warn'  => 3,
        // 系统运行错误
        'error' => 2,
        // 系统致命错误，将导致脚本结束运行
        'fatal' => 1,
    ];
    // 设置日志颜色
    private $color = [
        // 操作日志为绿色
        'info'  => '#00FF00',
        // Debug 信息为默认颜色
        'debug' => '#FFFFFF',
        // 警告为黄色
        'warn'  => '#FFFF00',
        // 系统错误为红色
        'error' => '#FF0000',
        // 系统致命错误为红色
        'fatal' => '#FF0000',
    ];
    // 日志文件头
    private $head = <<<EOF
<meta charset="utf8">
<style>
    .log {
        font-family: 'Consolas';
        width: 100%;
        border: 1px solid #000000;
    }
    .log h1 {
        margin: 0;
        padding: 5px 10px;
        font-size: 18px;
        border-bottom: 1px solid #000000;
    }
    .log ul {
        list-style: none;
        padding: 5px 10px;
        margin: 0;
        font-size: 16px;
        background-color: #000000;
        color: #FFFFFF;
    }
</style>
<div class="log">
    <h1>Despote Framework operation log</h1>
    <ul>
        <li>Despote Framework [Version 3.0]. Copyright (c) 2017 He110. All rights reserved.</li>
        <li>Copyright (c) 2017 He110. All rights reserved.</li>
EOF;

    protected function init()
    {
        isset($this->path) || $this->path   = PATH_LOG;
        isset($this->limit) || $this->limit = 2;
        is_dir($this->path) || \Utils::createFile($this->path, true);
    }

    public function log($level, $msg)
    {
        $level = strtolower($level);
        if ($this->level[$level] > $this->limit) {
            return;
        }

        // 记录日志的时间
        $time = date('Y-m-d H-i-s');
        // 日志存放地址
        $file = $this->path . date('Y-m-d') . '.html';
        // 日志颜色高亮
        $color = $this->color[$level];
        $level = strtoupper($level);
        // 日志模板
        $tpl = <<<EOF
        <li>[root@He110 ~] log -d $time</li>
        <li><span style="color: {$color}">[ $level ]</span> $msg</li>
        <li></li>
EOF;

        // 写入日志
        is_file($file) || file_put_contents($file, $this->head, LOCK_EX);
        file_put_contents($file, $tpl, FILE_APPEND | LOCK_EX);
    }
}
