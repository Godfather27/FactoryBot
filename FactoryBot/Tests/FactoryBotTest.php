<?php

namespace FactoryBot\Tests;

use FactoryBot\FactoryBot;
use PHPUnit\Framework\TestCase;
use FactoryBot\Tests\TestModels\CarModel;
use FactoryBot\Tests\TestModels\UserModel;
use FactoryBot\Tests\TestModels\AccountModel;

class FactoryBotTest extends TestCase
{
    protected function setUp()
    {
        FactoryBot::purge();
    }

    public function testBuildsSpecifiedClass()
    {
        $expected = UserModel::class;

        FactoryBot::define("User", [], ["class" => $expected]);

        self::assertInstanceOf($expected, FactoryBot::build("User"), "should build defined class");
    }

    public function testBuildsAssumedClass()
    {
        $expected = UserModel::class;

        FactoryBot::define($expected);

        self::assertInstanceOf($expected, FactoryBot::build($expected), "should build assumed class");
    }

    public function testCreatesAssumedClass()
    {
        $expectedClass = UserModel::class;
        FactoryBot::define($expectedClass);

        $user = FactoryBot::create($expectedClass);

        self::assertInstanceOf($expectedClass, $user, "should build assumed class");
        self::assertFalse($user->isNew(), "should save model");
    }

    public function testFailsOnNotExistingClass()
    {
        $expected = "NotexistingClass";

        $this->setExpectedException("InvalidArgumentException");

        FactoryBot::define($expected);
    }

    public function testAllowsAliases()
    {
        $expectedClass = UserModel::class;
        $alias1 = "User";
        $alias2 = "Admin";

        FactoryBot::define($expectedClass, [], ["aliases" => [$alias1, $alias2]]);

        self::assertInstanceOf($expectedClass, FactoryBot::build($expectedClass), "should build class from name");
        self::assertInstanceOf($expectedClass, FactoryBot::build($alias1), "should build from alias");
        self::assertInstanceOf($expectedClass, FactoryBot::build($alias2), "should build from alias");
    }

    public function testSetsOverrides()
    {
        $expectedFirstName = "test first name";
        $expectedLastName = "test last name";
        FactoryBot::define(UserModel::class);

        $user = FactoryBot::build(
            UserModel::class,
            [
                "firstName" => $expectedFirstName,
                "lastName" => $expectedLastName
            ]
        );

        self::assertEquals($expectedFirstName, $user->getFirstName(), "should build with overwritten first name");
        self::assertEquals($expectedLastName, $user->getLastName(), "should build with overwritten last name");
    }

    public function testSetsOverridesCanBeClosure()
    {
        $expectedFirstName = "test first name";
        $expectedLastName = "test last name";
        FactoryBot::define(UserModel::class);

        $user = FactoryBot::build(
            UserModel::class,
            [
                "firstName" => $expectedFirstName,
                "lastName" => function () use ($expectedLastName) {
                    return $expectedLastName;
                }
            ]
        );

        self::assertEquals($expectedFirstName, $user->getFirstName(), "should build with overwritten first name");
        self::assertEquals($expectedLastName, $user->getLastName(), "should build with overwritten last name");
    }

    public function testRejectsMalformatedOverrides()
    {
        FactoryBot::define(UserModel::class);

        $this->setExpectedException(
            "InvalidArgumentException",
            "`\$overrides` has to be provided as an associative array"
        );

        FactoryBot::build(UserModel::class, "firstname");
    }

    public function testRejectsMalformatedOverrides2()
    {
        FactoryBot::define(UserModel::class);

        $this->setExpectedException("InvalidArgumentException", "UserModel has no setter for `nickname`!");

        FactoryBot::build(UserModel::class, ["nickname" => "nick"]);
    }

    public function testSetsDefaultValues()
    {
        $expectedFirstName = "test first name";
        $expectedLastName = "test last name";
        FactoryBot::define(
            UserModel::class,
            [
                "firstName" => $expectedFirstName,
                "lastName" => $expectedLastName
            ]
        );

        $user = FactoryBot::build(UserModel::class);

        self::assertEquals($expectedFirstName, $user->getFirstName(), "should build with overwritten first name");
        self::assertEquals($expectedLastName, $user->getLastName(), "should build with overwritten last name");
    }

    public function testSetsDefaultSequenceValues()
    {
        $expectedId1 = 1;
        $expectedId2 = 2;
        FactoryBot::define(
            UserModel::class,
            [
                "id" => FactoryBot::sequence()
            ]
        );

        $user1 = FactoryBot::build(UserModel::class);
        $user2 = FactoryBot::build(UserModel::class);

        self::assertEquals($expectedId1, $user1->getId(), "should build with auto sequence id");
        self::assertEquals($expectedId2, $user2->getId(), "should build with auto sequence id");
    }

    public function testSetsCustomSequenceValues()
    {
        $expectedId1 = "User1";
        $expectedId2 = "User2";
        FactoryBot::define(
            UserModel::class,
            [
                "firstName" => FactoryBot::sequence(
                    function ($num) {
                        return "User" . $num;
                    }
                )
            ]
        );

        $user1 = FactoryBot::build(UserModel::class);
        $user2 = FactoryBot::build(UserModel::class);

        self::assertEquals($expectedId1, $user1->getfirstName(), "should build with overwritten first name");
        self::assertEquals($expectedId2, $user2->getfirstName(), "should build with overwritten first name");
    }

    public function testSetsDefaultSequenceValuesWithAliases()
    {
        $expectedId1 = 1;
        $expectedId2 = 2;
        FactoryBot::define(
            UserModel::class,
            [
                "id" => FactoryBot::sequence()
            ],
            [
                "aliases" => ["Admin"]
            ]
        );

        $user = FactoryBot::build(UserModel::class);
        $admin = FactoryBot::build("Admin");

        self::assertEquals($expectedId1, $user->getId(), "should build with auto sequence id");
        self::assertEquals($expectedId2, $admin->getId(), "should build with auto sequence id");
    }

    public function testSetsDefaultClosureValues()
    {
        $expectedFirstName = "test first name";
        $expectedLastName = "test last name";
        FactoryBot::define(
            UserModel::class,
            [
                "firstName" => $expectedFirstName,
                "lastName" => function () use ($expectedLastName) {
                    return $expectedLastName;
                }
            ]
        );

        $user = FactoryBot::build(UserModel::class);

        self::assertEquals($expectedFirstName, $user->getFirstName(), "should build with overwritten first name");
        self::assertEquals($expectedLastName, $user->getLastName(), "should build with overwritten last name");
    }

    public function testSetsDependendValues()
    {
        $expectedFirstName = "Jane";
        $expectedLastName = "Doe";
        $expectedEmail = "Jane.Doe@has-to-be.com";
        FactoryBot::define(
            UserModel::class,
            [
                "firstName" => $expectedFirstName,
                "lastName" => $expectedLastName,
                "email" => function ($model) {
                    return $model->getfirstName().".".$model->getLastName()."@has-to-be.com";
                }
            ]
        );

        $user = FactoryBot::build(UserModel::class);

        self::assertEquals($expectedFirstName, $user->getFirstName(), "should build with overwritten first name");
        self::assertEquals($expectedLastName, $user->getLastName(), "should build with overwritten last name");
        self::assertEquals($expectedEmail, $user->getEmail(), "should build with overwritten email");
    }

    public function testSetsDefaultValuesAndOverrides()
    {
        $expectedFirstName = "test first name";
        $expectedLastName = "test last name";
        FactoryBot::define(
            UserModel::class,
            [
                "firstName" => $expectedFirstName,
                "lastName" => "last name"
            ]
        );

        $user = FactoryBot::build(
            UserModel::class,
            [
                "lastName" => $expectedLastName
            ]
        );

        self::assertEquals($expectedFirstName, $user->getFirstName(), "should build with overwritten first name");
        self::assertEquals($expectedLastName, $user->getLastName(), "should build with overwritten last name");
    }

    public function testRejectsMalformatedDefaultValues()
    {

        $this->setExpectedException(
            "InvalidArgumentException",
            "`\$properties` has to be provided as an associative array"
        );

        FactoryBot::define(UserModel::class, "firstname");
    }

    public function testRejectsMalformatedDefaultValues2()
    {
        $this->setExpectedException("InvalidArgumentException", "UserModel has no setter for `nickname`!");

        FactoryBot::define(UserModel::class, ["nickname" => "nick"]);
    }

    public function testCreatesRelation()
    {
        $expectedName = "account name";
        FactoryBot::define(
            AccountModel::class,
            [
                "id" => 22,
                "name" => $expectedName
            ]
        );
        FactoryBot::define(
            UserModel::class,
            [
                "id" => 1,
                "firstName" => "first name",
                "lastName" => "last name",
                "account" => FactoryBot::relation(AccountModel::class)
            ]
        );

        $user = FactoryBot::build(UserModel::class);

        self::assertEquals($expectedName, $user->getAccount()->getName(), "should build nested model");
    }

    public function testCyclicRelation()
    {
        $expectedName = "user name";
        FactoryBot::define(
            UserModel::class,
            [
                "id" => 1,
                "firstName" => "first name",
                "lastName" => "last name",
                "subordinate" => FactoryBot::relation(
                    UserModel::class,
                    [
                        "subordinate" => null,
                        "firstName" => $expectedName
                    ]
                )
            ]
        );

        $user = FactoryBot::build(UserModel::class);

        self::assertEquals($expectedName, $user->getSubordinate()->getFirstName(), "should build nested model");
    }

    public function testCreatesRelations()
    {
        $expectedName = "car name";
        $expectedCount = 2;
        FactoryBot::define(
            CarModel::class,
            [
                "id" => 22,
                "name" => $expectedName
            ]
        );
        FactoryBot::define(
            UserModel::class,
            [
                "id" => 1,
                "firstName" => "first name",
                "lastName" => "last name",
                "cars" => FactoryBot::relations(CarModel::class, 2)
            ]
        );

        $user = FactoryBot::build(UserModel::class);

        self::assertEquals($expectedName, $user->getCars()[0]->getName(), "should build nested model");
        self::assertEquals($expectedCount, count($user->getCars()), "should build 2 car models");
    }

    public function testOverridesAcceptsModel()
    {
        $expectedName = "new account name";
        FactoryBot::define(
            AccountModel::class,
            [
                "id" => 22,
                "name" => "account name"
            ]
        );
        FactoryBot::define(
            UserModel::class,
            [
                "id" => 1,
                "firstName" => "first name",
                "lastName" => "last name",
                "account" => FactoryBot::relation(AccountModel::class)
            ]
        );

        $account = FactoryBot::build(AccountModel::class, ["name" => $expectedName]);
        $user = FactoryBot::build(UserModel::class, ["account" => $account]);

        self::assertEquals($expectedName, $user->getAccount()->getName(), "should build nested model");
    }

    public function testInheritsBuildStrategyBuildOnRelation()
    {
        FactoryBot::define(
            AccountModel::class,
            [
                "id" => 22,
                "name" => "account name"
            ]
        );
        FactoryBot::define(
            UserModel::class,
            [
                "id" => 1,
                "firstName" => "first name",
                "lastName" => "last name",
                "account" => FactoryBot::relation(AccountModel::class)
            ]
        );

        $user = FactoryBot::build(UserModel::class);

        self::assertTrue($user->getAccount()->isNew(), "should build nested model");
    }

    public function testInheritsBuildStrategyCreateOnRelation()
    {
        FactoryBot::define(
            AccountModel::class,
            [
                "id" => 22,
                "name" => "account name"
            ]
        );
        FactoryBot::define(
            UserModel::class,
            [
                "id" => 1,
                "firstName" => "first name",
                "lastName" => "last name",
                "account" => FactoryBot::relation(AccountModel::class)
            ]
        );

        $user = FactoryBot::create(UserModel::class);

        self::assertFalse($user->getAccount()->isNew(), "should build nested model");
    }

    public function testInheritsBuildStrategyBuildOnRelations()
    {
        $expectedName = "car name";
        $expectedCount = 2;
        FactoryBot::define(
            CarModel::class,
            [
                "id" => 22,
                "name" => $expectedName
            ]
        );
        FactoryBot::define(
            UserModel::class,
            [
                "id" => 1,
                "firstName" => "first name",
                "lastName" => "last name",
                "cars" => FactoryBot::relations(CarModel::class, 2)
            ]
        );

        $user = FactoryBot::build(UserModel::class);

        foreach ($user->getCars() as $car) {
            self::assertTrue($car->isNew(), "should build nested model");
        }
        self::assertEquals($expectedCount, count($user->getCars()));
    }

    public function testInheritsBuildStrategyCreateOnRelations()
    {
        $expectedName = "car name";
        $expectedCount = 2;
        FactoryBot::define(
            CarModel::class,
            [
                "id" => 22,
                "name" => $expectedName
            ]
        );
        FactoryBot::define(
            UserModel::class,
            [
                "id" => 1,
                "firstName" => "first name",
                "lastName" => "last name",
                "cars" => FactoryBot::relations(CarModel::class, 2)
            ]
        );

        $user = FactoryBot::create(UserModel::class);

        foreach ($user->getCars() as $car) {
            self::assertFalse($car->isNew(), "should build nested model");
        }
        self::assertEquals($expectedCount, count($user->getCars()), "should have 2 relations");
    }

    public function testFactoryAllowsInheritance()
    {
        $expectedRole = "admin";
        $expectedFirstName = "test user";
        FactoryBot::define(
            UserModel::class,
            [
                "firstName" => $expectedFirstName,
                "role" => "user",
            ]
        );
        FactoryBot::extend("Admin", UserModel::class, ["role" => $expectedRole]);

        $admin = FactoryBot::build("Admin");

        self::assertEquals($expectedFirstName, $admin->getFirstName(), "should build firstName of parent");
        self::assertEquals($expectedRole, $admin->getRole(), "should build role");
    }
}
