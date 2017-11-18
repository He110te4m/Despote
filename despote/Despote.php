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
        Event::trigger('tick', '框架运行');
    }

    public static function initEvent()
    {
        // 加载配置文件
        $conf = require PATH_CONF . 'event.php';

        // 根据配置文件批量注册事件
        foreach ($conf as $event) {
            // 去除非法配置
            if (!isset($event['name']) || !isset($event['callback'])) {
                continue;
            }
            // 添加事件
            Event::listen($event['name'], $event['callback']);
        }
    }

    public static function initCore()
    {
        // singleton
    }

    public static function test()
    {
        echo "测试调用静态方法成功";
    }
}
