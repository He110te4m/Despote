<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 缓存类
 * @author      He110 (i@he110.top)
 * @namespace   despote\kernel\cache
 */

namespace despote\kernel\cache;

use despote\base\Idata;
use despote\base\Service;

class FastCache extends Service implements Idata
{
    // 缓存在内存中，读写更快
    private static $cache = [];

    /**
     * 添加缓存数据
     * @param String  $key    键名
     * @param Mixed   $value  键值
     */
    public function set($key, $value, $expiry = 0)
    {
        self::$cache[$key] = $value;
    }

    /**
     * 批量添加缓存数据，当键值数组的元素个数比键名数组中元素少时，使用键值数组最后一个元素作为其余键名的值
     * @param  Array   $keys   键名数组，必须为索引数组
     * @param  Array   $values 键值数组，必须为索引数组
     */
    public function mset($keys, $values, $expiry = 0)
    {
        if (is_array($key)) {
            // 如果是数组，为了防止键值数组元素个数比键名数组元素个数少，将最后一个值取出，多出的键名全部使用值数组最后一个元素作为值
            $val = $values[count($values) - 1];
            for ($i = 0; $i < count($key); $i++) {
                $value = isset($values[$i]) ? $values[$i] : $val;
                $this->set($keys[$i], $value);
            }
        } else {
            // 如果不是数组直接设置并返回
            $this->set($key, $value);
        }
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
     * 批量删除数据接口规范
     * @param  String $keys 键名数组，必须是关联数组
     */
    public function mdel($keys)
    {
        foreach ($keys as $key) {
            $this->del($key);
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
     * 批量获取数据接口规范
     * @param  String $keys 键名数组
     * @return Array        键名数组对应的键值数组，返回的将是 [键名 => 键值] 关联数组
     */
    public function mget($keys)
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }

        return $result;
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
