<?php

namespace FactoryBot\Utils;

use FactoryBot\FactoryBot;

class Logger
{
    public static function log($message)
    {
        error_log($message);
    }

    public static function warn($message)
    {
        if (!FactoryBot::$warnings) {
            return;
        }
        error_log($message);
    }
}
