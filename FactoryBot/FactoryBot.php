<?php

namespace FactoryBot;

use Closure;
use FactoryBot\Core\Factory;
use FactoryBot\Core\Repository;
use FactoryBot\Exceptions\InvalidArgumentException;

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
     *
     * @return void
     */
    private function __construct()
    {
    }

    /**
     * setup method for factories.
     * Factories are registered in the global repository.
     *
     * @param  string $name
     * @param  array  $properties
     * @param  array  $options    ["class" => specify class, "aliases" => register factory with additional names]
     * @return void
     */
    public static function define(
        $name,
        $properties = [],
        $options = ["class" => null, "aliases" => []]
    ) {
        $_class = isset($options["class"]) ? $options["class"] : null;
        FactoryBot::validateClassParams($name, $_class);
        $class = FactoryBot::assumeClass($name, $_class);
        $factoryNames = isset($options["aliases"]) ? $options["aliases"] : [];
        $factoryNames[] = $name;

        $factory = new Factory($class, $properties);
        foreach ($factoryNames as $name) {
            Repository::registerFactory($name, $factory);
        }
    }

    /**
     * extends an existing factory with more specific default params
     *
     * @param  string $name          - name of the factory
     * @param  string $parentFactory - name of the factory, which will be inherited from
     * @param  array  $properties    - default properties
     * @param  array  $options       - ["aliases" => register factory with additional names]
     * @return void
     */
    public static function extend(
        $name,
        $parentFactoryName,
        $properties = [],
        $options = ["aliases" => []]
    ) {
        $factoryNames = isset($options["aliases"]) ? $options["aliases"] : [];
        $factoryNames[] = $name;

        $parentFactory = Repository::findFactory($parentFactoryName);
        $extendedFactory = $parentFactory->extend($properties);
        foreach ($factoryNames as $name) {
            Repository::registerFactory($name, $extendedFactory);
        }
    }

    /**
     * build the specified model without saving it to the database.
     *
     * @param  string $name      - name of the factory
     * @param  array  $overrides - model properties which should be overwritten
     * @return object - returns an instance of the defined model
     * @throws InvalidArgumentException - throws when passed params are invalid
     */
    public static function build($name, $overrides = [])
    {
        $factory = Repository::findFactory($name);
        $classInstance = $factory->build($overrides);
        return $classInstance;
    }

    /**
     * creates the specified model and saves it to the database.
     *
     * @param  string $name      - name of the factory
     * @param  array  $overrides - model properties which should be overwritten
     * @return object - returns an instance of the defined model
     * @throws InvalidArgumentException - throws when passed params are invalid
     */
    public static function create($name, $overrides = [])
    {
        $factory = Repository::findFactory($name);
        $classInstance = $factory->create($overrides);
        return $classInstance;
    }

    /**
     * define a 1:1 or n:1 relation in the Factory definition
     *
     * @param  string $name      - provide the name of the Factory which should be used
     * @param  array  $overrides - model properties which should be overwirtten
     * @return Closure - closure function for related model
     */
    public static function relation($name, $overrides = [])
    {
        // gets called by Factory->build/create($model, $buildStrategy)
        return function ($model, $buildStrategy) use ($name, $overrides) {
            $factory = Repository::findFactory($name);
            $classInstance = $factory->$buildStrategy($overrides);
            return $classInstance;
        };
    }

    /**
     * define a 1:n or n:m relation in the Factory definition
     *
     * @param  string $name          - provide the name of the Factory which should be used
     * @param  int    $defaultAmount - defines how many instances should be generated
     * @param  array  $overrides     - model properties which should be overwirtten
     * @return Closure - closure function for related model
     */
    public static function relations($name, $defaultAmount = 1, $overrides = [])
    {
        // gets called by Factory->build/create($model, $buildStrategy)
        $relations = [];
        for ($i = 0; $i < $defaultAmount; $i++) {
            $relations[] = self::relation($name, $overrides);
        }
        return $relations;
    }

    /**
     * create a autoincrement id or provide a Closure to generate a sequence
     *
     * @param  Closure|null $closure - function that generates unique values
     * @return Closure - value generator function for the Factory
     */
    public static function sequence($closure = null)
    {
        if (is_callable($closure)) {
            return function ($model, $buildStrategy) use ($closure) {
                $nextSequenceValue = Repository::findFactory(get_class($model))->getNextSequenceValue();
                return $closure($nextSequenceValue, $model, $buildStrategy);
            };
        }
        return function ($model) {
            return Repository::findFactory(get_class($model))->getNextSequenceValue();
        };
    }

    /**
     * unregister all Factories
     *
     * @return void
     */
    public static function purge()
    {
        Repository::purge();
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
