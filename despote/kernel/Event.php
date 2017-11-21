<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 事件触发类
 * @author      He110 (i@he110.top)
 * @namespace   despote\kernel
 */

class Event
{
    // 事件列表
    private static $events = [];

    /**
     * 注册事件，也就是事件钩子
     * @param  String   $event       事件唯一标记名，类似于：AFTER_LOAD
     * @param  callback $callback    事件发生时的回调函数
     * @param  boolean  $onlyOneTime 是否只执行一次
     * @return boolean               注册成功或者失败
     */
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

    /**
     * 触发事件
     * @return Mixed 如果未传入参数直接结束调用，如果事件未注册返回 false，成功调用返回数组，数组成员为每个事件的回调函数的返回信息
     */
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

        // 初始化函数调用结果
        $results = [];

        foreach (self::$events[$event] as $index => $item) {
            // 取得要执行的函数或者匿名方法这里其实应该加个判断
            $callback = $item['callback'];
            // 判断是不是只执行一次，是就执行完注销事件
            $item['onlyOneTime'] && self::unhook($event, $index);
            // 执行函数，不要解析太多，后面跟的是方法和参数名，这里可以任意发挥！
            $results[] = call_user_func_array($callback, $args);
        }

        return $results;
    }
}
