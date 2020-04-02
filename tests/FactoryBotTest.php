<?php

use FactoryBot\FactoryBot;
use PHPUnit\Framework\TestCase;

class FactoryBotTest extends TestCase
{
    protected function setUp()
    {
        FactoryBot::purge();
    }

    public function test_builds_specified_class()
    {
        $expected = UserModel::class;

        FactoryBot::define("User", [], ["class" => $expected]);

        self::assertInstanceOf($expected, FactoryBot::build("User"), "should build defined class");
    }

    public function test_builds_assumed_class()
    {
        $expected = UserModel::class;

        FactoryBot::define($expected);

        self::assertInstanceOf($expected, FactoryBot::build($expected), "should build assumed class");
    }

    public function test_creates_assumed_class()
    {
        $expectedClass = UserModel::class;
        FactoryBot::define($expectedClass);

        $user = FactoryBot::create($expectedClass);

        self::assertInstanceOf($expectedClass, $user, "should build assumed class");
        self::assertFalse($user->isNew(), "should save model");
    }

    public function test_fails_on_not_existing_class()
    {
        $expected = "NotexistingClass";

        $this->setExpectedException("InvalidArgumentException");

        FactoryBot::define($expected);
    }

    public function test_allows_aliases()
    {
        $expectedClass = UserModel::class;
        $alias1 = "User";
        $alias2 = "Admin";

        FactoryBot::define($expectedClass, [], ["aliases" => [$alias1, $alias2]]);

        self::assertInstanceOf($expectedClass, FactoryBot::build($expectedClass), "should build class from name");
        self::assertInstanceOf($expectedClass, FactoryBot::build($alias1), "should build from alias");
        self::assertInstanceOf($expectedClass, FactoryBot::build($alias2), "should build from alias");
    }

    public function test_sets_overrides()
    {
        $expectedFirstName = "test first name";
        $expectedLastName = "test last name";
        FactoryBot::define(UserModel::class);

        $user = FactoryBot::build(UserModel::class, [
            "firstName" => $expectedFirstName,
            "lastName" => $expectedLastName
        ]);

        self::assertEquals($expectedFirstName, $user->getFirstName(), "should build with overwritten first name");
        self::assertEquals($expectedLastName, $user->getLastName(), "should build with overwritten last name");
    }

    public function test_sets_overrides_can_be_closure()
    {
        $expectedFirstName = "test first name";
        $expectedLastName = "test last name";
        FactoryBot::define(UserModel::class);

        $user = FactoryBot::build(UserModel::class, [
            "firstName" => $expectedFirstName,
            "lastName" => function () use($expectedLastName) { return $expectedLastName; }
        ]);

        self::assertEquals($expectedFirstName, $user->getFirstName(), "should build with overwritten first name");
        self::assertEquals($expectedLastName, $user->getLastName(), "should build with overwritten last name");
    }

    public function test_rejects_malformated_overrides()
    {
        FactoryBot::define(UserModel::class);

        $this->setExpectedException(
            "InvalidArgumentException",
            "`\$overrides` has to be provided as an associative array"
        );

        FactoryBot::build(UserModel::class, "firstname");
    }

    public function test_rejects_malformated_overrides_2()
    {
        FactoryBot::define(UserModel::class);

        $this->setExpectedException("InvalidArgumentException", "UserModel has no setter for `nickname`!");

        FactoryBot::build(UserModel::class, ["nickname" => "nick"]);
    }

    public function test_sets_default_values()
    {
        $expectedFirstName = "test first name";
        $expectedLastName = "test last name";
        FactoryBot::define(UserModel::class, [
            "firstName" => $expectedFirstName,
            "lastName" => $expectedLastName
        ]);

        $user = FactoryBot::build(UserModel::class);

        self::assertEquals($expectedFirstName, $user->getFirstName(), "should build with overwritten first name");
        self::assertEquals($expectedLastName, $user->getLastName(), "should build with overwritten last name");
    }

    public function test_sets_default_sequence_values()
    {
        $expectedId1 = 1;
        $expectedId2 = 2;
        FactoryBot::define(UserModel::class, [
            "id" => FactoryBot::sequence()
        ]);

        $user1 = FactoryBot::build(UserModel::class);
        $user2 = FactoryBot::build(UserModel::class);

        self::assertEquals($expectedId1, $user1->getId(), "should build with auto sequence id");
        self::assertEquals($expectedId2, $user2->getId(), "should build with auto sequence id");
    }

    public function test_sets_custom_sequence_values()
    {
        $expectedId1 = "User1";
        $expectedId2 = "User2";
        FactoryBot::define(UserModel::class, [
            "firstName" => FactoryBot::sequence(function($num) {
                return "User" . $num;
            })
        ]);

        $user1 = FactoryBot::build(UserModel::class);
        $user2 = FactoryBot::build(UserModel::class);

        self::assertEquals($expectedId1, $user1->getfirstName(), "should build with overwritten first name");
        self::assertEquals($expectedId2, $user2->getfirstName(), "should build with overwritten first name");
    }

    public function test_sets_default_sequence_values_with_aliases()
    {
        $expectedId1 = 1;
        $expectedId2 = 2;
        FactoryBot::define(UserModel::class, [
            "id" => FactoryBot::sequence()
        ], [
            "aliases" => ["Admin"]
        ]);

        $user = FactoryBot::build(UserModel::class);
        $admin = FactoryBot::build("Admin");

        self::assertEquals($expectedId1, $user->getId(), "should build with auto sequence id");
        self::assertEquals($expectedId2, $admin->getId(), "should build with auto sequence id");
    }

    public function test_sets_default_closure_values()
    {
        $expectedFirstName = "test first name";
        $expectedLastName = "test last name";
        FactoryBot::define(UserModel::class, [
            "firstName" => $expectedFirstName,
            "lastName" => function () use($expectedLastName) { return $expectedLastName; }
        ]);

        $user = FactoryBot::build(UserModel::class);

        self::assertEquals($expectedFirstName, $user->getFirstName(), "should build with overwritten first name");
        self::assertEquals($expectedLastName, $user->getLastName(), "should build with overwritten last name");
    }

    public function test_sets_dependend_values()
    {
        $expectedFirstName = "Jane";
        $expectedLastName = "Doe";
        $expectedEmail = "Jane.Doe@has-to-be.com";
        FactoryBot::define(UserModel::class, [
            "firstName" => $expectedFirstName,
            "lastName" => $expectedLastName,
            "email" => function ($model) { return $model->getfirstName().".".$model->getLastName()."@has-to-be.com"; }
        ]);

        $user = FactoryBot::build(UserModel::class);

        self::assertEquals($expectedFirstName, $user->getFirstName(), "should build with overwritten first name");
        self::assertEquals($expectedLastName, $user->getLastName(), "should build with overwritten last name");
        self::assertEquals($expectedEmail, $user->getEmail(), "should build with overwritten email");
    }

    public function test_sets_default_values_and_overrides()
    {
        $expectedFirstName = "test first name";
        $expectedLastName = "test last name";
        FactoryBot::define(UserModel::class, [
            "firstName" => $expectedFirstName,
            "lastName" => "last name"
        ]);

        $user = FactoryBot::build(UserModel::class, [
            "lastName" => $expectedLastName
        ]);

        self::assertEquals($expectedFirstName, $user->getFirstName(), "should build with overwritten first name");
        self::assertEquals($expectedLastName, $user->getLastName(), "should build with overwritten last name");
    }

    public function test_rejects_malformated_default_values()
    {

        $this->setExpectedException(
            "InvalidArgumentException",
            "`\$properties` has to be provided as an associative array"
        );

        FactoryBot::define(UserModel::class, "firstname");
    }

    public function test_rejects_malformated_default_values_2()
    {
        $this->setExpectedException("InvalidArgumentException", "UserModel has no setter for `nickname`!");

        FactoryBot::define(UserModel::class, ["nickname" => "nick"]);
    }

    public function test_creates_relation()
    {
        $expectedName = "account name";
        FactoryBot::define(AccountModel::class, [
            "id" => 22,
            "name" => $expectedName
        ]);
        FactoryBot::define(UserModel::class, [
            "id" => 1,
            "firstName" => "first name",
            "lastName" => "last name",
            "account" => FactoryBot::relation(AccountModel::class)
        ]);

        $user = FactoryBot::build(UserModel::class);

        self::assertEquals($expectedName, $user->getAccount()->getName(), "should build nested model");
    }

    public function test_cyclic_relation()
    {
        $expectedName = "user name";
        FactoryBot::define(UserModel::class, [
            "id" => 1,
            "firstName" => "first name",
            "lastName" => "last name",
            "subordinate" => FactoryBot::relation(UserModel::class, [
                "subordinate" => null,
                "firstName" => $expectedName
            ])
        ]);

        $user = FactoryBot::build(UserModel::class);

        self::assertEquals($expectedName, $user->getSubordinate()->getFirstName(), "should build nested model");
    }

    public function test_creates_relations()
    {
        $expectedName = "car name";
        $expectedCount = 2;
        FactoryBot::define(CarModel::class, [
            "id" => 22,
            "name" => $expectedName
        ]);
        FactoryBot::define(UserModel::class, [
            "id" => 1,
            "firstName" => "first name",
            "lastName" => "last name",
            "cars" => FactoryBot::relations(CarModel::class, 2)
        ]);

        $user = FactoryBot::build(UserModel::class);

        self::assertEquals($expectedName, $user->getCars()[0]->getName(), "should build nested model");
        self::assertEquals($expectedCount, count($user->getCars()), "should build 2 car models");
    }

    public function test_overrides_accepts_model()
    {
        $expectedName = "new account name";
        FactoryBot::define(AccountModel::class, [
            "id" => 22,
            "name" => "account name"
        ]);
        FactoryBot::define(UserModel::class, [
            "id" => 1,
            "firstName" => "first name",
            "lastName" => "last name",
            "account" => FactoryBot::relation(AccountModel::class)
        ]);

        $account = FactoryBot::build(AccountModel::class, [
            "name" => $expectedName
        ]);
        $user = FactoryBot::build(UserModel::class, [
            "account" => $account
        ]);

        self::assertEquals($expectedName, $user->getAccount()->getName(), "should build nested model");
    }

    public function test_inherits_build_strategy_build_on_relation()
    {
        FactoryBot::define(AccountModel::class, [
            "id" => 22,
            "name" => "account name"
        ]);
        FactoryBot::define(UserModel::class, [
            "id" => 1,
            "firstName" => "first name",
            "lastName" => "last name",
            "account" => FactoryBot::relation(AccountModel::class)
        ]);

        $user = FactoryBot::build(UserModel::class);

        self::assertTrue($user->getAccount()->isNew(), "should build nested model");
    }

    public function test_inherits_build_strategy_create_on_relation()
    {
        FactoryBot::define(AccountModel::class, [
            "id" => 22,
            "name" => "account name"
        ]);
        FactoryBot::define(UserModel::class, [
            "id" => 1,
            "firstName" => "first name",
            "lastName" => "last name",
            "account" => FactoryBot::relation(AccountModel::class)
        ]);

        $user = FactoryBot::create(UserModel::class);

        self::assertFalse($user->getAccount()->isNew(), "should build nested model");
    }

    public function test_inherits_build_strategy_build_on_relations()
    {
        $expectedName = "car name";
        $expectedCount = 2;
        FactoryBot::define(CarModel::class, [
            "id" => 22,
            "name" => $expectedName
        ]);
        FactoryBot::define(UserModel::class, [
            "id" => 1,
            "firstName" => "first name",
            "lastName" => "last name",
            "cars" => FactoryBot::relations(CarModel::class, 2)
        ]);

        $user = FactoryBot::build(UserModel::class);

        foreach ($user->getCars() as $car) {
            self::assertTrue($car->isNew(), "should build nested model");
        }
    }

    public function test_inherits_build_strategy_create_on_relations()
    {
        $expectedName = "car name";
        $expectedCount = 2;
        FactoryBot::define(CarModel::class, [
            "id" => 22,
            "name" => $expectedName
        ]);
        FactoryBot::define(UserModel::class, [
            "id" => 1,
            "firstName" => "first name",
            "lastName" => "last name",
            "cars" => FactoryBot::relations(CarModel::class, 2)
        ]);

        $user = FactoryBot::create(UserModel::class);

        foreach ($user->getCars() as $car) {
            self::assertFalse($car->isNew(), "should build nested model");
        }
        self::assertEquals($expectedCount, count($user->getCars()), "should have 2 relations");
    }

    public function test_factory_allows_inheritance()
    {
        $expectedRole = "admin";
        $expectedFirstName = "test user";
        FactoryBot::define(UserModel::class, [
            "firstName" => $expectedFirstName,
            "role" => "user",
        ]);
        FactoryBot::extend("Admin", UserModel::class, [
            "role" => $expectedRole
        ]);

        $admin = FactoryBot::build("Admin");

        self::assertEquals($expectedFirstName, $admin->getFirstName(), "should build firstName of parent");
        self::assertEquals($expectedRole, $admin->getRole(), "should build role");
    }
}

/**
 * this class fakes Propel ORM integration
 */
class FakeORM
{
    private $new = true;

    public function save() {
        $this->new = false;
    }

    public function isNew() {
        return $this->new;
    }
}

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

class UserModel extends FakeORM
{
    private $id;
    private $firstName;
    private $lastName;
    private $email;
    // 1:1 relationship
    private $account;
    private $role;
    // 1:n relationship
    private $cars;
    // cyclic relation
    private $subordinate;

    public function setId($Id)
    {
        $this->id = $Id;
    }
    public function getId()
    {
        return $this->id;
    }

    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }
    public function getFirstName()
    {
        return $this->firstName;
    }

    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }
    public function getLastName()
    {
        return $this->lastName;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }
    public function getEmail()
    {
        return $this->email;
    }

    public function setAccount($account)
    {
        $this->account = $account;
    }
    public function getAccount()
    {
        return $this->account;
    }

    public function setRole($role)
    {
        $this->role = $role;
    }
    public function getRole()
    {
        return $this->role;
    }
    public function setCars($cars)
    {
        $this->cars = $cars;
    }
    public function getCars()
    {
        return $this->cars;
    }
    public function setSubordinate($subordinate)
    {
        $this->subordinate = $subordinate;
    }
    public function getSubordinate()
    {
        return $this->subordinate;
    }
}

class AccountModel extends FakeORM
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

class CarModel extends FakeORM
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