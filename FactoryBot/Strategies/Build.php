<?php

namespace FactoryBot\Strategies;

use FactoryBot\Strategies\StrategyInterface;

class Build implements StrategyInterface
{
    const STRATEGY_NAME = "build";

    /**
     * Notify Hooks about current lifecycle stage
     *
     * @param Factory $factory instance of a factory
     * @return void
     */
    public static function beforeCompile($factory)
    {
        $factory->notify("before");
        $factory->notify("beforeBuild");
    }

    /**
     * Return instance and notify Hooks about current lifecycle stage
     *
     * @param Factory $factory instance of a factory
     * @param object $instance hydrated instance of the provided Model
     * @return object          hydrated instance
     */
    public static function result($factory, $instance)
    {
        $factory->notify("afterBuild");
        $factory->notify("after");
        return $instance;
    }
}
