<?php

namespace FactoryBot\Strategies;

use FactoryBot\Core\Factory;
use FactoryBot\Strategies\StrategyInterface;

class Create implements StrategyInterface
{
    const STRATEGY_NAME = "create";

    /**
     * Notify Hooks about current lifecycle stage.
     *
     * @param Factory $factory factory for provided Model
     * @return void
     */
    public static function beforeCompile($factory)
    {
        $factory->notify("before");
        $factory->notify("beforeCreate");
    }

    /**
     * Return saved instance and notify Hooks about current lifecycle stage.
     *
     * @param Factory $factory factory for provided Model
     * @param object $instance hydrated instance of the provided Model
     * @return object          saved instance
     */
    public static function result($factory, $instance)
    {
        $instance->save();
        $factory->notify("afterCreate");
        $factory->notify("after");
        return $instance;
    }
}
