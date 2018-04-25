<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 视图类，用于加载视图
 * @author      He110 (i@he110.info)
 * @namespace   despote\base
 */

namespace despote\base;

use \Despote;

class View extends Service
{
    private $vars = [];

    protected function init()
    {
        defined('RES') || define('RES', '/static/');
    }

    /**
     * 变量分配
     * @param  String $key   变量名
     * @param  Mixed  $value 变量值
     */
    public function assign($key, $value)
    {
        $this->vars[$key] = $value;
    }

    /**
     * 渲染页面
     * @param  string $viewName     视图文件名
     * @param  array  $viewParams   视图中的变量映射
     * @param  string $layoutName   布局文件名
     * @param  array  $layoutParams 布局中的变量映射
     */
    public function render($viewName = '', $viewParams = [], $layoutName = '', $layoutParams = [])
    {
        // 获取当前模块的视图目录
        $path = PATH_APP . Despote::router()->getModule() . DS . 'view' . DS;
        // 获取视图文件的绝对路径
        $view = $path . $viewName;

        // 读入视图文件内容
        $viewParams = array_merge($this->vars, $viewParams);
        $content    = $this->renderView($view, $viewParams);
        // 判断是否加载布局
        echo empty($layoutName) ? $content : $this->renderView(
            $path . 'layout' . DS . $layoutName,
            array_merge($this->vars, $layoutParams, ['container' => $content])
        );
    }

    /**
     * 视图渲染，用于变量分配
     * @param  String $view   视图文件名，绝对路径
     * @param  array  $params 视图中的变量
     * @return String         渲染后的文件内容
     */
    private function renderView($view, $params = [])
    {
        // 开启输出缓存
        ob_start();
        // 开启绝对刷送，即每次操作都会自动 flush，无需手动使用 flush
        ob_implicit_flush(false);
        // 如果参数表不为空则分配变量
        empty($params) || extract($params);
        // 包含视图文件
        require $view;

        // 获取 PHP 解析后的视图文件并作为字符串返回
        return ob_get_clean();
    }
}
