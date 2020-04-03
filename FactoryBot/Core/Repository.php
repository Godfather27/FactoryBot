<?php

namespace FactoryBot\Core;

use FactoryBot\Exceptions\Exception;
use FactoryBot\Core\Factory;

/**
 * repository for defined Factories
 * @package FactoryBot\Core
 */
class Repository
{
    /**
     * all registered factories
     *
     * @var mixed
     */
    private static $factories = [];

    /**
     * Register a new Factory instance.
     *
     * @param  string  $name    - acessor name of Factory
     * @param  Factory $factory - Factory instance to be saved
     * @return void
     */
    public static function registerFactory($name, $factory)
    {
        self::$factories[$name] = $factory;
    }

    /**
     * Get a registered Factory by name.
     *
     * @param  string $name - name of the Factory
     * @return Factory
     */
    public static function findFactory($name)
    {
        if (!isset(self::$factories[$name])) {
            throw new Exception("Factory `$name` not defined!");
        }
        return self::$factories[$name];
    }

    /**
     * Delete all defined Factories.
     *
     * @return void
     */
    public static function purge()
    {
        self::$factories = [];
    }
}
