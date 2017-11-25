<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * @author      He110 (i@he110.top)
 * @namespace   app\Home\controller
 */
namespace app\Home\controller;

use \Despote;
use \despote\base\Controller;

class Index extends Controller
{
    public function index()
    {
        Despote::logger()->log('INFO', '测试 info');
        Despote::logger()->log('Debug', '测试 Debug');
        Despote::logger()->log('Warn', '测试 Warn');
        Despote::logger()->log('error', '测试 error');
        Despote::logger()->log('fatal', '测试 fatal');
        $this->render();
    }

    public function home()
    {
        echo "我是 home";
    }
}
