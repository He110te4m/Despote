<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 工具类，放置一些常用函数
 * @author      He110 (i@he110.top)
 * @namespace   despote\kernel
 */

class Utils
{
    /////////////
    // 资源统计 //
    ////////////

    // 统计的时间
    private static $times = [];
    // 统计的内存占用
    private static $memories = [];

    /**
     * 计算资源占用，并保存在数组中，只在 DEBUG 模式才会触发该事件
     * @param  String $title 在统计时正在发生什么事
     */
    public static function tick($title)
    {
        // 判断是否在 DEBUG 模式

        // 取当前时间
        $mtime         = explode(' ', microtime());
        self::$times[] = [
            'time'  => $mtime[1] + $mtime[0],
            'title' => $title,
        ];

        // 统计内存使用
        self::$memories[] = [
            'memory' => memory_get_usage(),
            'title'  => $title,
        ];
    }

    /**
     * 获取运行到目前为止消耗的时间
     * @return integer 消耗的时间，单位为秒(s)
     */
    public static function getRunTime()
    {
        // 校验是否有统计数据
        $len = count(self::$times);
        if ($len == 0) {
            return 0;
        }

        // 计算运行时间
        $runTime = self::$times[$len - 1]['time'] - self::$times[0]['time'];

        return $runTime;
    }

    /**
     * 获取运行到目前为止占用的内存
     * @return integer 占用的内存，单位为 MB
     */
    public static function getUseMemory()
    {
        // 校验是否有统计数据
        $len = count(self::$memories);
        if ($len == 0) {
            return 0;
        }

        // 计算运行时间
        $useMemory = self::$memories[$len - 1]['memory'] - self::$memories[0]['memory'] / 2062336;

        return $useMemory;
    }

    ///////////////////
    // 堆栈调试工具函数 //
    ///////////////////

    private static $id;

    public static function begin()
    {
        self::$id = mt_rand();
        echo self::$id . '开始<br>';
    }

    public static function end()
    {
        echo self::$id . '结束<br>';
    }

    /////////////
    // 配置加载 //
    ////////////

    private static $config;

    public static function initConf()
    {
        // 屏蔽系统错误提示
        ini_set('display_errors', 'Off');
        error_reporting(0);
        self::config('error_catch') && Event::trigger('ERROR_CATCH_ON');
    }

    public static function config($name, $defaultValue = '')
    {
        empty(self::$config) && self::$config = require PATH_CONF . 'config.php';

        return isset(self::$config[$name]) ? self::$config[$name] : $defaultValue;
    }
}
