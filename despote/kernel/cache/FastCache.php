<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 快速缓存，基于内存的缓存，程序运行结束后失效，速度快、不能持久化
 * @author      He110 (i@he110.info)
 * @namespace   despote\kernel\cache
 */

namespace despote\kernel\cache;

class FastCache extends Cache
{
    // 缓存在内存中，读写更快
    private static $cache = [];

    /**
     * 添加数据，当键名以存在时不添加
     * @param String  $key    键名
     * @param String  $value  键值
     */
    public function add($key, $value, $expiry = 0)
    {
        !isset(self::$cache[$key]) && self::$cache[$key] = $value;
    }

    /**
     * 设置缓存数据
     * @param String  $key    键名
     * @param Mixed   $value  键值
     */
    public function set($key, $value, $expiry = 0)
    {
        self::$cache[$key] = $value;
    }

    /**
     * 删除缓存数据
     * @param  String $key 要删除的键名
     */
    public function del($key)
    {
        if (isset(self::$cache[$key])) {
            unset(self::$cache[$key]);
        }
    }

    /**
     * 获取数据接口规范
     * @param  String $key 键名
     * @return Mixed       键名对应的键值
     */
    public function get($key)
    {
        return isset(self::$cache[$key]) ? self::$cache[$key] : '';
    }

    /**
     * 查询是否存在缓存
     * @param  String  $key 键名
     * @return boolean      缓存是否存在
     */
    public function has($key)
    {
        return isset(self::$cache[$key]);
    }

    /**
     * 清空所有缓存
     */
    public function flush()
    {
        self::$cache = [];
    }
}
