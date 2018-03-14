<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 路由解析类
 * @author      He110 (i@he110.top)
 * @namespace   despote\kernel
 */

namespace despote\kernel;

use \Despote;
use \despote\base\Service;
use \Event;
use \Exception;

class Router extends Service
{
    // 内置默认模块
    protected $module = 'Home';
    // 内置默认控制器
    protected $controller = 'Index';
    // 内置默认方法
    protected $action = 'index';
    // 是否进行模块绑定，绑定后只能使用设置好的模块进行访问，也就是单模块模式，路径直接填写：控制器/方法，即可
    // 如果不进行绑定，需要填写完整的路径：模块/控制器/方法
    protected $bindModule = false;
    // 内置默认域名绑定设置
    protected $host = [];
    // 现在的路由信息
    protected $router = [];

    protected function init()
    {
        $host = Despote::request()->getHost();
        if (!empty($this->host) && !isset($this->host[$host])) {
            // 域名绑定校验
            throw new \Exception("Access Forbidden", 403);
            return;
        }
        // 校验通过，开始解析 URL
        $this->parse();
    }

    public function parse()
    {
        // 解析原生 URL 请求地址，这样能区分出用户自己带上的 GET 参数
        $urlInfo = parse_url(Despote::request()->getUri());
        // 使用正则去除多余的斜杠
        $path = preg_replace('/([^:])[\/\\\\]{2,}/', '$1/', $urlInfo['path']);
        // 使用正则匹配去除可能存在的 index.php
        $path = trim(preg_replace('/^(\/)?index\.php/i', '', $path, 1), '/');
        // 获取 GET 参数
        parse_str(isset($urlInfo['query']) ? $urlInfo['query'] : '', $_GET);

        // 路由匹配
        $pathInfo = empty($path) ? [] : explode('/', $path);

        // 当 URL 参数少于三个，则必定是 domain/controller/action 这种形式，所以直接使用默认模块
        // 当 URL 参数大于等于三个，必定是传参或者是 domain/moduel/controller/action，为了规范，当需要传参是时候，必须编写完整 URL，或者使用 get/post 传参
        $module     = (count($pathInfo) < 3) ? $this->module : ($this->bindModule ? $this->module : array_shift($pathInfo));
        $controller = empty($pathInfo) ? $this->controller : array_shift($pathInfo);
        $action     = empty($pathInfo) ? $this->action : array_shift($pathInfo);

        // 将参数全部转化为 GET 数组的成员
        while ($pathInfo) {
            $key = array_shift($pathInfo);
            $val = array_shift($pathInfo);
            // 防止参数个数为奇数，即只有键名没有键值的情况
            $_GET[$key] = $val === null ? '' : $val;
        }

        $this->router = [
            'module'     => ucfirst($module),
            'controller' => ucfirst($controller),
            'action'     => $action,
        ];

        Despote::request()->load($_GET);
    }

    public function getModule()
    {
        return isset($this->router['module']) ? $this->router['module'] : $this->module;
    }

    public function getCtrl()
    {
        return isset($this->router['controller']) ? $this->router['controller'] : $this->controller;
    }

    public function getAction()
    {
        return isset($this->router['action']) ? $this->router['action'] : $this->action;
    }

    public function loadCtrl()
    {
        $http = Despote::request();

        // 获取控制器对应的类
        $class = APP . $this->getModule() . '\controller\\' . $this->getCtrl();

        // 反射获取 action 的参数并将值存在数组中
        try {
            $obj = new \ReflectionClass($class);
        } catch (Exception $e) {
            throw new Exception("{$this->getModule()} 模块中的 {$this->getCtrl()} 控制器中的 {$this->getAction()} 方法调用失败。调用的 URI 为：{$http->getUri()}", 1);
        }
        $func   = $obj->getMethod($this->getAction());
        $params = [];
        foreach ($func->getParameters() as $param) {
            $val                  = $http->get($param->name, false);
            $params[$param->name] = $val === false ? '' : $val;
        }
        if ($func->isPublic()) {
            Event::trigger('BEFORE_ACTION');
            // 实例化控制器并调用 action
            call_user_func_array([new $class(), $this->getAction()], $params);
            Event::trigger('AFTER_ACTION');
        }
    }
}
