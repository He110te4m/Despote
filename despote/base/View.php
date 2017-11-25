<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 视图类，用于加载视图
 * @author      He110 (i@he110.top)
 * @namespace   despote\base
 */

namespace despote\base;

use Despote;
use despote\base\Service;

class View extends Service
{
    protected function init()
    {
        defined('PATH_RES') || define('PATH_RES', Despote::request()->getHost() . 'static' . DS);
    }

    public function render($viewName = '', $viewParams = [], $layoutName = '', $layoutParams = [])
    {
        // 获取当前模块的视图目录
        $path = PATH_APP . Despote::router()->getModule() . DS . 'view' . DS;
        // 获取视图文件的绝对路径
        $view = $path . $viewName;

        // 读入视图文件内容
        $content = $this->renderView($view, $viewParams);
        // 判断是否加载布局
        echo empty($layoutName) ? $content : $this->renderView(
            $path . 'layout' . DS . $layoutName,
            array_merge($layoutParams, ['container' => $content])
        );
    }

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
