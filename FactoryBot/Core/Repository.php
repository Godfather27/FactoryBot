<?php

namespace FactoryBot\Core;

use FilesystemIterator;
use FactoryBot\Core\Factory;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use FactoryBot\Strategies\Build;
use FactoryBot\Strategies\Create;
use FactoryBot\Exceptions\Exception;
use FactoryBot\Strategies\StrategyInterface;

/**
 * repository for defined Factories
 * @package FactoryBot\Core
 */
class Repository
{
    /**
     * Default path for factory definitions
     */
    const DEFAULT_DEFINITIONS_BASE_DIRECTORY = "tests/factories/";

    /**
     * base directory where Factories are defined.
     * can be overwritten to load definitions with a different folder structure.
     *
     * @var string
     */
    public static $definitionsBaseDirectory = self::DEFAULT_DEFINITIONS_BASE_DIRECTORY;

    /**
     * all registered factories
     *
     * @var array
     */
    private static $factories = [];

    /**
     * all registered strategies
     *
     * @var array
     */
    private static $strategies = [
        Build::STRATEGY_NAME => Build::class,
        Create::STRATEGY_NAME => Create::class
    ];

    /**
     * Load Factory definitions
     *
     * @return void
     * @throws Exception throws if definition path does not exist
     */
    public static function findDefinitions()
    {
        if (!is_dir(self::$definitionsBaseDirectory)) {
            throw new Exception(sprintf("`%s` is no directory", self::$definitionsBaseDirectory));
        }

        foreach (self::createFileIterator() as $file) {
            self::loadDefinition($file);
        }
    }

    private static function loadDefinition($file)
    {
        $filename = $file->getFilename();
        if (preg_match("/\.php$/", $filename)) {
            require $file->getPathname();
        }
    }

    private static function createFileIterator()
    {
        $dir = new RecursiveDirectoryIterator(self::$definitionsBaseDirectory, FilesystemIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($dir);
        return $iterator;
    }

    private static function resetDefinitionsBaseDirectory()
    {
        self::$definitionsBaseDirectory = self::DEFAULT_DEFINITIONS_BASE_DIRECTORY;
    }

    /**
     * delete all factories and custom strategies
     *
     * @return void
     */
    public static function purge()
    {
        self::purgeFactories();
        self::purgeStrategies();
        self::resetDefinitionsBaseDirectory();
    }

    /**
     * Register a new Factory instance.
     *
     * @param  string  $name    acessor name of Factory
     * @param  Factory $factory Factory instance to be saved
     * @return void
     */
    public static function registerFactory($name, $factory)
    {
        self::$factories[$name] = $factory;
    }

    /**
     * Get a registered Factory by name.
     *
     * @param  string $name name of the Factory
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
    private static function purgeFactories()
    {
        self::$factories = [];
    }

    /**
     * Register a Strategy
     *
     * @param string $name     name of the strategy
     * @param string $strategy class of the strategy
     * @return void
     * @throws Exception       if Strategy does not implement StrategyInterface
     */
    public static function registerStrategy($name, $strategy)
    {
        if (!self::implementsStrategyInterface($strategy)) {
            throw new Exception(sprintf("Strategy `%s` must implement `%s`", $strategy, StrategyInterface::class));
        }
        self::$strategies[$name] = $strategy;
    }

    /**
     * Get a registered Strategy by name.
     *
     * @param string $name name of the Strategy
     * @return string      class of the Strategy
     * @throws Exception   if strategy is not defined
     */
    public static function findStrategy($name)
    {
        if (!isset(self::$strategies[$name])) {
            throw new Exception("Strategy `$name` not defined!");
        }
        return self::$strategies[$name];
    }

    /**
     * delete all custom strategies
     *
     * @return void
     */
    private static function purgeStrategies()
    {
        self::$strategies = [
            Build::STRATEGY_NAME => Build::class,
            Create::STRATEGY_NAME => Create::class
        ];
    }

    private static function implementsStrategyInterface($strategy)
    {
        $interfaces = class_implements($strategy);
        return isset($interfaces[StrategyInterface::class]);
    }
}
