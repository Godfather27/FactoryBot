<?php

namespace FactoryBot\Strategies;

interface StrategyInterface
{
    /**
     * Take actions before compiling a model.
     * Is called before an instance is hydrated.
     *
     * @param Factory $factory instance of a factory
     * @return void
     */
    public static function beforeCompile($factory);

    /**
     * Generate final result.
     * Is called after model was hydrated.
     *
     * @param Factory $factory instance of a factory
     * @param object $instance hydrated instance created by the factory
     * @return mixed           return value depends on strategy goal
     */
    public static function result($factory, $instance);
}
