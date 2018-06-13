<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 工具类，放置一些常用函数
 * @author      He110 (i@he110.info)
 * @namespace   despote\kernel
 */

class Utils
{
    /////////////
    // 配置加载 //
    /////////////

    private static $config;

    /**
     * 初始化环境配置
     */
    public static function initConf()
    {
        // 屏蔽系统错误提示
        if (self::config('debug', false) == false) {
            ini_set('display_errors', 'Off');
            error_reporting(0);
        } else {
            ini_set('display_errors', '1');
            error_reporting(E_ALL);
        }
        // 传参自定义错误处理函数，依次为异常处理函数、错误处理函数、停止响应函数
        // Event::trigger('ERROR_CATCH_ON', 'onException', 'onError', 'onShutdown');

        $error = self::config('error');
        if (empty($error)) {
            Event::trigger('ERROR_CATCH_ON');
        } else {
            // 设置异常处理函数
            $exception = isset($error['exception']) ? $error['exception'] : 'onException';
            // 设置错误处理函数
            $error = isset($error['error']) ? $error['error'] : 'onError';
            // 设置停止响应处理函数
            $shutdown = isset($error['shutdown']) ? $error['shutdown'] : 'onShutdown';
            // 设置这些函数所在的类名
            $class = isset($error['class']) ? $error['class'] : 'this';
            // 触发事件设置错误处理函数
            Event::trigger('ERROR_CATCH_ON', $exception, $error, $shutdown, $class);
        }
    }

    /**
     * 获取系统配置信息
     * @param  String $name         配置名
     * @param  Mixed  $defaultValue 配置不存在时返回的值，不配置默认返回空字符串
     * @return Mixed                配置存在时返回配置值，否则返回 defaultValue 的值
     */
    public static function config($name, $defaultValue = '')
    {
        empty(self::$config) && self::$config = require PATH_CONF . 'config.php';

        return isset(self::$config[$name]) ? self::$config[$name] : $defaultValue;
    }

    /**
     * 判断数组是否为关联数组
     * @param  Array   $arr 需要判断的数组
     * @return Boolean      关联数组返回 true，索引数组返回 false
     */
    public static function isAssoc(array $arr)
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    /**
     * 获取 uuid（通用唯一标识符）
     * @param   String  $prefix  uuid 的前缀
     * @return  String            生成的 uuid
     */
    public function getUuid($prefix = '')
    {
        $charid = strtoupper(md5(uniqid(mt_rand(), true)));
        $uuid   = substr($charid, 0, 8) . $prefix . substr($charid, 8, 4) . $prefix . substr($charid, 12, 4) . $prefix . substr($charid, 16, 4) . $prefix . substr($charid, 20, 12);

        return $uuid;
    }

    /////////////
    // 资源统计 //
    /////////////

    // 统计的时间
    private static $times = [];
    // 统计的内存占用
    private static $memories = [];

    /**
     * 计算资源占用，并保存在数组中
     * @param  String $title 统计分类，默认为 Core，即核心框架加载
     * @param  String $event 统计完成后需要触发的事件名
     * @param  Array  $args  传递给事件的参数
     * @return Mixed         默认返回 true，如果有触发事件则返回事件执行后的返回值
     */
    public static function tick($title = 'Core', $event = '', $args = [])
    {
        // 返回值
        $result = true;
        // 容错处理
        empty($title) && $title = 'Core';
        // 键名不区分大小写
        $title = strtoupper($title);

        // 校验时间统计
        isset(self::$times[$title]) || self::$times[$title] = [];
        // 校验内存统计
        isset(self::$memories[$title]) || self::$memories[$title] = [];

        // 取当前时间
        $mtime                 = explode(' ', microtime());
        self::$times[$title][] = $mtime[1] + $mtime[0];

        // 统计内存使用
        self::$memories[$title][] = function_exists('memory_get_usage') ? memory_get_usage() : 0;

        // 如果设置了事件触发，则开始触发事件
        if (!empty($event)) {
            if (isset($args)) {
                // 转成数组方便传参
                is_array($args) || $args = [$args];
                // 整合参数触发事件
                $args = array_merge([$event], $args);
            } else {
                $args = [$event];
            }

            // 获取事件处理结果
            $result = call_user_func_array('Event::trigger', $args);
        }

        return $result;
    }

    /**
     * 获取运行到目前为止消耗的时间，单位为 秒 (s)
     * @param  String  $title 统计分类，默认为 Core，即核心框架加载
     * @return Double         从加载到现在消耗的秒数
     */
    public static function getRunTime($title = 'Core')
    {
        $runTime = 0;
        // 容错处理
        empty($title) && $title = 'Core';
        // 键名不区分大小写
        $title = strtoupper($title);

        // 校验是否有统计数据
        if (isset(self::$times[$title])) {
            // 获取最后一次计时时间
            $len = count(self::$times[$title]);
            // 获取到函数调用时的时间
            $mtime                 = explode(' ', microtime());
            self::$times[$title][] = $mtime[1] + $mtime[0];
            // 计算运行时间
            $runTime = self::$times[$title][$len] - self::$times[$title][0];
        }

        return $runTime;
    }

    /**
     * 获取运行到目前为止占用的内存，单位为 兆 (MB)
     * @param  String  $title 统计分类，默认为 Core，即核心框架加载
     * @return Double         从加载到现在消耗的内存
     */
    public static function getUseMemory($title = 'Core')
    {
        $memory = 0;
        // 容错处理
        empty($title) && $title = 'Core';
        // 键名不区分大小写
        $title = strtoupper($title);

        // 校验是否有统计数据
        if (isset(self::$memories[$title])) {
            // 获取最后一次统计内存数据
            $len = count(self::$memories[$title]);
            // 获取到函数调用时的数据
            self::$memories[$title][] = function_exists('memory_get_usage') ? memory_get_usage() : 0;
            // 计算内存消耗
            $memory = (self::$memories[$title][$len] - self::$memories[$title][0]) / 2062336;
        }

        return $memory;
    }

    /**
     * 获取微秒级时间戳
     *
     * @return String 微秒时间戳（13 位时间戳）
     */
    public function getMiniTimeStamp() {
        list($sec, $miniSec) = explode(' ', microtime());
        $miniSec *= 1000;

        return $sec . $miniSec;
    }

    ///////////////////
    // 堆栈调试工具函数 //
    ///////////////////

    public static function begin($str)
    {
        echo '<br>' . $str . '开始<br>';
    }

    public static function end($str)
    {
        echo '<br>' . $str . '结束<br>';
    }
}
