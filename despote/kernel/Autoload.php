<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 自动加载类，遵循 PSR4 自动加载，不满足 PSR4 自动加载规范的请在 \despote\conf\autoload.php 文件中配置
 * @author      He110 (i@he110.info)
 * @namespace   despote\kernel
 */

namespace despote\kernel;

class AutoLoad
{
    // 根据配置文件(\despote\conf\autoload.php)自动加载类名和对应的文件映射表
    private static $classMap = [];

    /**
     * 注册自动加载函数
     */
    public static function register()
    {
        // 加载配置文件中的 类 <=> 文件映射表
        self::$classMap = require PATH_CONF . 'autoload.php';
        // 注册 PSR4 自动加载
        spl_autoload_register(['\despote\kernel\AutoLoad', 'loadByPSR4']);
        // 注册根据配置文件自动加载
        spl_autoload_register(['\despote\kernel\AutoLoad', 'loadByConf']);
    }

    /**
     * 注册自动加载特殊类
     * @param   String   $class  需要自动加载的类名
     * @param   String   $path   类文件所在的文件路径
     * @return  Boolean          注册成功返回 true，否则返回 false
     */
    public static function regClass ($class, $path) {
        if (is_string($class) && is_string($path)) {
            self::$classMap[$class] = $path;
            $result = true;
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * 根据 PSR4 自动加载类
     * @param  String $class 类名
     */
    private static function loadByPSR4($class)
    {
        // 如果已经加载了就跳过，第二个参数必须设置为 false，否则会自动尝试加载这个类，而这个类文件没有包含进来，会调用自动加载函数，从而造成死循环
        if (class_exists($class, false)) {
            return;
        }

        // 自动补全命名空间
        if ($class[0] != '\\') {
            $class = '\\' . $class;
        }

        // 拼接文件绝对路径
        $path = PATH_ROOT . str_replace('\\', '/', $class) . '.php';
        // 文件存在则加载，否则忽略
        file_exists($path) && require $path;
    }

    /**
     * 根据配置文件加载对应的类，可以省略命名空间的书写
     * @param  String $class 类名
     */
    private static function loadByConf($class)
    {
        // 如果已经加载了就跳过，第二个参数必须设置为 false，否则会自动尝试加载这个类，而这个类文件没有包含进来，会调用自动加载函数，从而造成死循环
        if (class_exists($class, false)) {
            return;
        }

        // 遍历内置映射表
        foreach (self::$classMap as $name => $path) {
            // 判断是否是需要加载的类
            if ($class === $name) {
                // 判断文件是否存在，存在则加载并结束函数调用
                if (is_file($path)) {
                    require $path;
                    return;
                }
            }
        }
    }
}
