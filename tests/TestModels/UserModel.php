<?php

namespace FactoryBot\Tests\TestModels;

/**
 * model to test default behaviour of FactoryBot
 * @package FactoryBot\Tests\TestModels
 */
class UserModel extends FakePropel
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
