<?php

namespace FactoryBot\Tests\TestModels;

/**
 * used to test 1:n relations
 * @package FactoryBot\Tests\TestModels
 */
class CarModel extends FakePropel
{
    private $id;
    private $name;

    public function getId()
    {
        return $this->id;
    }
    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName()
    {
        return $this->name;
    }
    public function setName($name)
    {
        $this->name = $name;
    }
}
