# Windwalker Router

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/router": "~2.0"
    }
}
```

## Getting Started

``` php
use Windwalker\Router\Router;

$router = new Router;
```

### Add Routes

``` php
use Windwalker\Route\Route;

// Route with name
$router->addRoute(new Route('sakura', 'flower/(id)/sakura', array('_controller' => 'SakuraController')));

// Route without name
$router->addRoute(new Route(null, 'flower/(id)/sakura', array('_controller' => 'SakuraController')));
```

### Match Route

``` php
$route = $router->match('flower/12/sakura');

$variables = $route->getVariables(); // Array([_controller] => SakuraController)

// Use variables
$class = $variables['_controller'];

$controller = new $class;
```

### Add More Options to Route

Route interface: '($name, $pattern[, $variables = array()][, $allowMethods = array()][, $options = array()])'

``` php
$route = new Route(
    'name',
    'pattern/of/route/(id).(format)',

    // Default Variables
    array(
        'id'    => 1,
        'alias' => 'foo-bar-baz',
        'format' => 'html'
    ),

    // Allow methods
    array('GET', 'POST'),

    // Options
    array(
        'host'    => 'windwalker.io',
        'scheme'  => 'http', // Only http & https
        'port'    => 80,
        'sslPort' => 443,
        'requirements' => array(
            'id' => '\d+'
        ),
        'extra' => array(
            '_ctrl' => 'Controller\Class\Name',
        )
    )
);

$router->addRoute($route);

// match routes
$route = $router->match(
    'pattern/of/route/25.html',
    array(
        'host'   => $uri->getHost(),
        'scheme' => $uri->getScheme(),
        'port'   => $uri->getPort()
    )
);

$variables = $route->getVariables();

// Merge these matched variables back to http request
$_REQUEST = array_merge($_REQUEST, $variables);

// Extra is the optional variables but we won't want to merge into request
$extra = $router->getExtra();

print_r($variables);
print_r($extra);
```

The printed result:

```
Array
(
    [id] => 25
    [alias] => foo-bar-baz
    [format] => html
)

Array(
    [_ctrl] => Controller\Class\Name
)
```

### Build Route

`build()` is a method to generate route uri for view template.

``` php
$router->addRoute(new Route('sakura', 'flower/(id)/sakura', array('_controller' => 'SakuraController')));

$uri = $router->build('sakura', array('id' => 30)); // flower/30/sakura

echo '<a href="' . $uri . '">Link</a>';
```

### Quick Mapping

`addMap()` is a simple method to quick add route without complex options.

``` php
$router->addMap('flower/(id)/sakura', array('_controller' => 'SakuraController', 'id' => 1));

$variables = $router->match('flower/30/sakura');
```

## Rules

### Simple Params

``` php
new Route(null, 'flower/(id)-(alias)');
```

### Optional Params

#### Single Optional Params

``` php
new Route(null, 'flower(/id)');
```

Matched route could be:

```
flower
flower/25
```

#### Multiple Optional Params

``` php
new Route(null, 'flower(/year,month,day)');
```

Matched route could be:

```
flower
flower/2014
flower/2014/10
flower/2014/10/12
```

The matched variables will be

```
Array
(
    [year] => 2014
    [month] => 10
    [day] => 12
)
```

### Wildcards

``` php
// Match 'king/john/troilus/and/cressida'
new Route(null, 'flower/(*tags)');
```

Matched:

```
Array
(
    [tags] => Array
    (
        [0] => john
        [1] => troilus
        [2] => and
        [3] => cressida
    )
)
```

## Matchers

Windwalker Router provides some matchers to use different way to match routes.

### Sequential Matcher

Sequential Matcher use the [Sequential Search Method](http://en.wikipedia.org/wiki/Linear_search) to find route.
It is the slowest matcher but much more customizable. It is the default matcher of Windwalker Router.

``` php
use Windwalker\Router\Matcher\SequentialMatcher;

$router = new Router(array(), new SequentialMatcher);
```

### Binary Matcher

Binary Matcher use the [Binary Search Algorithm](http://en.wikipedia.org/wiki/Binary_search_algorithm) to find route.
This matcher is faster than SequentialMatcher but it will break the ordering of your routes. Binary search will re-sort all routes by pattern characters.

``` php
use Windwalker\Router\Matcher\BinaryMatcher;

$router = new Router(array(), new BinaryMatcher);
```

### Trie Matcher

Trie Matcher use the [Trie](http://en.wikipedia.org/wiki/Trie) tree to search route.
This matcher is the fastest method of Windwalker Router, but the limit is that it need to use an simpler route pattern 
which is not as flexible as the other two matchers.

``` php
use Windwalker\Router\Matcher\TrieMatcher;

$router = new Router(array(), new TrieMatcher);
```

### Rules of TrieMatcher

#### Simple Params

only match when the uri segments all exists. If you want to use optional segments, you must add two or more patterns.

```
flower
flower/:id
flower/:id/:alias
```

#### Wildcards

This pattern will convert segments after `flower/` this to an array which named `tags`:

```
flower/*tags
```

## Single Action Router

Single action router is a simple router that extends Windwalker Router. It just return a string if matched.

This is a single action controller example:

``` php
$router->addMap('flower/(id)/(alias)', 'FlowerController');

$controller = $router->match('flower/25/sakura');

$_REQUEST = array_merge($_REQUEST, $router->getVariables());

echo (new $controller)->execute();
```

Or a controller with action name:

``` php
$router->addMap('flower/(id)/(alias)', 'FlowerController::indexAction');

$matched = $router->match('flower/25/sakura');

$_REQUEST = array_merge($_REQUEST, $router->getVariables());

list($controller, $action) = explode('::', $matched);

echo (new $controller)->$action();
```

## RestRouter

RestRouter is a simple router extends to SingleActionRouter, it can add some suffix of different methods.

``` php
$router->addMap('flower/(id)/(alias)', 'Flower\\Controller\\');

$controller = $router->match('flower/25/sakura', 'POST'); // Get Flower\\Controller\\Create

(new $controller)->execute();
```

Default Suffix mapping is:

```
'GET'     => 'Get',
'POST'    => 'Create',
'PUT'     => 'Update',
'PATCH'   => 'Update',
'DELETE'  => 'Delete',
'HEAD'    => 'Head',
'OPTIONS' => 'Options'
```

You can override it:

``` php
$router->setHttpMethodSuffix('POST', 'SaveController');
```

## Exception

If Router not matched anything, it throws `Windwalker\Router\Exception\RouteNotFoundException`.

``` php
try
{
    $route = $router->match('flower/25');
}
catch (RouteNotFoundException $e)
{
    Application::close('Page not found', 404);
    
    exit();
}
catch (\Exception $e)
{
    // Do other actions...
}
```
