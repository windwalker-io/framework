# Windwalker Cache

Windwalker Cache package provides an simple interface to easily store and fetch cache files.  

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/cache": "~3.0"
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
$data = $cache->get('flower'); // return Array('sakura')
```

### Auto Fetch Data By Callback

Using call method to auto detect is cache exists or not. 

``` php
$data = $cache->call('flower', function()
{
    return array('sakura');
});
```

It is same as this code:

``` php
if (!$cache->exists('flower'))
{
    $cache->set('flower', array('sakura'));
}

$data = $cache->get('flower');
```

## Storage

Set storage to Cache so we can use different storage engine to save cache data.

``` php
use Windwalker\Cache\Cache;
use Windwalker\Cache\Storage\ArrayStorage;

$cache = new Cache(new ArrayStorage);
```

### ArrayStorage and RuntimeArrayStorage

This is default storage, which will store data in itself and will not depends on any outside storage engine.

The `RuntimeArrayStorage` use static property to storage data, which means all data will live in current runtime
no matter how many times you create it.

### FileStorage

Create a cache with `FileStorage` and set a path to store files.

``` php
use Windwalker\Cache\Cache;
use Windwalker\Cache\Storage\FileStorage;

$path = '/your/cache/path';

$cache = new Cache(new FileStorage($path));

$cache->set('flower', array('sakura'));
```

The file will store at `/your/cache/path/~5a46b8253d07320a14cace9b4dcbf80f93dcef04.data`, and the data will be serialized string.

```
a:1:{i:0;s:6:"sakura";}
```

#### File Group

Group is a subfolder of your storage path.

``` php
$path = '/your/cache/path';

$cache = new Cache(new FileStorage($path, 'mygroup'));

$cache->set('flower', array('sakura'));
```

The file wil store at `/your/cache/path/mygroup/~5a46b8253d07320a14cace9b4dcbf80f93dcef04.data` that for organize your cache folder.

#### PHP File Format and Deny Access

If your cache folder are exposure on web environment, we have to make our cache files unable to access. The argument 3 
 of `FileStorage` is use to deny access.
  
``` php
$path = '/your/cache/path';

$cache = new Cache(new FileStorage($path, 'mygroup', true));

$cache->set('flower', array('sakura'));
```

The stored file will be a PHP file with code to deny access:

`/your/cache/path/mygroup/~5a46b8253d07320a14cace9b4dcbf80f93dcef04.php`

``` php
<?php die("Access Deny"); ?>a:1:{i:0;s:6:"sakura";}
```

## Available Storage

- ArrayStorage
- RuntimeArrayStorage
- FileStorage
- MemcachedStorage
- RedisStorage
- WincacheStorage
- XcacheStorage
- NullStorage

## Serializer

The default `PhpSerializer` will make our data be php serialized string, if you want to use other format,
just change serializer at second argument of Cache object.

``` php
use Windwalker\Cache\Cache;
use Windwalker\Cache\Serializer\JsonSerializer;
use Windwalker\Cache\Storage\FileStorage;

$cache = new Cache(new FileStorage(__DIR__ . '/cache'), new JsonSerializer);

$cache->set('flower', array('flower' => 'sakura'));
```

The stored cache file is:

```
{"flower":"sakura"}
```

### Full Page Cache

Sometimes we may need to store whole html as static page cache. `StringSerializer` or `RawSerializer` helps us save raw data as string:
 
``` php
use Windwalker\Cache\Cache;
use Windwalker\Cache\Serializer\StringSerializer;
use Windwalker\Cache\Storage\FileStorage;

$cache = new Cache(new FileStorage($path), new StringSerializer);

$url = 'http://mysite.com/foo/bar/baz';

if ($cache->exists($url))
{
    echo $cache->get($url);
    
    exit();
}

$html = View::render('html.layout');

$cache->set($url, $html);

echo $html;
```

### PhpFileSerializer

This serializer can save array data as a php file, will be useful when we need to cache config data.

``` php
use Windwalker\Cache\Cache;
use Windwalker\Cache\Serializer\PhpSerializer;
use Windwalker\Cache\Storage\FileStorage;

$cache = new Cache(new FileStorage($path), new PhpSerializer);

$config = array('foo' => 'bar');

$cache->set('config.name', $config);

$cache->get('config.name'); // Array( [foo] => bar )
```

The cache file will be:

``` php
<?php

return array (
  'foo' => 'bar',
);
```

### Supported Serializer

- PhpSerializer
- PhpFileSerializer
- JsonSerializer
- StringSerializer
- RawSerializer

## PSR6 Cache Interface

Windwalker Cache Storage are all follows [PSR6](http://www.php-fig.org/psr/psr-6/), so you can use other libraries'
CacheItemPool object as storage, you can also directly use Storage object.

``` php
use Windwalker\Cache\Item\CacheItem;
use Windwalker\Cache\Storage\FileStorage;

$cachePool = new FileStorage(__DIR__ . '/cache');

$cachePool->save(new CacheItem('foo', 'Bar', 150));

// OR save differed
$cachePool->saveDeferred(new CacheItem('baz', 'Yoo', 150));
$cachePool->commit();
```
