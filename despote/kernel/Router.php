<?php

/**
 * 路由解析类
 */
namespace despote\kernel;

use \Despote;
use \despote\base\Service;

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
    protected $bindModule = true;
    // 内置默认域名绑定设置
    protected $host = [];
    // 现在的路由信息
    protected $router = [];

    protected function init()
    {
        if (!empty($this->host)) {
            // 域名绑定校验
            $host = Despote::request()->getHost();
            if (!isset($this->host[$host])) {
                throw new \Exception("Access Forbidden", 403);
                return;
            }
        }
        // 校验通过，开始解析
        $this->parse();
        $this->loadCtrl();
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
        // echo $path;

        $pathInfo   = explode('/', $path);
        $module     = $this->bindModule ? $this->module : array_shift($pathInfo);
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

    private function loadCtrl()
    {
        // 获取控制器对应的类
        $class = '\app\\' . $this->getModule() . '\controller\\' . $this->getCtrl();

        // 反射获取 action 的参数并将值存在数组中
        $obj   = new \ReflectionClass($class);
        $func  = $obj->getMethod($this->getAction());
        $parms = [];
        foreach ($func->getParameters() as $parm) {
            $val = Despote::request()->get($parm->name, false);
            if ($val) {
                $parms[$parm->name] = $val;
            } else {
                $parms[$parm->name] = null;
            }
        }

        // 实例化控制器并调用 action
        call_user_func_array([new $class(), $this->getAction()], $parms);
    }
}
