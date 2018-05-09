<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * Memcached 缓存类，基于 Memcached 缓存，支持分布式缓存，Windows 下无法使用（Windows 下的 PHP 不能使用 Memcached 扩展）
 * @author      He110 (i@he110.info)
 * @namespace   despote\kernel\cache
 */

namespace despote\kernel\cache;

use \Memcached;

class MemCache extends Cache
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

    /**
     * 添加数据，当键名已存在时不添加
     * @param String   $key    键名
     * @param Mixed    $value  键值
     * @param Integer  $value  缓存有效时间，单位为 s(秒)，默认为 3 天
     */
    public function add($key, $value, $expiry = 0)
    {
        $this->has($key) || $this->set($key, $value);
    }

    /**
     * 设置缓存数据
     * @param String   $key    键名
     * @param Mixed    $value  键值
     * @param Integer  $value  缓存有效时间，单位为 s(秒)，默认为 3 天
     */
    public function set($key, $value, $expiry = 0)
    {
        return $this->getIns()->set($key, $value, $expiry > 0 ? time() + $expiry : 0);
    }

    /**
     * 删除缓存数据
     * @param  String $key 要删除的键名
     */
    public function del($key)
    {
        return $this->getIns()->delete($key);
    }

    /**
     * 获取数据
     * @param  String $key  键名
     * @return Mixed        键名对应的键值
     */
    public function get($key)
    {
        return $this->getIns()->get($key);
    }

    /**
     * 查询数据是否存在
     * @param  String   $key  需要判断是否存在缓存的键名
     * @return Boolean        是否存在此缓存
     */
    public function has($key)
    {
        // 直接获取值
        $this->get($key);
        // 返回获取是否成功
        return $this->getIns()->getResultCode() === Memcached::RES_SUCCESS;
    }

    /**
     * 清空所有缓存
     */
    public function flush()
    {
        return $this->getIns()->flush();
    }
}
