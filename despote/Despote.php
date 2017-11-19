<?php
/**
 * 框架核心类
 */
class Despote
{
    /**
     * 框架核心加载方法
     */
    public static function run()
    {
        // 注册必要事件
        self::initEvent();
        // 统计现在的资源占用信息，方便在过程中获取
        Event::trigger('tick', '框架运行');
        // 开始核心方法
        self::initCore();
        // 统计最终运行时间
        Event::trigger('tick', '框架运行');
    }

    public static function initEvent()
    {
        // 加载配置文件
        $conf = require PATH_CONF . 'event.php';

        // 根据配置文件批量注册事件
        foreach ($conf as $event) {
            // 对配置项进行处理
            if (!isset($event['name']) || !isset($event['callback'])) {
                continue;
            }
            $only = isset($event['only']) ? $event['only'] : false;

            // 添加事件
            Event::hook($event['name'], $event['callback'], $only);
        }
    }

    public static function initCore()
    {
        // singleton
    }
}
