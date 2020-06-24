<?php

namespace FactoryBot\Tests;

use FactoryBot\FactoryBot;
use PHPUnit\Framework\TestCase;
use FactoryBot\Tests\TestModels\CarModel;
use FactoryBot\Tests\TestModels\UserModel;
use FactoryBot\Tests\TestModels\AccountModel;

/**
 * BDD Test class for FactoryBot
 * @package FactoryBot\Tests
 */
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
        $this->setExpectedException("InvalidArgumentException");

        FactoryBot::define("NotexistingClass");
    }

    public function testAllowsAliases()
    {
        $expectedClass = UserModel::class;
        $alias1 = "User";
        $alias2 = "Admin";

        FactoryBot::define($expectedClass, [], ["aliases" => [$alias1, $alias2]]);

        self::assertInstanceOf($expectedClass, FactoryBot::build($alias1), "should build correct class from alias");
        self::assertInstanceOf($expectedClass, FactoryBot::build($alias2), "should build correct class from alias");
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

    public function testSetsCallableOverrides()
    {
        $expectedLastName = "test last name";
        FactoryBot::define(UserModel::class);

        $user = FactoryBot::build(
            UserModel::class,
            [
                "lastName" => function () use ($expectedLastName) {
                    return $expectedLastName;
                }
            ]
        );

        self::assertEquals(
            $expectedLastName,
            $user->getLastName(),
            "should build with overwritten last name from callable"
        );
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

        self::assertEquals($expectedFirstName, $user->getFirstName(), "should build defined first name");
        self::assertEquals($expectedLastName, $user->getLastName(), "should build defined last name");
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

        self::assertEquals($expectedId1, $user1->getfirstName(), "should build custom sequence first name");
        self::assertEquals($expectedId2, $user2->getfirstName(), "should build custom sequence first name");
    }

    public function testSetsDefaultSequenceValuesWithAliases()
    {
        $expectedId1 = 1;
        $expectedId2 = 2;
        $expectedId3 = 3;
        FactoryBot::define(
            UserModel::class,
            ["id" => FactoryBot::sequence()],
            ["aliases" => ["Admin"]]
        );

        $user1 = FactoryBot::build(UserModel::class);
        $admin = FactoryBot::build("Admin");
        $user2 = FactoryBot::build(UserModel::class);

        self::assertEquals($expectedId1, $user1->getId(), "should build with auto sequence id");
        self::assertEquals($expectedId2, $admin->getId(), "should build with auto sequence id incremented");
        self::assertEquals($expectedId3, $user2->getId(), "should build with auto sequence id incremented");
    }

    public function testSetsCallableDefaultValues()
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

        self::assertEquals($expectedFirstName, $user->getFirstName(), "should build defined first name");
        self::assertEquals($expectedLastName, $user->getLastName(), "should build last name from callable");
    }

    public function testSetsDependendValues()
    {
        $expectedFirstName = "Jane";
        $expectedLastName = "Doe";
        $expectedEmail = "Jane.Doe@example.com";
        FactoryBot::define(
            UserModel::class,
            [
                "firstName" => $expectedFirstName,
                "lastName" => $expectedLastName,
                "email" => function ($model) {
                    return $model->getfirstName() . "." . $model->getLastName() . "@example.com";
                }
            ]
        );

        $user = FactoryBot::build(UserModel::class);

        self::assertEquals($expectedFirstName, $user->getFirstName(), "should build defined first name");
        self::assertEquals($expectedLastName, $user->getLastName(), "should build defined last name");
        self::assertEquals($expectedEmail, $user->getEmail(), "should build email dependend on first and last name");
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

        self::assertEquals($expectedFirstName, $user->getFirstName(), "should build with defined first name");
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

    public function testRejectsNotSettableDefaultValues()
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
                "name" => $expectedName
            ]
        );
        FactoryBot::define(
            UserModel::class,
            [
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

    public function testBuildsRelations()
    {
        $expectedName = "car name";
        $expectedCount = 2;
        FactoryBot::define(
            CarModel::class,
            [
                "name" => $expectedName
            ]
        );
        FactoryBot::define(
            UserModel::class,
            [
                "cars" => FactoryBot::relations(CarModel::class, 2)
            ]
        );

        $user = FactoryBot::build(UserModel::class);

        self::assertEquals($expectedName, $user->getCars()[0]->getName(), "should build nested model");
        self::assertEquals($expectedCount, count($user->getCars()), "should build 2 car models");
    }

    public function testOverridesAcceptsModelInstance()
    {
        $expectedName = "new account name";
        FactoryBot::define(AccountModel::class, ["name" => "account name"]);
        FactoryBot::define(UserModel::class, ["account" => FactoryBot::relation(AccountModel::class)]);
        $account = FactoryBot::build(AccountModel::class, ["name" => $expectedName]);

        $user = FactoryBot::build(UserModel::class, ["account" => $account]);

        self::assertEquals($expectedName, $user->getAccount()->getName(), "should overwrite nested model");
    }

    public function testInheritsBuildStrategyBuildOnRelation()
    {
        FactoryBot::define(AccountModel::class);
        FactoryBot::define(UserModel::class, ["account" => FactoryBot::relation(AccountModel::class)]);

        $user = FactoryBot::build(UserModel::class);

        self::assertTrue($user->getAccount()->isNew(), "should build nested model");
    }

    public function testInheritsBuildStrategyCreateOnRelation()
    {
        FactoryBot::define(AccountModel::class);
        FactoryBot::define(UserModel::class, ["account" => FactoryBot::relation(AccountModel::class)]);

        $user = FactoryBot::create(UserModel::class);

        self::assertFalse($user->getAccount()->isNew(), "should create nested model");
    }

    public function testInheritsBuildStrategyBuildOnRelations()
    {
        $expectedCount = 2;
        FactoryBot::define(CarModel::class);
        FactoryBot::define(UserModel::class, ["cars" => FactoryBot::relations(CarModel::class, 2)]);

        $user = FactoryBot::build(UserModel::class);

        foreach ($user->getCars() as $car) {
            self::assertTrue($car->isNew(), "should build nested model");
        }
        self::assertEquals($expectedCount, count($user->getCars()), "should have 2 relations");
    }

    public function testInheritsBuildStrategyCreateOnRelations()
    {
        $expectedCount = 2;
        FactoryBot::define(CarModel::class);
        FactoryBot::define(UserModel::class, ["cars" => FactoryBot::relations(CarModel::class, 2)]);

        $user = FactoryBot::create(UserModel::class);

        foreach ($user->getCars() as $car) {
            self::assertFalse($car->isNew(), "should create nested model");
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

    public function testCallsglobalLifecycleHooks()
    {
        $spy1 = false;
        $spy2 = false;
        $spy3 = false;
        FactoryBot::registerGlobalHook(
            "before",
            function () use (&$spy1) {
                $spy1 = true;
            }
        );
        FactoryBot::registerGlobalHook(
            "afterBuild",
            function () use (&$spy2) {
                $spy2 = true;
            }
        );
        FactoryBot::registerGlobalHook(
            "afterCreate",
            function () use (&$spy3) {
                $spy3 = true;
            }
        );
        FactoryBot::define(UserModel::class);
        FactoryBot::build(UserModel::class);

        self::assertTrue($spy1, "should call global `before` hook");
        self::assertTrue($spy2, "should call global `afterBuild` hook");
        self::assertFalse($spy3, "should not call global `afterCreate` hook");
    }

    public function testCallsFactoryLifecycleHook()
    {
        $spy1 = false;
        $spy2 = false;

        FactoryBot::define(
            UserModel::class,
            [],
            ["hooks" => [
                FactoryBot::hook("afterCreate", function () use (&$spy1) {
                    $spy1 = true;
                }),
                FactoryBot::hook("before", function () use (&$spy2) {
                    $spy2 = true;
                }),
            ]]
        );

        FactoryBot::build(UserModel::class);

        $this->assertFalse($spy1, "should not call Factories `afterCreate` hook");
        $this->assertTrue($spy2, "should call Factories `before` Hook");
    }

    public function testCallsMultipleLifecycleHooksForSameStage()
    {
        $spy1 = false;
        $spy2 = false;
        $spy3 = false;
        $spy4 = false;

        FactoryBot::registerGlobalHook("before", function () use (&$spy1) {
            $spy1 = true;
        });
        FactoryBot::registerGlobalHook("before", function () use (&$spy2) {
            $spy2 = true;
        });
        FactoryBot::define(
            UserModel::class,
            [],
            ["hooks" => [
                FactoryBot::hook("before", function () use (&$spy3) {
                    $spy3 = true;
                }),
                FactoryBot::hook("before", function () use (&$spy4) {
                    $spy4 = true;
                }),
            ]]
        );

        FactoryBot::build(UserModel::class);

        $this->assertTrue($spy1, "should call 1st global `before` Hook");
        $this->assertTrue($spy2, "should call 2nd global `before` Hook");
        $this->assertTrue($spy3, "should call 1st Factories `before` Hook");
        $this->assertTrue($spy4, "should call 2nd Factories `before` Hook");
    }
}
