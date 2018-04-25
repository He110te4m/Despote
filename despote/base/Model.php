<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * 模型类，用于模型的封装，便于控制器加载调用
 * @author      He110 (i@he110.info)
 * @namespace   despote\base
 */

namespace despote\base;

use \Despote;
use \Exception;

class Model extends Service
{
    /**
     * 获取当前模块下的某个模型
     * @param  string $modelName 模型的类名
     * @return Mixed             成功返回模型对象，失败返回 false
     */
    public function getModel($modelName = 'common')
    {
        $obj    = false;
        $router = Despote::router();

        // 生成 Model 类名
        $class = APP . $router->getModule() . '\model\\' . ucfirst($modelName);

        // 反射获取 Medel 的对象
        try {
            $obj = new $class();
        } catch (Exception $e) {
            $uri = Despote::request()->getUri();
            throw new Exception("{$router->getModule()} 模块中的 {$modelName} 模型创建失败。调用的 URI 为：{$uri}", 1);
        }

        return $obj;
    }
}
