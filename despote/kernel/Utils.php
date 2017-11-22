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

    /////////////
    // 文件操作 //
    /////////////

    /**
     * 创建文件或文件夹
     * @param  String  $file  文件绝对地址(文件夹属于特殊文件)
     * @param  boolean $isDir 是否是文件夹，默认为 false
     * @param  integer $mode  文件权限
     * @return Boolean        创建成功返回 true，创建失败返回 false
     */
    public static function createFile($file, $isDir = false, $mode = 0775)
    {
        // 如果是目录，就判断目录是否不存在，如果是文件，就判断文件所在的目录是否不存在，只要有一个条件满足，就创建目录
        if ((isDir && !is_dir($file)) || (!isDir && !is_dir(dirname($file)))) {
            // 创建文件并给权限
            @mkdir($mdir, $mode, true);
            @chmod($mdir, $mode);
        }

        // 前面的 if 保证目录肯定存在，如果需要的是目录，可以直接返回了
        if (isDir) {
            return true;
        }

        // 创建空文件，如果成功，返回 true
        $fileHandle = @fopen($path, 'w');
        if ($fileHandle) {
            fclose($fileHandle);
            return true;
        }

        // 失败返回 false
        return false;
    }

    /**
     * 获取文件指定行数内容
     * @param  String $file 文件绝对路径
     * @param  Mixed  $line 读取单行传入行数，读取连续多行传入数组，如：[1, 100]
     * @param  string $mode 文件读取模式，默认为 rb
     * @return Mixed        读取单行返回字符串，读取多行返回字符串数组
     */
    public static function getFileLine($file, $line, $mode = 'rb')
    {
        // 文件反射对象
        $fileObj = new \SplFileObject($file, $mode);
        if (is_array($line) && count($line) > 1) {
            // 需要读取的文件内容
            $content = [];
            // 初始化相关变量
            $startLine = $line[0];
            $endLine   = $line[count($line) - 1];
            $count     = $endLine - $startLine;

            // 转到第 N 行, seek 方法参数从 0 开始计数
            $fileObj->seek($startLine - 1);
            for ($i = 0; $i <= $count; ++$i) {
                // current() 获取当前行内容
                $content[] = $fileObj->current();
                // 下一行
                $fileObj->next();
            }
        } else {
            // 判断 line 参数传入 [行数] 这种情况
            is_array($line) && $line = $line[0];
            // 转到需要读取的行
            $fileObj->seek($line - 1);
            // 获取内容
            $content = $fileObj->current();
        }

        return $content;
    }
}
