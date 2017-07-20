<?php

namespace common\utils;

use ZPHP\Core\Factory;

class loadClass
{

    public static function getService($service)
    {
        return Factory::getInstance($service);
    }

    public static function getDao($dao)
    {
        return Factory::getInstance($dao);
    }
}
