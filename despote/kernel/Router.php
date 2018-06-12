<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 路由解析类
 * @author      He110 (i@he110.info)
 * @namespace   despote\kernel
 */

namespace despote\kernel;

use \Despote;
use \despote\base\Service;
use \Event;
use \Exception;
use \Utils;

class Router extends Service
{
    // 内置默认模块
    protected $module = 'Home';
    // 内置默认控制器
    protected $controller = 'Index';
    // 内置默认方法
    protected $action = 'index';
    // 内置默认后缀名
    protected $suffix = 'html';
    // 是否进行模块绑定，绑定后只能使用设置好的模块进行访问，也就是单模块模式，路径直接填写：控制器/方法，即可
    // 如果不进行绑定，需要填写完整的路径：模块/控制器/方法
    protected $bindModule = false;
    // 内置默认域名绑定设置
    protected $host = [];
    // 现在的路由信息
    protected $router = [];
    // 绑定的特殊路由，形式为：用于匹配的 URL => 真实 URL，仅支持 get/post 传参
    protected $binds = [];

    /**
     * 初始化函数，开始校验与解析 URL
     */
    protected function init()
    {
        // 如果特殊路由配置文件存在则加载特殊路由配置
        file_exists(PATH_CONF . 'router.php') && $this->binds = require(PATH_CONF . 'router.php');

        $host = Despote::request()->getHost();
        if (!empty($this->host) && !isset($this->host[$host])) {
            // 域名绑定校验
            throw new Exception("Access Forbidden", 403);
        } else {
            // 校验通过，开始解析 URL
            $this->parse();
        }
    }

    /**
     * 解析 URL
     */
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

        // 尝试匹配特殊路由
        if (array_key_exists('/' . $path, $this->binds)) {
            $this->router = '/' . $path;
            return;
        }

        // 过滤后缀，伪静态设置
        $suffix = Despote::file()->getSuffix($path);
        if (!empty($suffix)) {
            if ($this->suffix == '' || $suffix == $this->suffix) {
                $path = rtrim(preg_replace('/.' . $suffix . '$/i', '', $path, 1), '/');
            } else {
                throw new Exception('URL 解析失败，请确认伪静态配置是否正确', 500);
                die;
            }
        }

        // 路由匹配
        $pathInfo = empty($path) ? [] : explode('/', $path);

        // 子目录支持
        $path_child = defined('PATH_CHILD') ? PATH_CHILD : '';
        // 判断是否需要进行子目录解析，提高效率
        if (!empty($path_child)) {
            $childDir = ltrim($path_child, '/');
            $childDirs = explode('/', $childDir);
            $i = 0;
            // 校验子目录是否匹配并处理子目录支持
            while(!empty($childDirs)) {
                if ($pathInfo[$i] == $childDirs[$i]) {
                    array_shift($pathInfo);
                    array_shift($childDirs);
                    ++$i;
                } else {
                    throw new Exception('子目录解析失败，请检查子目录配置', 500);
                    die;
                }
            }
        }

        // 当 URL 参数少于三个，则必定是 domain/controller/action 这种形式，所以直接使用默认模块
        // 当 URL 参数大于等于三个，必定是传参或者是 domain/moduel/controller/action，为了规范，当需要传参是时候，必须编写完整 URL，或者使用 get/post 传参
        $module     = (count($pathInfo) < 3) ? $this->module : ($this->bindModule ? $this->module : array_shift($pathInfo));
        $controller = empty($pathInfo) ? $this->controller : array_shift($pathInfo);
        $action     = empty($pathInfo) ? $this->action : array_shift($pathInfo);

        // 将参数全部转化为 GET 数组的成员
        while ($pathInfo) {
            $key = array_shift($pathInfo);
            $val = array_shift($pathInfo);
            // 防止参数个数为奇数，即只有键名没有键值的情况，故不使用 array_merge
            $_GET[$key] = $val === null ? '' : $val;
        }

        $this->router = [
            'module'     => ucfirst($module),
            'controller' => ucfirst($controller),
            'action'     => $action,
        ];

        Despote::request()->load($_GET);
    }

    /**
     * 获取当前使用的 Module
     * @return  String  当前使用的模块名
     */
    public function getModule()
    {
        return isset($this->router['module']) ? $this->router['module'] : $this->module;
    }

    /**
     * 获取当前使用的 Ctroller
     * @return  String  当前使用的控制器名
     */
    public function getCtrl()
    {
        return isset($this->router['controller']) ? $this->router['controller'] : $this->controller;
    }

    /**
     * 获取当前使用的 Action
     * @return  String  当前使用的动作名
     */
    public function getAction()
    {
        return isset($this->router['action']) ? $this->router['action'] : $this->action;
    }

    /**
     * 加载控制器
     */
    public function loadCtrl()
    {
        $http = Despote::request();

        // 根据路由类型初始化 Controller 和 Action
        if(is_array($this->router)) {
            // 开始正常 MVC 初始化
            $class = '\\' . APP . '\\' . $this->getModule() . '\\controller\\' . $this->getCtrl();
            $action = $this->getAction();
        } else {
            // 开始特殊路由初始化
            $class = $this->binds[$this->router]['ctrl'];
            $action = $this->binds[$this->router]['action'];
        }

        // 反射获取 action 的参数并将值存在数组中
        try {
            $obj = new \ReflectionClass($class);
        } catch (Exception $e) {
            if (Utils::config('debug', false)) {
                throw new Exception("{$class} 控制器中的 {$action} 方法调用失败。调用的 URI 为：{$http->getUri()}", 1);
            }
            die;
        }
        $func = $obj->getMethod($action);

        // 装载 Action 参数，兼容 Action 传参
        $params = [];
        foreach ($func->getParameters() as $param) {
            $val                  = $http->get($param->name, false);
            $params[$param->name] = $val === false ? '' : $val;
        }

        // 判断是否为公有可调用的 Action
        if ($func->isPublic()) {
            // 中间件支持
            Event::trigger('BEFORE_ACTION');
            // 实例化控制器并调用 action
            call_user_func_array([new $class(), $action], $params);
            // 中间件支持
            Event::trigger('AFTER_ACTION');
        }
    }

    /**
     * 根据资源生成 URL
     *
     * @param   String  $ctrl    加载的控制器或文件于根目录的相对地址
     * @param   String  $action  加载的动作
     * @param   Array   $params  加载的参数
     * @param   String  $module  加载的模块
     * @return  String           返回生成的 URL
     */
    public function createUrl($ctrl = 'Index', $action = 'index', $params = [], $module = 'Home')
    {
        if (file_exists(PATH_ROOT . $ctrl)) {
            // 如果文件存在，则静态链接
            $uri = Despote::request()->getHost(true) . '/' . $ctrl;
        } else {
            // 如果文件不存在，根据路由规则生成
            $uri = Despote::request()->getHost(true);
            // 参数配置
            $params = empty($params) ? '' : '?' . http_build_query($params);

            // 后缀绑定设置
            empty($this->suffix) || $action = $action . '.' . $this->suffix;

            $uri .= '/' . $module . '/' . $ctrl . '/' . $action . $params;
        }

        return $uri;
    }
}
