![Version](https://img.shields.io/github/v/tag/Godfather27/FactoryBot?label=latest%20version) ![PHP Composer](https://github.com/Godfather27/FactoryBot/workflows/PHP%20Composer/badge.svg) ![Coverage](https://img.shields.io/codecov/c/github/Godfather27/FactoryBot) ![Maintainability](https://api.codeclimate.com/v1/badges/e0f8e8dae62079f141d2/maintainability)

# FactoryBot

This is inspired by thoughtbot's ruby [factory_bot](https://github.com/thoughtbot/factory_bot).

FactoryBot is a fixtures replacement with a straightforward definition syntax, support for multiple build strategies (saved instances, unsaved instances), and support for multiple factories for the same class (user, admin_user, and so on), including factory inheritance.

## Installation

Install the latest version with

        $ composer require factory-bot/factory-bot

## Configure your test suite

### PHPUnit

use `FactoryBot\FactoryBot` in your bootstrap `setup.php` file.

Define all Factories you want to use in your setup file.

`phpunit.xml`

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<phpunit bootstrap="tests/setup.php">
```

`tests/setup.php`

```php
// setup other stuff

use FactoryBot\FactoryBot;

FactoryBot::define(
    UserModel::class,
    ["firstName" => "John", "lastName" => "Doe"]
);

// define more Factories
```

## Defining factories

Each factory has a name and a set of attributes. The name is used to guess the class of the object by default:

```php
use FactoryBot\FactoryBot;

FactoryBot::define(
    UserModel::class,
    ["firstName" => "John", "lastName" => "Doe"]
);
```

It is also possible to explicitly specify the class:

```php
use FactoryBot\FactoryBot;

FactoryBot::define(
    "Admin",
    ["firstName" => "John", "lastName" => "Doe"],
    ["class" => UserModel::class]
);
```

It is highly recommended that you have one factory for each class that provides the simplest set of attributes necessary to create an instance of that class. Other factories can be created through inheritance to cover common scenarios for each class.

Attempting to define multiple factories with the same name will overwrite the previously defined factory.

## Using factories

FactoryBot supports several different build strategies: `build`, `create`

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
FactoryBot::build(UserModel::class, ["firstName" => "Jane"]);
```

## Aliases

FactoryBot allows you to define aliases to existing factories to make them easier to re-use. This could come in handy when, for example, your Post object has an author attribute that actually refers to an instance of a User class. While normally FactoryBot can infer the factory name from the association name, in this case it will look for an author factory in vain. So, alias your user factory so it can be used under alias names.

```php
FactoryBot::define(
    UserModel::class,
    ["firstName" => "Jane"],
    ["aliases" => ["Author", "Commenter"]]
);

FactoryBot::define(
    PostModel::class,
    [
        "title" => "lorem ipsum!",
        "body" => "lorem ipsum dolor sit amet",
        "author" => FactoryBot::relation("Author")
    ]
);

FactoryBot::define(
    CommentModel::class,
    [
        "body" => "lorem ipsum dolor sit amet",
        "commenter" => FactoryBot::relation("Commenter")
    ]
);
```

## Dependent Attributes

Attributes can be based on the values of other attributes using the callable definition style.
The callable gets passed in the partially hydrated model.
The model gets hydrated with the specified params in order, so putting the email last enables us to access `firstName` and `lastName`.

```php
FactoryBot::define(
    UserModel::class,
    [
        "firstName" => "John",
        "lastName" => "Doe",
        "email" => function ($model) {
            return strtolower(
                $model->getfirstName() . "." . $model->getLastName() . "@example.com"
            );
        }
    ]
);

FactoryBot::build(UserModel::class)->getEmail()
# > "john.doe@example.com"
```

## Inheritance

You can easily create multiple factories for the same class without repeating common attributes by nesting factories:

```php
# Define a basic user
FactoryBot::define(
    UserModel::class,
    [
        "firstName" => "Jane",
        "lastName" => "Doe",
        "role" => "user"
    ]
);

# Extend the User Model as an Admin Factory
FactoryBot::extend("Admin", UserModel::class, ["role" => "admin"]);
```

As mentioned above, it's good practice to define a basic factory for each class with only the attributes required to create it. Then, create more specific factories that inherit from this basic parent. Factory definitions are still code, so keep them DRY.

## Relations

It's possible to set up relations within factories. Use the relation method from FactoryBot and provide the Factory which should be used.

```php
FactoryBot::define(
    PostModel::class,
    [
        "title" => "lorem ipsum!",
        "body" => "lorem ipsum dolor sit amet",
        "author" => FactoryBot::relation("Author")
    ]
);
```

Relations default to using the same build strategy as their parent object:

```php
FactoryBot::define(
    PostModel::class,
    ["author" => FactoryBot::relation("Author")]
);

$post = FactoryBot::create(PostModel::class);
$post->isNew();              # > false
$post->getAuthor()->isNew(); # > false

$post2 = FactoryBot::build(PostModel::class);
$post2->isNew();              # > true
$post2->getAuthor()->isNew(); # > true
```

To generate has many relationships you can use the relations method:

```php
FactoryBot::define(
    UserModel::class,
    ["posts" => FactoryBot::relations(PostModel::class, 2)]
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
        "subordinate" => FactoryBot::relation(
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
FactoryBot::define(UserModel::class, ["id" => FactoryBot::sequence()]);
```

To implement your own sequence method pass a method which generates a unique sequence value per call.

```php
FactoryBot::define(
    UserModel::class,
    [
        "email" => FactoryBot::sequence(function($num, $model) {
            return "user" . $num . "@example.com";
        })
    ]
);

$user = FactoryBot::build(UserModel::class);
$user->getEmail() # > "user1@example.com"
```

## Custom Strategies

The capability of FactoryBot can be extended by implementing custom Strategies.

A Strategy must implement the `FactoryBot/Strategies/StrategyInterface` Interface.
The Interface defines two methods. `beforeCompile` is a method which gets called before the hydration step is executed. The second method is `result` which gets called after the hydration step, here you can do some changes to the instance before returning it.

In this example the Strategy will return the instance as a JSON string.

```php
use FactoryBot\FactoryBot;
use FactoryBot\Strategies\StrategyInterface;

class JsonStrategy implements StrategyInterface
{
    public static function beforeCompile($factory)
    {
        // we call $factory->notify to ensure the Factories "before" Hooks get called
        $factory->notify("before");
    }

    public static function result($factory, $instance)
    {
        // we call $factory->notify to ensure the Factories "after" Hooks get called
        $factory->notify("after");

        $result = self::getPropertiesArray($instance);

        return json_encode($result);
    }

    public static function getPropertiesArray($instance)
    {
        $instanceArray = (array) $instance;
        $result = [];
        foreach ($instanceArray as $keyWithVisibility => $value) {
            $keySegments = explode("\0", $keyWithVisibility);
            $keyWithoutVisibility = end($keySegments);
            $result[$keyWithoutVisibility] = $value;
        }
        return $result;
    }
}

FactoryBot::registerStrategy("json", JsonStrategy::class);
FactoryBot::define(UserModel::class, ["firstName" => "Jane"]);
FactoryBot::json(UserModel::class); # > '{"id":null,"firstName":"Jane",...,"subordinate":null,"new":true}'
```

## Lifecycle Hooks

FactoryBot provides 6 different hooks to inject custom code.

- `before` - called before building or creating an instance.
- `after` - called after building or creating an instance.
- `beforeCreate` - called before creating an instance.
- `afterCreate` - called after creating an instance.
- `beforeBuild` - called before building an instance.
- `afterBuild` - called after building an instance.

Factories can also define any number of the same kind of hook. These hooks will be executed in the order they are specified.

### Hook per Factory

On the definition of a Factory you can register a lifecycle hook, which only gets executed on the Factory where it is defined.

Example:

```php
$logger = new Logger();

FactoryBot::define(
    UserModel::class,
    ["name" => "Jane Doe"],
    ["hooks" => [
        FactoryBot::hook("afterCreate", function ($instance) use ($logger) {
            $logger->debug("created an UserModel instance: $instance->getName()");
        })
    ]]
);

$user = FactoryBot::create(UserModel::class); # logger output > "created an UserModel instance: Jane Doe"
```

### global Hook

A Hook can also be registered for all Factories.

```php
$logger = new Logger();

FactoryBot::registerGlobalHook("afterCreate", function ($instance) use ($logger) {
    $class = get_class($instance);
    $logger->debug("created an $class instance");
});

FactoryBot::define(UserModel::class);
FactoryBot::define(PostModel::class);

$user = FactoryBot::create(UserModel::class); # logger output > "created an UserModel instance"
$post = FactoryBot::create(PostModel::class); # logger output > "created an PostModel instance"
```

A Hook can also be removed again.

```php
$logger = new Logger();

$hook = FactoryBot::registerGlobalHook("afterCreate", function ($instance) use ($logger) {
    $class = get_class($instance);
    $logger->debug("created an $class instance");
});

FactoryBot::define(UserModel::class);
FactoryBot::define(PostModel::class);

$user = FactoryBot::create(UserModel::class); # logger output > "created an UserModel instance"
$post = FactoryBot::create(PostModel::class); # logger output > "created an PostModel instance"

FactoryBot::removeGlobalHook($hook);

$user = FactoryBot::create(UserModel::class); # no logger output
$post = FactoryBot::create(PostModel::class); # no logger output
```

## using FactoryBot with php faker

Faker methods should be wrapped by a callable. This way the faker method will be called during the build process.
Otherwise the faker method will be called on definition and all instances will have the same value.

using a local instance of faker:
```php
use Faker\Factory;
use FactoryBot\FactoryBot;

$faker = Factory::create('at_AT');
FactoryBot::define(
    UserModel::class,
    [
        "name" => "Jane Doe",
        # local variables have to be injected using use
        "street" => function () use ($faker) { return $faker->streetName(); },
    ]
);
```

using an instance variable:
```php
use Faker\Factory;
use FactoryBot\FactoryBot;

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
        FactoryBot::define(
            UserModel::class,
            [
                "name" => "Jane Doe",
                # instance variables can be accessed without use
                "street" => function () { return $this->faker->streetName(); },
            ]
        );
    }
}
```
