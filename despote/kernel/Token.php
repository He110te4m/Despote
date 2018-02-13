<?php
/*
 *    ____                        _
 *   |  _ \  ___  ___ _ __   ___ | |_ ___
 *   | | | |/ _ \/ __| '_ \ / _ \| __/ _ \
 *   | |_| |  __/\__ \ |_) | (_) | ||  __/
 *   |____/ \___||___/ .__/ \___/ \__\___|
 *                   |_|
 * Token 工具类
 * @author      He110 (i@he110.top)
 * @namespace   despote\kernel
 */

namespace despote\kernel;

use \despote\base\Service;

class Token extends Service
{
    public function create($opera = 'only')
    {
        if ($opera == 'only') {
            return $this->createOnlyToken();
        } else {
            return $this->createApiToken($opera);
        }
    }

    private function createApiToken($opera)
    {
    }

    private function verifyApiToken($token)
    {
    }

    private function createOnlyToken()
    {
    }

    private function verifyOnlyToken($token)
    {
    }
}
