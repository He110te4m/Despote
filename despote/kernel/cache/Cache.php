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

use \despote\base\Idata;
use \despote\base\Service;

abstract class Cache extends Service implements Idata
{
    // 添加缓存数据接口规范
    abstract public function add($key, $value, $expiry = 0);

    /**
     * 批量添加缓存数据，当键值数组的元素个数比键名数组中元素少时，使用键值数组最后一个元素作为其余键名的值
     * @param  Array   $keys   键名数组，必须为索引数组
     * @param  Array   $values 键值数组，必须为索引数组
     */
    public function madd($key, $value, $expiry = 0)
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

    // 设置数据接口规范
    abstract public function set($key, $value, $expiry = 259200);

    /**
     * 批量设置数据，如果键名已经存在，则直接覆盖
     * @param  Array   $keys   键名数组
     * @param  Mixed   $values 键值数组
     * @param  integer $expiry 过期时间
     */
    public function mset($keys, $values, $expiry = 259200)
    {
        if (is_array($keys)) {
            // 如果是数组，为了防止键值数组元素个数比键名数组元素个数少，将最后一个值取出，多出的键名全部使用值数组最后一个元素作为值
            $val = $values[count($values) - 1];
            for ($i = 0; $i < count($keys); $i++) {
                $value = isset($values[$i]) ? $values[$i] : $val;
                $this->set($keys[$i], $value, $expiry);
            }
        } else {
            // 如果不是数组直接设置并返回
            $this->set($keys, $value);
        }
    }

    // 删除数据接口规范
    abstract public function del($key);

    /**
     * 批量删除数据
     * @param  Array  $keys 键名数组
     */
    public function mdel($keys)
    {
        foreach ($keys as $key) {
            $this->del($key);
        }
    }

    // 获取数据接口规范
    abstract public function get($key);

    /**
     * 批量获取数据
     * @param  Array  $keys 键名数组
     */
    public function mget($keys)
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }

        return $result;
    }

    // 查询数据是否存在接口规范
    abstract public function has($key);

    // 刷新所有缓存
    abstract public function flush();
}
