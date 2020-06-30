<?php

namespace FactoryBot\Tests;

use FactoryBot\Core\Repository;
use FactoryBot\FactoryBot;
use PHPUnit\Framework\TestCase;
use FactoryBot\Tests\TestModels\UserModel;

/**
 * BDD Test class for FactoryBot
 * @package FactoryBot\Tests
 */
class FactoryTest extends TestCase
{
    protected function setUp()
    {
        FactoryBot::purge();
    }

    public function testFactoryReturnsNotSetProperties()
    {
        $expected = [
            "lastName",
            "email",
            "account",
            "role",
            "cars",
            "subordinate"
        ];
        FactoryBot::define(UserModel::class, [
            "id" => FactoryBot::sequence(),
            "firstName" => "Jane"
        ]);
        $userFactory = Repository::findFactory(UserModel::class);

        $this->assertEquals($expected, $userFactory->getNotSetProperties(), "should return all not set properties");
    }
}
