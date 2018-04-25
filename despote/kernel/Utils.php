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
    // 资源统计 //
    ////////////

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

    /////////////
    // 加密解密 //
    ////////////

    /**
     * 使用 key 加密字符串
     * @param  String  $data   密码
     * @param  String  $key    key
     * @param  integer $expire 过期时间，如果为 0 永不过期
     * @return String          密钥
     */
    public static function encrypt($data, $key, $expire = 0)
    {
        // 键名不需要解密，所以可以使用不可逆算法加密
        $key = md5($key);
        // 数据需要解密，所以需要使用可逆算法，为了速度，采用 base64 加密
        $data = base64_encode($data);
        // 加密后的键名长度
        $keyLen = strlen($key);
        // 加密后的数据长度
        $dataLen = strlen($data);
        // 加密时的 key 字符串索引
        $keyIndex = 0;
        // 为了加密而拼凑出来的 key
        $tempKey = '';

        // 根据加密后的键名循环获取，获得和数据一样长的 key 串
        for ($i = 0; $i < $dataLen; ++$i, ++$keyIndex) {
            // 如果已经遍历完 key 了，就重新再遍历一次
            if ($keyIndex == $keyLen) {
                $keyIndex = 0;
            }
            // 获取指定索引的 key 对应的字符
            $tempKey .= substr($key, $keyIndex, 1);
        }

        // 在密钥开头标明过期时间，长度是 10 位，和时间戳的长度一致
        $str = sprintf('%010d', $expire ? $expire + time() : 0);
        // 生成密钥
        for ($i = 0; $i < $dataLen; $i++) {
            // 使用 data 中该位置的字符的 ASCII 码和 key 中该位置的字符的 ASCII 码的和，作为新的密钥
            $str .= chr(ord(substr($data, $i, 1)) + (ord(substr($tempKey, $i, 1))) % 256);
        }
        // 再次进行 base64 加密
        $str = base64_encode($str);
        // 替换敏感字符
        $str = str_replace(['=', '+', '/'], ['O0O0O', 'o000o', 'oo00o'], $str);

        // 返回密钥
        return $str;
    }

    /**
     * 解密密钥
     * @param  String $data 密钥
     * @param  String $key  key
     * @return String       密码
     */
    public static function decrypt($data, $key)
    {
        // 敏感字符替换处理
        $data = str_replace(['O0O0O', 'o000o', 'oo00o'], ['=', '+', '/'], $data);
        // 加密 key，用于还原密钥
        $key = md5($key);
        // 初次处理过的密钥
        $data = base64_decode($data);
        // 加密时的 key 字符串索引
        $keyIndex = 0;
        // 获取过期时间
        $expire = substr($data, 0, 10);
        // 获取真实密钥
        $data = substr($data, 10);
        // 如果密钥过期，直接返回 null
        if ($expire > 0 && $expire < time()) {
            return null;
        }
        // 加密后的数据长度
        $dataLen = strlen($data);
        // 加密后的 key 长度
        $keyLen = strlen($key);
        // 初始化临时 key
        $tempKey = '';
        // 初始化密码
        $str = '';

        // 获取临时 key
        for ($i = 0; $i < $dataLen; $i++) {
            // 和加密一样，循环获取 key 字符串的值
            if ($keyIndex == $keyLen) {
                $keyIndex = 0;
            }

            // 拼凑临时 key
            $tempKey .= substr($key, $keyIndex, 1);
            $keyIndex++;
        }

        // 解密数据
        for ($i = 0; $i < $dataLen; $i++) {
            // 确保运算后的 ASCII 码比 0 大并且有意义
            if (ord(substr($data, $i, 1)) < ord(substr($tempKey, $i, 1))) {
                $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($tempKey, $i, 1)));
            } else {
                $str .= chr(ord(substr($data, $i, 1)) - ord(substr($tempKey, $i, 1)));
            }
        }

        // 返回密码
        return base64_decode($str);
    }

    /////////////
    // 配置加载 //
    ////////////

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
        Event::trigger('ERROR_CATCH_ON');
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
}
