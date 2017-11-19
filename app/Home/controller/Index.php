<?php

namespace app\Home\controller;

// class Index extends Controller
class Index
{
    public function index($test)
    {
        echo "测试 action 加载<br>";
        echo "test:" . $test;
    }
}
