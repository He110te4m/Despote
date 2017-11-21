<?php
/**
 * 框架核心类
 */
class Despote
{
    // 实例化过的服务实例
    private static $instances = [];
    // 注册的服务实例，在 \despote\conf\core.php 文件中配置
    private static $services = [];

    /**
     * 框架核心加载方法
     */
    public static function run()
    {
        // 注册必要事件
        self::initEvent();

        // 统计现在的资源占用信息，方便在过程中获取
        Event::trigger('TICK', '框架核心');
        // 开始核心方法
        self::initCore();
        // 加载配置文件配置
        Event::trigger('INIT_CONFIG');
        // 开始路由分发
        self::router()->loadCtrl();
        // 统计最终运行时间
        Event::trigger('TICK', '框架核心');
    }

    /**
     * 根据配置文件注册事件，配置文件位置：\despote\conf\event.php
     */
    private static function initEvent()
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

    /**
     * 根据配置文件加载系统服务
     */
    private static function initCore()
    {
        $services = require PATH_CONF . 'services.php';
        // 必备服务，不管配置文件是否加载都需要加载的文件
        $core = [
            'request' => '\despote\kernel\Request',
            'router'  => '\despote\kernel\Router',
        ];
        // 加载系统服务
        self::$services = array_merge($core, $services);
    }

    /**
     * 使用 Service Locator 获取组件对象
     * @param  String $id 组件注册时的 ID
     * @return Object     组件初始化后的对象
     */
    public static function getIns($id)
    {
        if (isset(self::$instances[$id])) {
            // 如果已经有初始化好的服务对象直接返回，类似于单例设计模式
            return self::$instances[$id];
        } else if (isset(self::$services[$id])) {
            // 如果没有初始化好的服务对象，判断是否有注册这个服务
            if (is_array(self::$services[$id])) {
                // 如果是数组，直接获取类名
                $class = self::$services[$id]['class'];
                // 去除类名，表示已经初始化过了
                unset(self::$services[$id]['class']);
                // 初始化服务并丢到对象池中
                self::$instances[$id] = new $class(self::$services[$id]);
            } else {
                // 如果不是数组，就没有配置项，直接创建
                self::$instances[$id] = new self::$services[$id]();
            }

            return self::$instances[$id];
        } else {
            // 没有注册服务直接抛出异常
            throw new Exception("Unkonw Service ID: $id", 500);
        }
    }

    /**
     * 加入静态魔术方法，方便 getIns 的调用，如：
     * 获取 request 时，只需要 \Despote::request()，同时支持链式操作
     * @param  String $name Service 的 ID
     * @return Object       调用了 self::getIns($name) 之后返回的对象
     */
    public static function __callStatic($name, $args)
    {
        return call_user_func_array('self::getIns', [$name]);
    }
}
