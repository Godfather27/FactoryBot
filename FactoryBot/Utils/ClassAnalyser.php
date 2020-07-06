<?php

namespace FactoryBot\Utils;

use ReflectionClass;
use ReflectionMethod;

class ClassAnalyser
{
    /**
     * Get an array of methods from the provided class
     *
     * @param string $class       class which should be analyzed
     * @return ReflectionMethod[] array of methods
     */
    public static function getMethods($class)
    {
        $reflectionClass = new ReflectionClass($class);
        return $reflectionClass->getMethods();
    }

    /**
     * Get an array of all properties which have a setter method
     *
     * @param string $class class which should be analyzed
     * @return array        array of settable properties
     */
    public static function getSetableProperties($class)
    {
        $methods = self::getMethods($class);
        $setableProperties = [];
        foreach ($methods as $method) {
            if (preg_match("/^set(\w*)/", $method->name, $matches)) {
                $setableProperties[] = lcfirst($matches[1]);
            }
        }
        return $setableProperties;
    }

    /**
     * Check if class has setter method for provided property
     *
     * @param string $class        class which should be analyzed
     * @param string $propertyName name of the property which should be checked
     * @return bool                returns wether the property has a setter or not
     */
    public static function hasSetter($class, $propertyName)
    {
        return method_exists($class, self::getSetterFromPropertyName($propertyName));
    }

    /**
     * Generates the setter method name from its property name
     *
     * @param string $propertyName name of the property
     * @return string              name of the setter method
     */
    public static function getSetterFromPropertyName($propertyName)
    {
        return "set" . ucfirst($propertyName);
    }
}
