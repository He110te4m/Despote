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

class MemCache extends Service implements Idata
{
    // 设置数据接口规范
    public function set($key, $value, $expiry = 99999999)
    {}
    // 批量设置数据接口规范
    public function mset($keys, $values, $expiry = 99999999)
    {}
    // 删除数据接口规范
    public function del($key)
    {}
    // 批量删除数据接口规范
    public function mdel($keys)
    {}
    // 获取数据接口规范
    public function get($key)
    {}
    // 批量获取数据接口规范
    public function mget($keys)
    {}
    // 查询数据是否存在接口规范
    public function has($key)
    {}
    public function flush()
    {}
}
