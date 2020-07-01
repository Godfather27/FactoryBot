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

    /**
     * Create Factory instance
     *
     * @param array $hooks hooks which should be registered for this Factory
     * @return void
     * @throws InvalidArgumentException
     */
    public function __construct($hooks)
    {
        $this->setFactoryHooks($hooks);
    }

    /**
     * call all Hooks registered for a specific factory.
     *
     * @param string $lifecycleStageName name of the lifecycle stage
     * @param mixed|null $instance       hydrated instance of a model
     * @return void
     */
    public function notify($lifecycleStageName, $instance = null)
    {
        foreach ($this->getHooks() as $hook) {
            if ($hook->lifecycleStage === $lifecycleStageName) {
                $hook->run($instance);
            }
        }
    }

    /**
     * Register a global hook
     * globally registered hooks will be executed on each Factory.
     *
     * @param Hook $hook hook which should be registered
     * @return void
     */
    public static function registerHook(Hook $hook)
    {
        self::$globalHooks[] = $hook;
    }

    /**
     * Remove Hook from the global Hook registry
     *
     * @param Hook $hook hook which should get removed
     * @return void
     */
    public static function removeHook(Hook $hook)
    {
        $key = array_search($hook, self::$globalHooks);
        unset(self::$globalHooks[$key]);
    }

    /**
     * remove all registered global hooks
     *
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
                throw new InvalidArgumentException("invalid Hook used in definition of Factory");
            }
        }
        $this->factoryHooks = $factoryHooks;
    }

    /**
     * Get global and factory hooks relevant for this Factory.
     *
     * @return array array with hooks
     */
    private function getHooks()
    {
        return array_merge(self::$globalHooks, $this->factoryHooks);
    }
}
