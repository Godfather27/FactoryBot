<?php

namespace FactoryBot\Core;

use FactoryBot\Core\Hook;
use FactoryBot\Exceptions\InvalidArgumentException;

class LifecycleHooksObserver
{
    /**
     * globally registered hooks
     * called on each Factory
     * @var array
     */
    private static $globalHooks = [];

    /**
     * hooks which are called on specific Factories
     * @var array
     */
    private $factoryHooks = [];

    public function __construct($hooks)
    {
        $this->setFactoryHooks($hooks);
    }

    /**
     * call all hooks registered for a specific factory.
     * @param string $lifecycleStageName name of the lifecycle stage
     * @param mixed|null $instance       hydrated instance of a model
     * @return void
     */
    public function notify($lifecycleStageName, $instance = null)
    {
        foreach ($this->getHooks() as $hook) {
            if ($hook->name === $lifecycleStageName) {
                $hook->run($instance);
            }
        }
    }

    /**
     * register a global hook
     * globally registered hooks will be executed on each factory.
     * @param Hook $hook hook which should be registered
     * @return void
     */
    public static function registerHook(Hook $hook)
    {
        self::$globalHooks[] = $hook;
    }

    /**
     * remove all registered global hooks
     * @return void
     */
    public static function purge()
    {
        self::$globalHooks = [];
    }

    private function setFactoryHooks($factoryHooks)
    {
        foreach ($factoryHooks as $hook) {
            if (!$hook instanceof Hook) {
                throw new InvalidArgumentException(sprintf("invalid Hook used in definition of Factory"));
            }
        }
        $this->factoryHooks = $factoryHooks;
    }

    /**
     * gets all hooks relevant for a specific Factory.
     * global and factory hooks
     * @return array array with hooks
     */
    private function getHooks()
    {
        return array_merge(self::$globalHooks, $this->factoryHooks);
    }
}
