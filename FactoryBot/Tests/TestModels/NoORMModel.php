<?php

namespace FactoryBot\Tests\TestModels;

/**
 * used to test models which don't implement ORM logic
 * @package FactoryBot\Tests\TestModels
 */
class NoORMModel
{
    private $id;

    public function setId($Id)
    {
        $this->id = $Id;
    }
    public function getId()
    {
        return $this->id;
    }
}
