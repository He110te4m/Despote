<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 日志记录类
 * @author      He110 (i@he110.info)
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
    protected $limit;
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
        // 兼容传入数字方式
        5       => 5,
        4       => 4,
        3       => 3,
        2       => 2,
        1       => 1,
    ];
    // 设置日志颜色
    private $color = [
        // 操作日志为绿色
        'info'  => '#0F0',
        // Debug 信息为默认颜色
        'debug' => '#FFF',
        // 警告为黄色
        'warn'  => '#FF0',
        // 系统错误为红色
        'error' => '#F00',
        // 系统致命错误为红色
        'fatal' => '#F00',
    ];
    // 日志文件头
    private static $head = <<<EOF
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
    private static $logs = [];

    protected function init()
    {
        isset($this->path) || $this->path   = PATH_LOG;
        isset($this->limit) || $this->limit = 2;
        is_dir($this->path) || \Despote::file()->create($this->path, true);
    }

    /**
     * 记录日志
     * @param  Mixed  $level  兼容等级 or 字符串，可选值见 $this->level
     * @param  String $msg    需要记录的信息
     * @param  String $type   记录的操作类型，用于区分不同的日志
     */
    public function log($level, $msg, $type = '')
    {
        // 校验日志级别是否需要记录
        $level = strtolower($level);
        if ($this->level[$level] > $this->limit) {
            return;
        }

        // 记录日志的时间
        $time = date('Y-m-d H-i-s');
        // 日志存放地址
        empty($type) || $type .= ' ';
        $file = $this->path . $type . date('Y-m-d') . '.html';
        // 日志颜色高亮
        $color = $this->color[$level];
        $level = strtoupper($level);

        // 初始化日志
        isset(self::$logs[$file]) || self::$logs[$file] = [];
        // 写入日志，等待脚本执行完成后再写入，避免频繁写入文件
        self::$logs[$file][] = [
            'msg'   => $msg,
            'time'  => $time,
            'color' => $color,
            'level' => $level,
        ];
    }

    public static function save()
    {
        // 处理不同类别的日志
        foreach (self::$logs as $file => $item) {
            // 清除上次的日志记录
            $content = '';
            foreach ($item as $log) {
                // 日志模板
                $content .= <<<EOF
                <li>[root@He110 ~] log -d {$log['time']}</li>
                <li><span style="color: {$log['color']}">[ {$log['level']} ]</span> {$log['msg']}</li>
                <li></li>
EOF;
            }

            // 写入日志
            is_file($file) || file_put_contents($file, self::$head, LOCK_EX);
            file_put_contents($file, $content, FILE_APPEND | LOCK_EX);
        }
    }
}
