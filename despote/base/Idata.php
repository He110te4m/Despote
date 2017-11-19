<?php

namespace despote\base;

/**
 * 所有数据操作相关的类需要遵循的接口规范
 */
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
