# Windwalker Session

Windwalker Session package provides a simple interface to manage session data.

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/session": "~2.0"
    }
}
```

## Getting Started

``` php
use Windwalker\Session\Session;

$session = new Session;

$session->start();

$session->set('flower', 'sakura');

$data = $session->get('flower', 'default');

$session->exists('animal'); // bool
```

### Set Expire Time

```php
$session->setOption('expire_time', 20); // Minutes

$session->start();
```

### Destroy Session

``` php
$session->destroy();

// Restart
$session->start();
```

### Fork Session

Fork session to generate a new id.

``` php
$session->fork();
```

## Session Bags

Session bag is a data storage to store data, we can add many bags to Session object and access them.

### Use Default Bag

Get Default Bag

``` php
$session->getBag('default');
```

Get data from default bag.

``` php
$session->get('foo');

// OR
$session->getBag('default')->get('foo');
```

### Use Custom Bags

``` php
use Windwalker\Session\Bag\SessionBag;

$session->setBag('mybag', new SessionBag);

// Get data
$myBag = $session->getBag('mybag');

$myBag->set('foo', 'bar');
$myBag->get('foo', 'default');
```

We can use Namespace to get data from bags

``` php
// Get form default bag
$session->get('foo', 'default', 'mybag');

// Get from mybag
$session->get('foo', 'default', 'mybag');

// Set to mybag
$session->set('foo', 'bar', 'mybag');
```

## Flash

Flash bag is a data temporary storage, if we take data out, the bag will be clear.

``` php
$session->addFlash('Save success.', 'info');
$session->addFlash('Login Fail.', 'error');

// Take all messages and clear
$allMessages = $session->getFlashes();

// Peek messages but don't clear
$session->getFlashBag()->all();
``` 

### Auto Expired Flash Bag

We can make all flash data clear when every page loaded, use AutoExpiredFlashBag instead FlashBag.

``` php
$session = new Session(null, null, new AutoExpiredFlashBag);
```

## Handlers

Windwalker Session provides many handlers to storage session.

``` php
use Windwalker\Session\Handler\MemcachedHandler;

$session = new Session(new MemcachedHandler);
```

### Available Handlers

- ApcHandler
- DatabaseHandler
- MemcacheHandler
- MemcachedHandler
- NativeHandler
- WincacheHandler
- XcacheHandler










