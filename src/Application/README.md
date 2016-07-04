# Windwalker Application

Windwalker application is a kernel as main entry of your system.

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/application": "~3.0"
    }
}
```

## Create An Application

Create application and extends the `doExecute()` method to something.

``` php
use Windwalker\Application\AbstractApplication;
use Windwalker\IO\Input;
use Windwalker\Structure\Structure;

class MyApplication extends AbstractApplication
{
    protected function init()
    {
        // Do stuff.

        // Get config
        $this->get('foo'); // bar
    }

    public function doExecute()
    {
        try
        {
            // Some code here...
        }
        catch (\Exception $e)
        {
            Error::renderErrorPage();
        }

        return true;
    }
}

$app = new MyApplication(new Structure(array('foo' => 'bar')));

$app->execute();
```

Config is `Structure` object, see [Windwalker Structure](https://github.com/ventoviro/windwalker-structure)

## WebApplication

`AbstractWebApplication` contains `WebEnvironment` and `WenHttpServer` object that help us handle HTTP request and output.

### WebEnvironment

Use `WebEnvironment` to get information of browser or server.

``` php
$this->environment->browser->getBrowser(); // Get browser name
```

Use `Platform` to get server information.

``` php
$this->environment->platform->isUnix();
```

See: [Environment Package](https://github.com/ventoviro/windwalker-environment)

### PSR7 Handler

`dispatch()` is a standard PSR7 handler so we can write our logic here, just return Response object and the `WebHttpServer`
object which in Application will render it to client.

``` php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Windwalker\Application\AbstractWebApplication;

class MyHttpKernel extends AbstractWebApplication
{
	public function dispatch(Request $request, Response $response, $next = null)
	{
		// Get request query
		$query = $request->getQueryParams();

		// Get Psr Uri
		$uri = $request->getUri();

		// Write body
		$response->getBody()->write('<h1>Hello World~~~!</h1>');

		return $response;
	}
}

$app = new MyHttpKernel;

$app->execute();
```

Result:

``` html
<h1>Hello World~~~!</h1>
```

### Error Handler

Set error handler as final handler so we can use it in `dispatch()`.

``` php
class MyHttpKernel extends AbstractWebApplication
{
	public function dispatch(Request $request, Response $response, $next = null)
	{
		try
		{
			throw new \Exception('Whoops~', 500);
		}
		catch (\Exception $e)
		{
			return $next($e, $request, $response);
		}

		return $response;
	}
}

$app = new MyHttpKernel;

$app->setFinalHandler(function (Exception $e, Request $request, Response $response)
{
    $response->getBody()->write(sprintf('<h1>Error %s. Message: %s</h1>', $e->getCode(), $e->getMessage()));
});

$app->execute();
```

Result:

``` html
<h1>Error 500. Message: Whoops~</h1>
```

See [Windwalker Http Package](https://github.com/ventoviro/windwalker-http)

## Cli Application

This is a example of a simple cli application.

``` php
// app.php

use Windwalker\Application\AbstractCliApplication;

class MyCliApp extends AbstractCliApplication
{
    public function doExecute()
    {
        // Get options (-h)
        $help = $this->io->get('h');

        if ($help)
        {
            $msg = <<<MSG
Help message: version 1.0
------------------------------------
myapp.php <command> [-options]

  foo    Description of this command.
  bar    Description of this command.
  help   Description of this command.
MSG;

            $this->io->out($msg);
            
            $this->close();
        }

        // Get arguments
        $arg = $this->getArgument(0);

        // Do some stuff...

        return 0; // Exit code 0 means success
    }
}

$app = new MyCliApp;

$app->execute();
```

Now we can access this app by PHP CLI:

``` bash
php app.php arg1 arg2 -h --option --foo bar --n=a
```

See: [Windwalker IO](https://github.com/ventoviro/windwalker-io)
