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

use \despote\base\Icache;
use \despote\base\Service;
use \Memcached;

class MemCache extends Service implements Icache
{
    // MemCache 服务器列表
    protected $servers;
    // 服务器用户名
    protected $usr;
    // 服务器密码
    protected $pwd;
    // MemCached 对象单例
    private $ins;

    /**
     * 在构造函数中自动调用的初始化函数
     */
    public function init()
    {
        $this->getIns()->addServers($this->servers);
    }

    /**
     * 获取 MemCached 单例
     * @return Object MemCached 类的实例
     */
    private function getIns()
    {
        if (is_null($this->ins)) {
            $ins = &$this->ins;
            $ins = new Memcached();
            $ins->setOptions([
                // 使用一致性分布算法实现分布式缓存，稳定性较好
                Memcached::OPT_DISTRIBUTION         => Memcached::DISTRIBUTION_CONSISTENT,
                // 使用带权一致性分布算法，由于每台服务器性能不同，所以需要给予不同的权重
                Memcached::OPT_LIBKETAMA_COMPATIBLE => true,
            ]);
        }

        return $this->ins;
    }

    public function add($key, $value, $expiry = 0)
    {
        $this->has($key) || $this->set($key, $value);
    }

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
    public function set($key, $value, $expiry = 259200)
    {
        return $this->getIns()->set($key, $value, $expiry > 0 ? time() + $expiry : 0);
    }
    // 批量设置数据接口规范
    public function mset($keys, $values, $expiry = 259200)
    {
        if (is_array($key)) {
            // 如果是数组，为了防止键值数组元素个数比键名数组元素个数少，将最后一个值取出，多出的键名全部使用值数组最后一个元素作为值
            $val = $values[count($values) - 1];
            for ($i = 0; $i < count($key); $i++) {
                $value = isset($values[$i]) ? $values[$i] : $val;
                $this->set($keys[$i], $value, $expiry);
            }
        } else {
            // 如果不是数组直接设置并返回
            $this->set($key, $value);
        }
    }
    // 删除数据接口规范
    public function del($key)
    {
        return $this->getIns()->delete($key);
    }
    // 批量删除数据接口规范
    public function mdel($keys)
    {
        foreach ($keys as $key) {
            $this->del($key);
        }
    }
    // 获取数据接口规范
    public function get($key)
    {
        return $this->getIns()->get($key);
    }
    // 批量获取数据接口规范
    public function mget($keys)
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }

        return $result;
    }
    // 查询数据是否存在接口规范
    public function has($key)
    {
        // 直接获取值
        $this->get($key);
        // 返回获取是否成功
        return $this->getIns()->getResultCode() === Memcached::RES_SUCCESS;
    }
    public function flush()
    {
        return $this->getIns()->flush();
    }
}
