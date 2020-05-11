<?php

namespace FactoryBot\Tests\TestModels;

/**
 * used to test 1:1 relations
 * @package FactoryBot\Tests\TestModels
 */
class AccountModel extends FakePropel
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
