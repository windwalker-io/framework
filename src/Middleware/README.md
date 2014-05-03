# Windwalker Middleware

Windwalker Middleware is a simple & elegant PHP Middleware library help you integrating middleware pattern in your project.

## Installation via Composer

Add "asika/windwalker-middleware": "1.0.*" to the require block in your composer.json.

``` json
{
    "require": {
        "asika/windwalker-middleware": "~1.0"
    }
}
```

## Getting Started

### Basic Example

This is a simple way using middleware to wrap your logic.

``` php
use Windwalker\Middleware\CallbackMiddleware;
use Windwalker\Middleware\Middleware;

class TestA extends Middleware
{
	/**
	 * call
	 *
	 * @return  mixed
	 */
	public function call()
	{
		echo ">>> AAAA\n";

		$this->next->call();

		echo "<<< AAAA\n";
	}
}

class TestB extends Middleware
{
	/**
	 * call
	 *
	 * @return  mixed
	 */
	public function call()
	{
		echo ">>> BBBB\n";

		$this->next->call();

		echo "<<< BBBB\n";
	}
}

$a = new TestA;

$a->setNext(new TestB);

$a->call();
```

The result should be:

```
>>> AAAA
>>> BBBB
<<< BBBB
<<< AAAA
```

### Callback Middleware

If you don't want to create a class, you want to set a middleware in runtime, using `CallbackMiddleware`

``` php
$a = new TestA;
$b = new TestB;

$a->setNext($b);
$b->setNext(new CallbackMiddleware(
	function($next)
	{
		echo ">>>CCCC\n";
		echo "<<<CCCC\n";
	}
));

$a->call();
```

The result should be:

```
>>> AAAA
>>> BBBB
>>> CCCC
<<< CCCC
<<< BBBB
<<< AAAA
```

The `CallbackMiddleware` support second argument as next in constructor:

``` php
$ware = new CallbackMiddleware(
	function($next)
	{
		echo ">>>CCCC\n";

		$next->call();

		echo "<<<CCCC\n";
	},
	new NextMiddleware
)
```

## The Blackhole

If a middleware call next, we have to make sure there are a next middleware exists, or we will return error.

``` php
class TestB extends Middleware
{
	/**
	 * call
	 *
	 * @return  mixed
	 */
	public function call()
	{
		echo ">>> BBBB\n";

		$this->next->call();

		echo "<<< BBBB\n";
	}
}

$b = new TestB;

$b->call();

// Error, next not exists.
```

But yes we can set a blackhole middleware in the last element, that will do nothing when previous class call it, using `EndMiddleware`:

``` php
$b = new TestB;

$b->setNext(new EndMiddleware);

$b->call();
```

The result still like below:

```
>>> BBBB
<<< BBBB
```

## Chaining Middleware

We can using `ChainBuilder` to chaining multiple middlewares.

``` php
use Windwalker\Middleware\Chain\ChainBuilder;

$chain = new ChainBuilder;

$chain
    ->add('TestA')
    ->add(new TestB)
    ->add(function($next)
    {
        echo ">>>CCCC\n";
        echo "<<<CCCC\n";
    });

$chain->call();
```

The result still:

```
>>> AAAA
>>> BBBB
>>> CCCC
<<< CCCC
<<< BBBB
<<< AAAA
```

## Todo

Writing UnitTest...
