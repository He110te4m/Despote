<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 所有数据操作相关的类需要遵循的接口规范
 * @author      He110 (i@he110.top)
 * @namespace   despote\base
 */

namespace despote\base;

interface Idata
{
    // 获取数据接口规范
    function get($key);
    // 设置数据接口规范
    function set($key, $value, $expiry = 99999999);
    // 删除数据接口规范
    function del($key);
    // 查询数据是否存在接口规范
    function has($key);
}
