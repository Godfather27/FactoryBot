<?php

namespace FactoryBot\Tests\TestModels;

/**
 * this class fakes Propel ORM integration
 * @package FactoryBot\Tests\TestModels
 */
class FakePropel
{
    private $new = true;

    public function save()
    {
        $this->new = false;
    }

    public function isNew()
    {
        return $this->new;
    }
}
