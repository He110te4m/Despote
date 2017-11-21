<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 所有组件类的父类
 * @author      He110 (i@he110.top)
 * @namespace   despote\base
 */

namespace despote\base;

class Service
{
    /**
     * 使用父类构造函数为子类分配配置
     * @param array $params 配置数组
     */
    final public function __construct($params = [])
    {
        foreach ($params as $param => $value) {
            $this->$param = $value;
        }
        $this->init();
    }

    /**
     * 子类实例化后自动调用的方法，子类可以继承并修改
     */
    protected function init()
    {}
}
