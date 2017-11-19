<?php

/**
 * 事件触发类
 */
class Event
{
    // 事件列表
    private static $events = [];

    public static function hook($event, $callback, $onlyOneTime = false)
    {
        // 判断回调处理函数能否被调用，不能就不添加了
        if (!is_callable($callback)) {
            return false;
        }

        // 添加事件
        self::$events[$event][] = [
            'callback'    => $callback,
            'onlyOneTime' => $onlyOneTime,
        ];

        // 添加成功
        return true;
    }

    /**
     * 注销事件
     * @param  String  $event 事件名称
     * @param  integer $index 第几个回调函数
     */
    public static function unhook($event, $index = null)
    {
        if (is_null($index)) {
            unset(self::$events[$event]);
        } else {
            unset(self::$events[$event][$index]);
        }
    }

    public static function trigger()
    {
        // 如果没有传入参数，直接返回
        if (!func_num_args()) {
            return;
        }

        // 获取参数
        $args = func_get_args();
        // 获取事件名称，即第一个参数
        $event = array_shift($args);
        // 检测该事件有没有被注册
        if (!isset(self::$events[$event])) {
            return false;
        }

        foreach (self::$events[$event] as $index => $item) {
            // 取得要执行的函数或者匿名方法这里其实应该加个判断
            $callback = $item['callback'];
            // 判断是不是只执行一次，是就执行完注销事件
            $item['onlyOneTime'] && self::unhook($event, $index);
            // 执行函数，不要解析太多，后面跟的是方法和参数名，这里可以任意发挥！
            call_user_func_array($callback, $args);
        }
    }
}
