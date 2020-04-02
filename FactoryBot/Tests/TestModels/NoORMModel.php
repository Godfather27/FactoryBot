<?php

namespace FactoryBot\Tests\TestModels;

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
