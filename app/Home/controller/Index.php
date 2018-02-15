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

use \despote\base\Controller;

class Index extends Controller
{
    public function index()
    {
        $this->render('index.php');
    }
}
