<?php

namespace FactoryBot\Core;

use FactoryBot\Exceptions\InvalidArgumentException;

class Hook
{
    /**
     * name of the lifecycle stage.
     * @var string
     */
    public $lifecycleStage;

    /**
     * callback method
     * @var callable
     */
    private $callback;

    private static $lifecycleStages = [
        "before",
        "beforeCreate",
        "beforeBuild",
        "after",
        "afterCreate",
        "afterBuild"
    ];

    /**
     * Hook will be executed on different Factory
     * lifecycle stages
     * @param string $lifecycleStage - name of the lifecycle stage
     * @param callable $callback - callback which should be called
     * @return void
     */
    public function __construct($lifecycleStage, $callback)
    {
        $this->setLifecycleStage($lifecycleStage);
        $this->setCallback($callback);
    }

    /**
     * execute hook
     * @param mixed|null $instance hydrated model instance
     * @return void
     */
    public function run($instance)
    {
        call_user_func($this->callback, $instance);
    }

    private function setLifecycleStage($lifecycleStage)
    {
        if ($this->isLifecycleStage($lifecycleStage)) {
            throw new InvalidArgumentException("Invalid Hook: lifecycle stage `$lifecycleStage` does not exist.");
        }
        $this->lifecycleStage = $lifecycleStage;
    }

    private function setCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException("Invalid Hook: callback not executable.");
        }
        $this->callback = $callback;
    }

    private function isLifecycleStage($name)
    {
        return !in_array($name, self::$lifecycleStages);
    }
}
