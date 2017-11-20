<?php

namespace app\Home\controller;

use \despote\base\Controller;

class Index extends Controller
{
    public function index()
    {
        $this->render('', [], 'default.php');
    }

    public function home()
    {
        echo "我是 home";
    }
}
