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
        Despote::fileCache()->set('usr', 'He110');
        Despote::fileCache()->set('pwd', '520520520');
        echo Despote::fileCache()->get('usr');
        echo "<br>";
        echo Despote::fileCache()->get('pwd');
        echo "<br>";
        Despote::fileCache()->del('pwd');
        echo Despote::fileCache()->get('usr');
        echo "<br>";
        echo Despote::fileCache()->get('pwd');
        echo "<br>";
        $this->render();
    }

    public function home()
    {
        echo "我是 home";
    }
}
