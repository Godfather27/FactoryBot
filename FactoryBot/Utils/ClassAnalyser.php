<?php

namespace FactoryBot\Utils;

use ReflectionClass;

class ClassAnalyser
{
    public static function getMethods($class) {
        $reflectionClass = new ReflectionClass($class);
        return $reflectionClass->getMethods();
    }

    public static function getSetableProperties($class) {
        $methods = self::getMethods($class);
        $setableProperties = [];
        foreach ($methods as $method) {
            if (preg_match("/^set(\w*)/", $method->name, $matches)) {
                $setableProperties[] = lcfirst($matches[1]);
            }
        }
        return $setableProperties;
    }

    public static function hasSetter($class, $propertyName) {
        return method_exists($class, self::getSetterFromPropertyName($propertyName));
    }

    public static function getSetterFromPropertyName($propertyName)
    {
        return "set" . ucfirst($propertyName);
    }

    public static function getGetterFromPropertyName($propertyName)
    {
        return "get" . ucfirst($propertyName);
    }
}