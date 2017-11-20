<?php

namespace despote\base;

/**
 * 控制器基类
 */
class Controller
{
    protected $view;

    private function getView()
    {
        // 如果视图不存在就加载视图
        is_null($this->view) && $this->view = new View();

        return $this->view;
    }

    protected function render($viewName = '', $viewParams = [], $layoutName = '', $layoutParams = [])
    {
        // 默认使用 index.php 为视图
        empty($viewName) && $viewName = 'index.php';

        // 渲染视图
        $this->getView()->render($viewName, $viewParams, $layoutName, $layoutParams);
    }
}
