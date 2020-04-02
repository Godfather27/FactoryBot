<?php

namespace FactoryBot\Core;

use FactoryBot\FactoryBot;
use FactoryBot\Utils\Logger;
use FactoryBot\Utils\ClassAnalyser;
use FactoryBot\Exceptions\InvalidArgumentException;

class Factory
{
    const BUILD_STRATEGY_BUILD = "build";
    const BUILD_STRATEGY_CREATE = "create";

    /**
     * class which gets constructed by this factory
     *
     * @var string
     */
    private $class;

    /**
     * properties which will be hydrated if not overwritten on build
     *
     * @var array
     */
    private $defaultProperties;

    /**
     * the currently created instance of the registred modl
     *
     * @var object
     */
    private $classInstance;

    /**
     * the current sequence number
     *
     * @var int
     */
    private $sequence = 0;

    /**
     * Factory constructor
     *
     * @param  string $class      - name of the model class
     * @param  array  $properties - default properties for the model hydration
     * @return void
     * @throws InvalidArgumentException
     */
    public function __construct($class, $properties = [])
    {
        $this->class = $class;
        $this->validateDefaultProperties($properties);
        $this->defaultProperties = $properties;
        $this->warnNotSetProperties();
    }

    /**
     * creates an instance of the specified model and hydrates it with the specified values
     *
     * @param  array  $overrides     - model properties which should be overwritten
     * @param  string $buildStrategy wether the object should be saved, or not
     * @return object - instance of the specified model
     * @throws InvalidArgumentException
     */
    public function build($overrides = [], $buildStrategy = self::BUILD_STRATEGY_BUILD)
    {
        $this->validateOverrides($overrides);
        $this->buildStrategy = $buildStrategy;
        $this->classInstance = new $this->class();
        $properties = array_merge($this->defaultProperties, $overrides);
        $this->classInstance = $this->hydrateClassInstance($properties);
        return $this->classInstance;
    }

    /**
     * builds and saves an instance of the specified model
     *
     * @param  array $overrides - model properties which should be overwritten
     * @return object - instance of the specified model
     * @throws InvalidArgumentException
     */
    public function create($overrides = [])
    {
        $this->classInstance = $this->build($overrides, self::BUILD_STRATEGY_CREATE);
        $this->classInstance->save();
        return $this->classInstance;
    }

    /**
     * extend existing factory with new default properties
     *
     * @param  array $properties - default properties
     * @return Factory
     */
    public function extend($properties = [])
    {
        $mergedProperties = array_merge($this->defaultProperties, $properties);
        return new Factory($this->class, $mergedProperties);
    }

    /**
     * increments the sequence and returns the new sequence number
     *
     * @return int
     */
    public function getNextSequenceValue()
    {
        return $this->sequence += 1;
    }

    private function hydrateClassInstance($properties)
    {
        foreach ($properties as $propertyName => $value) {
            $setterMethod = ClassAnalyser::getSetterFromPropertyName($propertyName);
            $resolvedValue = $this->resolveValue($value);
            $this->classInstance->$setterMethod($resolvedValue);
        }
        return $this->classInstance;
    }

    private function resolveValue($value)
    {
        $value = is_array($value) ? array_map([$this, "resolveValue"], $value) : $value;
        return is_callable($value) ? $value($this->classInstance, $this->buildStrategy) : $value;
    }

    private function validateDefaultProperties($properties)
    {
        if (!is_array($properties)) {
            throw new InvalidArgumentException("`\$properties` has to be provided as an associative array");
        }
        foreach (array_keys($properties) as $propertyName) {
            $this->validateProperty($propertyName);
        }
    }

    private function validateOverrides($overrides)
    {
        if (!is_array($overrides)) {
            throw new InvalidArgumentException("`\$overrides` has to be provided as an associative array");
        }
        foreach (array_keys($overrides) as $propertyName) {
            $this->validateProperty($propertyName);
        }
    }

    private function validateProperty($propertyName)
    {
        if (!is_string($propertyName)) {
            throw new InvalidArgumentException("propertyName `$propertyName` must be a `string`!");
        }

        if (!ClassAnalyser::hasSetter($this->class, $propertyName)) {
            throw new InvalidArgumentException("$this->class has no setter for `$propertyName`!");
        }
    }

    private function warnNotSetProperties()
    {
        if (!FactoryBot::$warnings) {
            return;
        }
        $notSetProperties = $this->getNotSetProperties();
        if (count($notSetProperties) > 0) {
            $notSetPropertiesString = implode(", ", $notSetProperties);
            Logger::warn(
                "$this->class Factory not defined \$defaultProperties: " . $notSetPropertiesString
            );
        }
    }

    private function getNotSetProperties()
    {
        $setableProperties = ClassAnalyser::getSetableProperties($this->class);
        $notSetProperties = [];
        foreach ($setableProperties as $propertyName) {
            if (!array_key_exists($propertyName, $this->defaultProperties)) {
                $notSetProperties[] = $propertyName;
            }
        }
        return $notSetProperties;
    }
}
