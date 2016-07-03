# Windwalker DI

Windwalker DI is a [dependency injection](http://en.wikipedia.org/wiki/Dependency_injection) tools,
provide us an [IOC](http://en.wikipedia.org/wiki/Inversion_of_control) container to manage objects and data.
We also support service provider to help developers build their service in a universal interface.

Windwalker DI is a modified version of Joomla DI Container.

For more information about IOC and DI, please see
[Inversion of Control Containers and the Dependency Injection pattern](http://martinfowler.com/articles/injection.html) by Martin Fowler.

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/di": "~2.0"
    }
}
```

## Create A Container

Just new an instance.

``` php
use Windwalker\DI\Container

$container = new Container;
```

Now we can store objects into it.

``` php
$input = new Input;

$container->set('input', $input);

$input = $container->get('input');
```

## Lazy Loading

Sometimes we will hope not create object instantly, we can use callback to create object.

``` php
// Set a closure into it
$container->set('input', function(Container $container)
{
    return new Input;
});

// Will call this closure when we get it
$input = $container->get('input');
```

But if we use `set()` method to set callback, this object will be recreated when every time we try to get it.

## Shared Object (Singleton)

Use `set('foo', $object, true)` or `share('foo', $object)` to make an object singleton, we'll always get the same instance.

``` php
// Share a closure
$container->share('input', function(Container $container)
{
    return new Input;
});

// Will will always get same instance
$input = $container->get('input');

// The second argument of get() can force create new instance
$newInput = $container->get('input', true);

// Use readable constant
$newInput = $container->get('input', Container::FORCE_NEW);
```

## Protect Object

Use `protect()` to prevent others override your important object.

``` php
$container->protect(
    'input',
    function(Container $container)
    {
        return new Input;
    },
    true // Shared or not
);

// We can still get this object
$input = $container->get('input');

// @Throws OutOfBoundsException
$container->set('input', $otherInput);
```

## Alias

It is convenience to set an alias to key of objects which we often use.

``` php
$container->share('system.application', $app)
    ->alias('app', 'system.application');

// Same as system.application
$app = $container->get('app');
```

So it is a good way that we can build IOC structure:

``` php
$config = array(
    'ioc.structure' => array(
        'app'     => 'system.application',
        'input'   => 'system.input',
        'session' => 'system.session'
    )
);

// Your own IOC class follows your rule
IOC::setStructure($config['ioc.structure']);
IOC::setContainer($container);

// Will get system.session from Container
$session = IOC::getSession();
```

## Create Object

Container can build an object and auto inject the needed dependency objects.

``` php
use Windwalker\IO\Input;
use Windwalker\Structure\Structure;

class MyClass
{
    public $input;
    public $config;

    public function __construct(Input $input, Structure $config)
    {
        $this->input = $input;
        $this->config = $config;
    }
}

$myObject = $container->createObject('MyClass');

$myObject->input; // Input
$myObject->config; // Structure
```

### Binding Classes

Sometimes we hope to inject particular object we want, we can bind a class as key to let Container know what you want to
instead the dependency object.

Here is a class but dependency to an abstract class, we can bind a sub class to container for use.

``` php
use Windwalker\Model\AbstractModel;
use Windwalker\Structure\Structure;

class MyClass
{
    public $model;
    public $config;

    public function __construct(AbstractModel $model, Structure $config)
    {
        $this->model = $model;
        $this->config = $config;
    }
}

class MyModel extends AbstractModel
{
}

// Bind MyModel as AbstractModel
$container->share('Windwalker\\Model\\AbstractModel', function()
{
    return new MyModel;
});

$myObject = $container->createObject('MyClass');

$myObject->model; // MyModel
```

## Extending

Container allows you to extend an object, the new instance or closure will override the original one, this is a sample:

``` php
// Create an item first
$container->share('flower', function()
{
    return new Flower;
});

$container->extend('flower', function($origin, Container $container)
{
    $origin->name = 'sakura';

    return $origin;
});

$flower = $container->get('flower');

$flower->name; // sakura
```

## Container Aware

The `ContainerAwareInterface` help us get and set Container as a system, constructor, we often use it on application or controller classes.

``` php
use Windwalker\DI\ContainerAwareInterface

class MyController implements ContainerAwareInterface
{
    protected $container;

    public function getContainer()
    {
        return $this->container;
    }

    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    public function execute()
    {
        $container = $this->getContainer();
    }
}
```

### Using Trait

In PHP 5.4, you can use `ContainerAwareTrait` to create an aware object.

``` php
class MyController implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function execute()
    {
        $container = $this->getContainer();
    }
}
```

## Service Providers

Service providers is an useful way to encapsulate logic of creating objects and services.
Just implements the `Windwalker\DI\ServiceProviderInterface`.

``` php
use Windwalker\DI\Container
use Windwalker\DI\ServiceProviderInterface

class DatabaseServiceProvider implements ServiceProviderInterface
{
    public function register(Container $container)
    {
        $container->share('db', function (Container $container)
        {
            $options = $container->get('config')->get('database');

            return DatabaseFactory::getDbo($options['driver'], $options);
        });

        // Or use callable
        $container->share('query', array($this, 'getQuery'));
    }

    public function getQuery(Container $container)
    {
        return new MysqlQueery;
    }
}

$container->registerServiceProvider(new DatabaseServiceProvider);
```
