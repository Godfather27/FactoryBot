<?php

namespace FactoryBot\Core;

use FactoryBot\Exceptions\Exception;

class Repository
{
    /**
     * all registered factories
     * @var mixed
     */
    private static $factories = [];

    /**
     * register a new factory instance
     * @param string $name - acessor name of factory
     * @param mixed $factory
     * @return void
     */
    public static function registerFactory($name, $factory)
    {
        self::$factories[$name] = $factory;
    }

    /**
     * gets the factory by name
     * @param string $name - name of the factory
     * @return Factory
     */
    public static function findFactory($name)
    {
        if (!isset(self::$factories[$name]))
        {
            throw new Exception("Factory `$name` not defined!");
        }
        return self::$factories[$name];
    }

    /**
     * deletes all defined Factories
     * @return void
     */
    public static function purge()
    {
        self::$factories = [];
    }
}