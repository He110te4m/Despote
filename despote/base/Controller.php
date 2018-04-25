<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 控制器基类
 * @author      He110 (i@he110.info)
 * @namespace   despote\base
 */

namespace despote\base;

class Controller extends Service
{
    protected $view;
    protected $model;

    private function getView()
    {
        // 如果视图不存在就加载视图
        is_null($this->view) && $this->view = new View();

        return $this->view;
    }

    protected function getModel($modelName = 'common')
    {
        is_null($this->model) && $this->model = new Model();

        return $this->model->getModel($modelName);
    }

    protected function assign($key, $value)
    {
        $this->getView()->assign($key, $value);
    }

    protected function render($viewName = 'index.php', $viewParams = [], $layoutName = '', $layoutParams = [])
    {
        // 默认使用 index.php 为视图
        empty($viewName) && $viewName = 'index.php';

        // 渲染视图
        $this->getView()->render($viewName, $viewParams, $layoutName, $layoutParams);
    }
}
