<?php

namespace despote\base;

/**
 * 所有组件类的父类
 */
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
