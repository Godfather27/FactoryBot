# FactoryBot

This is inspired by thoughtbot's ruby [FactoryBot](https://github.com/thoughtbot/factory_bot).

## Configure your test suite

### PHPUnit

use `FactoryBot\FactoryBot` in your bootstrap `setup.php` file.

Define all Factories you want to use.

`phpunit.xml`

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<phpunit bootstrap="tests/setup.php">
```

## Defining factories

Each factory has a name and a set of attributes. The name is used to guess the class of the object by default:

```php
use FactoryBot\FactoryBot;

FactoryBot::define(UserModel::class, [
    "firstName" => "John",
    "lastName" => "Doe"
]);
```

It is also possible to explicitly specify the class:

```php
use FactoryBot\FactoryBot;

FactoryBot::define("Admin", [
    "firstName" => "John",
    "lastName" => "Doe"
], ["class" => UserModel::class]);
```

It is highly recommended that you have one factory for each class that provides the simplest set of attributes necessary to create an instance of that class. Other factories can be created through inheritance to cover common scenarios for each class.

Attempting to define multiple factories with the same name will raise an error.

## Using factories

factory_bot supports several different build strategies: build, create

```php
use FactoryBot\FactoryBot;

# Returns a User instance that's not saved
FactoryBot::build(UserModel::class)

# Returns a saved User instance
FactoryBot::create(UserModel::class);
```

No matter which strategy is used, it's possible to override the defined attributes by passing an array:

```php
# Build a User instance and override the first_name property
FactoryBot::build(UserModel::class, [
    "firstName" => "Jane"
]);
```

## Aliases

factory_bot allows you to define aliases to existing factories to make them easier to re-use. This could come in handy when, for example, your Post object has an author attribute that actually refers to an instance of a User class. While normally factory_bot can infer the factory name from the association name, in this case it will look for an author factory in vain. So, alias your user factory so it can be used under alias names.

```php
FactoryBot::define(
    UserModel::class,
    ["firstName" => "Jane"],
    ["aliases" => ["Author", "Commenter"]]);

FactoryBot::define(
    PostModel:class,
    [
        "title" => "lorem ipsum!",
        "body" => "lorem ipsum dolor sit amet",
        "author" => FatoryBot::relation("Author")
    ]
);

FactoryBot::define(
    CommentModel:class,
    [
        "body" => "lorem ipsum dolor sit amet",
        "commenter" => FatoryBot::relation("Commenter")
    ]
);
```

## Dependent Attributes

Attributes can be based on the values of other attributes using the closure definition style.
The closure gets passed in the partially hydrated model.
The model gets hydrated with the specified params in order, so putting the email last enables us to access `firstName` and `lastName`.

```php
FactoryBot::define(
    UserModel::class,
    [
        "firstName" => "John",
        "lastName" => "Doe",
        "email" => function ($model) {
            return strtolower(
                $model->getfirstName() . "." . $model->getLastName() . "@has-to-be.com"
            );
        }
    ]
);

FactoryBot::build(UserModel::class)->getEmail()
# > "john.doe@has-to-be.com"
```

## Inheritance

You can easily create multiple factories for the same class without repeating common attributes by nesting factories:

```php
# Define a basic user
FactoryBot::define(UserModel::class, [
    "firstName" => "Jane",
    "lastName" => "Doe",
    "role" => "user"
]);

# Extend the User Model as an Admin Factory
FactoryBot::extend("Admin", UserModel::class, [
    "role" => "admin"
]);
```

As mentioned above, it's good practice to define a basic factory for each class with only the attributes required to create it. Then, create more specific factories that inherit from this basic parent. Factory definitions are still code, so keep them DRY.

## Relations

It's possible to set up relations within factories. Use the relation method from FactoryBot and provide the Factory which should be used.

```php
FactoryBot::define(
    PostModel:class,
    [
        "title" => "lorem ipsum!",
        "body" => "lorem ipsum dolor sit amet",
        "author" => FatoryBot::relation("Author")
    ]
);
```

Relations default to using the same build strategy as their parent object:

```php
FactoryBot::define(
    PostModel:class,
    [
        "author" => FatoryBot::relation("Author")
    ]
);

$post = FactoryBot::create(PostModel:class);
$post->isNew();              # > false
$post->getAuthor()->isNew(); # > false

$post2 = FactoryBot::build(PostModel:class);
$post2->isNew();              # > true
$post2->getAuthor()->isNew(); # > true
```

To generate has many relationships you can use the relations method:

```php
FactoryBot::define(
    UserModel::class,
    [
        "posts" => FatoryBot::relations(PostModel::class, 2)
    ]
);

$user = FactoryBot::build(UserModel::class);
$user->getPosts(); # > [PostModel, PostModel]
```

To generate cyclic relationships you should set children to `null` to avoid infinite children generation:

```php
FactoryBot::define(
    UserModel::class,
    [
        "firstName" => "Jane",
        "lastName" => "Doe",
        "subordinate" => FatoryBot::relation(
            UserModel::class,
            ["subordinate" => null]
        )
    ]
);

$user = FactoryBot::build(UserModel::class);
$user->getSubordinate(); # > UserModel
$user->getSubordinate()->getSubordinate(); # > null
```


## Sequences

Unique values in a specific format (for example, e-mail addresses) can be generated using sequences.

The default implementation will generate a sequence of numbers, like the classic auto increment in SQL.

```php
FactoryBot::define(UserModel::class, [
    "id" => FactoryBot::sequence()
]);
```

To implement your own sequence method pass a method which generates a unique sequence value per call.

```php
FactoryBot::define(UserModel::class, [
    "email" => FactoryBot::sequence(function($num, $model) {
        return "user" . $num . "@has-to-be.com";
    })
]);

$user = FactoryBot::build(UserModel::class);
$user->getEmail() # > "user1@has-to-be.com"
```

## using FactoryBot with php faker

Faker methods should always be called inside Closure functions. This way the faker method will be called on building.
Otherwise the faker method will be called on definition and all instances will have the same value.

```php
$faker = Factory::create('at_AT');
FactoryBot::define(UserModel::class, [
    "name" => "Jane Doe",
    "street" => function () use($faker) { return $faker->streentName(); }, # local variables have to be injected using use
]);
```

```php
class FactorySetup
{
    /**
     * faker generator
    */
    private $faker;

    public function __construct()
    {
        $this->faker = Factory::create('at_AT');
        $this->setUpUserFactory()
    }

    public function setUpUserFactory()
    {
        FactoryBot::define(UserModel::class, [
            "name" => "Jane Doe",
            "street" => function () { return $this->faker->streentName(); }, # instance variables can be accessed without use
        ]);
    }
}
```