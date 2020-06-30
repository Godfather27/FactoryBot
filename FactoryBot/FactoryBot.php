<?php

namespace FactoryBot;

use FactoryBot\Core\Hook;
use FactoryBot\Core\Factory;
use FactoryBot\Core\Repository;
use FactoryBot\Core\LifecycleHooksObserver;
use FactoryBot\Exceptions\InvalidArgumentException;

/**
 * API Entrypoint
 *
 * FactoryBot is a fixtures replacement with a straightforward definition syntax, support for multiple build strategies
 * (saved instances, unsaved instances),
 * and support for multiple factories for the same class (user, admin_user, and so on), including Factory inheritance.
 * @package FactoryBot
 */
class FactoryBot
{
    /**
     * flag to either show debugging warnings or not
     *
     * @var boolean
     */
    public static $warnings = false;

    /**
     * constructor disabled
     * no instance allowed
     * @codeCoverageIgnore
     *
     * @return void
     */
    private function __construct()
    {
    }

    /**
     * create a Hook which is triggered for all Factories
     * @param string $lifecycleStage stage at which the hook gets fired
     * @param callable $callback     callback which gets executed
     * @return Hook registered hook, can be used to remove it later with FactoryBot::removeGlobalHook($hook)
     */
    public static function registerGlobalHook($lifecycleStage, $callback)
    {
        $hook = new Hook($lifecycleStage, $callback);
        LifecycleHooksObserver::registerHook($hook);
        return $hook;
    }

    /**
     * removes a Hook from the hook registry
     * @param Hook $hook Hook which should be removed
     * @return void
     */
    public static function removeGlobalHook(Hook $hook)
    {
        LifecycleHooksObserver::removeHook($hook);
    }

    /**
     * returns a Hook which can be registered on a Factory
     * @param string $lifecycleStage stage at which the hook gets fired (e.g. before, beforeCreate, after)
     * @param callable $callback     callback which gets executed
     * @return Hook
     */
    public static function hook($lifecycleStage, $callback)
    {
        return new Hook($lifecycleStage, $callback);
    }

    /**
     * Define Factory and register it in the Repository.
     *
     * @param  string $name       name of the Factory
     * @param  array  $properties default properties for the Factory
     * @param  array  $options
     *  [
     *    "class" => specify class,
     *    "aliases" => register Factory with additional names,
     *    "hooks" => array of FactoryBot::hook()
     *  ]
     * @return void
     */
    public static function define(
        $name,
        $properties = [],
        $options = ["class" => null, "aliases" => [], "hooks" => []]
    ) {
        $_class = isset($options["class"]) ? $options["class"] : null;
        FactoryBot::validateClassParams($name, $_class);
        $class = FactoryBot::assumeClass($name, $_class);
        $factoryNames = isset($options["aliases"]) ? $options["aliases"] : [];
        $factoryNames[] = $name;
        $hooks = isset($options["hooks"]) ? $options["hooks"] : [];

        $factory = new Factory($class, $properties, $hooks);
        foreach ($factoryNames as $name) {
            Repository::registerFactory($name, $factory);
        }
    }

    /**
     * Extend an existing Factory with more specific default params.
     *
     * @param  string $name          name of the Factory
     * @param  string $parentFactory name of the Factory, which will be inherited from
     * @param  array  $properties    default properties
     * @param  array  $options
     * [
     *    "aliases" => register Factory with additional names,
     *    "hooks" => array of FactoryBot::hook()
     * ]
     * @return void
     */
    public static function extend(
        $name,
        $parentFactoryName,
        $properties = [],
        $options = ["aliases" => [], "hooks" => []]
    ) {
        $factoryNames = isset($options["aliases"]) ? $options["aliases"] : [];
        $factoryNames[] = $name;
        $hooks = isset($options["hooks"]) ? $options["hooks"] : [];

        $parentFactory = Repository::findFactory($parentFactoryName);
        $extendedFactory = $parentFactory->extend($properties, $hooks);
        foreach ($factoryNames as $name) {
            Repository::registerFactory($name, $extendedFactory);
        }
    }

    /**
     * Build the specified model without saving it to the database.
     *
     * @param  string $name             name of the Factory
     * @param  array  $overrides        model properties which should be overwritten
     * @return object                   returns an instance of the defined model
     * @throws InvalidArgumentException throws when passed params are invalid
     */
    public static function build($name, $overrides = [])
    {
        $factory = Repository::findFactory($name);
        $classInstance = $factory->build($overrides);
        return $classInstance;
    }

    /**
     * Create the specified model and save it to the database.
     *
     * @param  string $name             name of the Factory
     * @param  array  $overrides        model properties which should be overwritten
     * @return object                   returns an instance of the defined model
     * @throws InvalidArgumentException throws when passed params are invalid
     */
    public static function create($name, $overrides = [])
    {
        $factory = Repository::findFactory($name);
        $classInstance = $factory->create($overrides);
        return $classInstance;
    }

    /**
     * Define an 1:1 or n:1 relation.
     *
     * @param  string $name      provide the name of the Factory which should be used
     * @param  array  $overrides model properties which should be overwirtten
     * @return callable          returns relation generator function for related model
     */
    public static function relation($name, $overrides = [])
    {
        // gets called by Factory->hydrateClassInstance($model, $buildStrategy)
        return function ($model, $buildStrategy) use ($name, $overrides) {
            $factory = Repository::findFactory($name);
            $classInstance = $factory->$buildStrategy($overrides);
            return $classInstance;
        };
    }

    /**
     * Define a 1:n or n:m relation.
     *
     * @param  string $name          provide the name of the Factory which should be used
     * @param  int    $defaultAmount defines how many instances should be generated
     * @param  array  $overrides     model properties which should be overwirtten
     * @return array                 returns array of relation generator functions for related model
     */
    public static function relations($name, $defaultAmount = 1, $overrides = [])
    {
        // gets called by Factory->hydrateClassInstance($model, $buildStrategy)
        $relations = [];
        for ($i = 0; $i < $defaultAmount; $i++) {
            $relations[] = self::relation($name, $overrides);
        }
        return $relations;
    }

    /**
     * Create an autoincrement id or provide a callable to generate a sequence.
     *
     * @param  callable|null $callable callable that returns unique values
     * @return callable                value generator function for the Factory
     */
    public static function sequence($callable = null)
    {
        if (is_callable($callable)) {
            // gets called by Factory->hydrateClassInstance($model, $buildStrategy)
            return function ($model, $buildStrategy) use ($callable) {
                $nextSequenceValue = Repository::findFactory(get_class($model))->getNextSequenceValue();
                return $callable($nextSequenceValue, $model, $buildStrategy);
            };
        }
        // gets called by Factory->hydrateClassInstance($model, $buildStrategy)
        return function ($model) {
            return Repository::findFactory(get_class($model))->getNextSequenceValue();
        };
    }

    /**
     * Remove all registered Factories and remove all hooks.
     *
     * @return void
     */
    public static function purge()
    {
        Repository::purge();
        LifecycleHooksObserver::purge();
    }

    private static function validateClassParams($name, $class)
    {
        if (!class_exists($class) && !class_exists($name)) {
            throw new InvalidArgumentException(
                "`$name` is not a class, provide a class argument, or use the class name as the factory name!"
            );
        }
    }

    private static function assumeClass($name, $class)
    {
        return $class === null ? $name : $class;
    }
}
