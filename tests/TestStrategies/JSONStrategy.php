<?php

namespace FactoryBot\Tests\TestStrategies;

use FactoryBot\Strategies\StrategyInterface;

class JSONStrategy implements StrategyInterface
{
    public static function beforeCompile($factory)
    {
        $factory->notify("before");
    }

    public static function result($factory, $instance)
    {
        $factory->notify("after");

        $result = self::getPropertiesArray($instance);

        return json_encode($result);
    }

    public static function getPropertiesArray($instance)
    {
        $instanceArray = (array) $instance;
        $result = [];
        foreach ($instanceArray as $keyWithVisibility => $value) {
            $keySegments = explode("\0", $keyWithVisibility);
            $keyWithoutVisibility = end($keySegments);
            $result[$keyWithoutVisibility] = $value;
        }
        return $result;
    }
}
