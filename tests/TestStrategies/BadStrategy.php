<?php

namespace FactoryBot\Tests\TestStrategies;

class BadStrategy
{
    public static function beforeCompile($factory)
    {
    }

    public static function result($factory, $instance)
    {
        return $instance;
    }
}
