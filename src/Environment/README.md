# Windwalker Environment

Environment package provides a set of methods help us know information of our server and user's browser.

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/environment": "~2.0"
    }
}
```

## Create An Environment Object

The default environment maintains a server object provides us some information of our server. 

``` php
$env = new Environment;

$server = $env->getServer();
```

## The Server Object

### Detect Running Environment

``` php
$server->isWeb();
$server->isCli();
```

Same as:

``` php
use Windwalker\Environment\PhpHelper;

PhpHelper:isWeb();
PhpHelper::isCli();
```

### Detect System OS

Get server OS information. The `getOS()` return first 3 letters from `getUname()`. The Uname is same as `PHP_OS`. 
List of `PHP_OS` please see: https://gist.github.com/asika32764/90e49a82c124858c9e1a 

``` php
$sever->getOS();  // WIN, UNI, LIN, DAR ... etc.
$sever->getUname(); // PHP_OS

$sever->isWin();
$sever->isLinux();
$sever->isUnix();
```

### Get System Path

``` php
$server->getWorkingDirectory();

$server->getRoot();

$server->getEntry();

$server->getServerPublicRoot();

$server->getRequestUri();
```

#### getWorkingDirectory()

If in Web environment, this method return running script directory.

If in CLI environment, this method return current terminal working dir(same as `pwd` command).

#### getRoot(`[$full = true]`)

Get running script root directory. 

Set first argument to `false`, will return relative path from DocumentRoot in Web, 
or return relative path from working dir in CLI. 

#### getEntry(`[$full = true]`)

Get running script file name.

Set first argument to `false`, will return relative path from DocumentRoot in Web, 
or return relative path from working dir in CLI.

#### getServerPublicRoot()

Return the Http server DocumentRoot, same as `$_SERVER['DOCUMENT_ROOT']`.

#### getRequestUri(`[$withParams = true]`)

Call this method will return the URI path as `$_SERVER['REQUEST_URI']` with Http queries.

```
/path/foo/?bar=baz
```

Set first argument to `false` will return request path without params, same as `$_SERVER['PHP_SELF']`.

```
/path/foo/
```

### Get Request Information

``` php
$server->getHost();
$server->getScheme();
$server->getPort();
```

## The PhpHelper

PhpHelper provides some useful methods to know about our PHP status.

### Check PHP Running Environment

``` php
PhpHelper::isWeb();
PhpHelper::isCli();
PhpHelper::isHHVM();
PhpHelper::isPHP();
PhpHelper::isEmbed();
```

### Get PHP Version

If is PHP, return `PHP_VERSION`. If is HHVM, return `HHVM_VERSION`.

``` php
PhpHelper::getVersion()
```

### Set Debug Mode

`setStrict()` will set `error_reporting()` to 32767.
 
`setMuted()` will set `error_reporting()` to 0.

``` php
PhpHelper::setStrict();
PhpHelper::setMuted();
```

### Check Extensions

``` php
PhpHelper::hasXdebug();
PhpHelper::hasPcntl();
PhpHelper::hasCurl();
PhpHelper::hasMcrypt();
```

## WebEnvironment

WebEnvironment maintains two objects, the Server and the WebClient. 

``` php
use Windwalker\Environment\Web\WebEnvironment;

$env = new WebEnvironment;

$server = $env->getServer();
$client = $env->getClient();
```

## WebClient

WebClient is a client detector help us know information about user's browser.

### Detect Browser

``` php
$browser = $client->getBrowser();

// Check is IE
$browser == WebClient::IE;
```

Available Browser Detection

- IE
- FIREFOX
- CHROME
- SAFARI
- OPERA
- ANDROID_TABLET

### Detect Browser Version

``` php
$version = $client->getBrowserVersion();

// Check version
$version >= 11;
```

### Detect Browser Engine

``` php
$engine = $client->getEngine();

// Check engine
$engine == Client::WEBKIT
```

Available Engines

- TRIDENT
- WEBKIT
- GECKO
- PRESTO
- KHTML
- AMAYA

### Detect User's OS or Device

``` php
$platform = $client->getPlatform();

// Check platform
$platform == Client::ANDROID
```

Available Platforms

- WINDOWS
- WINDOWS_PHONE
- WINDOWS_CE
- IPHONE
- IPAD
- IPOD
- MAC
- BLACKBERRY
- ANDROID
- LINUX

### Other Detection
 
``` php
$client->isRobot();
$client->isMobile();
$client->getLanguages();
$client->getEncodings();
$client->isSSLConnection();
$client->getUserAgent();
```
