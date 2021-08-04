<?php

namespace App\Common;

use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;

class Log
{
    public static function get(string $name = 'pinkacg')
    {
        return ApplicationContext::getContainer()->get(LoggerFactory::class)->get($name);
    }
}
