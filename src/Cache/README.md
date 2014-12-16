# Windwalker Cache

Windwalker Cache package provides an simple interface to easily store and fetch cache files.  

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/cache": "~2.0"
    }
}
```

## Getting Started

Create a cache object and store data.

``` php
use Windwalker\Cache\Cache;

$data = array('sakura');

$cache = new Cache;

$cache->set('flower', $data);
```

Then we can get this data by same key.

``` php
$data = $cache->get('flower');
```

### Auto Fetch Data By Closure

Using call method to auto detect is cache exists or not. 

``` php
$data = $cache->call('flower', function()
{
    return array('sakura');
});
```

It is same as this code:

``` php
if (!$cache->has('flower'))
{
    $cache->set('flower', array('sakura'));
}

$data = $cache->get('flower');
```

### RuntimeStorage

The default cache storage is `RuntimeStorage`, it means our data only keep in runtime but will not save as files.

## Using FileStorage

Create a cache with `FileStorage` and set a path to store files.

``` php
use Windwalker\Cache\Cache;
use Windwalker\Cache\Storage\FileStorage;

$path = 'your/cache/path';

$cache = new Cache(new FileStorage($path));

$cache->set('flower', array('sakura'));
```

The file will store at `your/cache/path/~5a46b8253d07320a14cace9b4dcbf80f93dcef04.data`, and the data will be serialized string.

```
a:1:{i:0;s:6:"sakura";}
```

### Add Group

``` php
$path = 'your/cache/path';

$cache = new Cache(new FileStorage($path, 'mygroup'));

$cache->set('flower', array('sakura'));
```

The file wil store at `your/cache/path/mygroup/~5a46b8253d07320a14cace9b4dcbf80f93dcef04.data` that for organize your cache folder.

### Deny Access

If your cache folder are exposure on web environment, we have to make our cache files unable to access. The argument 3 
 of `FileStorage` is use to deny access.
  
``` php
$path = 'your/cache/path';

$cache = new Cache(new FileStorage($path, 'mygroup', true));

$cache->set('flower', array('sakura'));
```

The stored file will be a PHP file with code to deny access:

`your/cache/path/mygroup/~5a46b8253d07320a14cace9b4dcbf80f93dcef04.php`

``` php
<?php die("Access Deny"); ?>a:1:{i:0;s:6:"sakura";}
```

## Available Storage

- RuntimeStorage
- FileStorage
- RawFileStorage
- MemcachedStorage
- RedisStorage
- WincacheStorage
- XcacheStorage
- NullStorage

## Storage Format

The default data handler will make our data be serialized string, if you want to use other format, just change `DataHandler`
at second argument of Cache object.

``` php
use Windwalker\Cache\Cache;
use Windwalker\Cache\Storage\FileStorage;
use Windwalker\Cache\DataHandler\JsonHandler;

$cache = new Cache(new FileStorage(__DIR__ . '/cache'), new JsonHandler);

$cache->set('flower', array('flower' => 'sakura'));
```

The stored cache file is:

```
{"flower":"sakura"}
```

### Full Page Cache

Sometimes we want to store whole html as static page cache. `StringHandler`  help us save raw string:
 
``` php
use Windwalker\Cache\Cache;
use Windwalker\Cache\Storage\RawFileStorage;

$cache = new Cache(new FileStorage($path), new StringHandler);

$url = 'http://mysite.com/foo/bar/baz';

if ($cache->has($url))
{
    $html = $cache->get($url);
    
    exit();
}

$view = new View;

$html = $view->render();

$cache->set($url, $html);

echo $html;
```

### Supported Handlers

- SerializeHandler
- JsonHandler
- StringHandler

## TODO

Waiting for [PSR-6](https://github.com/php-fig/fig-standards/blob/master/proposed/cache.md) Compatible and will rewrite for it. 
