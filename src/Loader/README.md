# Windwalker Loader

Windwalker Loader is a simple, easy using class loader, support PSR-0, PSR-4 and class mapping autoload.

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/loader": "~3.0"
    }
}
```

## Usage

``` php
use Windwalker\Loader\ClassLoader;

$loader = new ClassLoader;

// Register autoload first
$loader->register();

$loader->addPsr0('Windwalker', __DIR__ . '/../src');

$loader->addPsr4('Windwalker\\Core\\', __DIR__ . '/core/src');

$loader->addMap('Windwalker\\Cache\\Cache', __DIR__ . '/../src/Cache/Cache.php');

// Just use your class, it will autoload
$cache = new \Windwalker\Cache\Cache;
```

## Autoload Standard

- [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md)
- [PSR-4](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-4-autoloader.md)
