<?php

namespace despote\base;

use Despote;

class View
{
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
