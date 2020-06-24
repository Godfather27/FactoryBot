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
        $this->setName($lifecycleStage);
        $this->setCallback($callback);
    }

    public function run($instance)
    {
        call_user_func($this->callback, $instance);
    }

    private function setName($name)
    {
        if ($this->isLifecycleStage($name)) {
            throw new InvalidArgumentException("Invalid Hook: lifecycle stage `$name` does not exist.");
        }
        $this->name = $name;
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
