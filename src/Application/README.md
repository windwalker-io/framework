# Windwalker Application

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/application": "~2.0"
    }
}
```

## Create An Application

``` php
use Windwalker\Application\AbstractApplication;
use Windwalker\IO\Input;
use Windwalker\Structure\Structure;

class MyApplication extends AbstractApplication
{
    protected function init()
    {
        // Do stuff.
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

        $this->setBody($output);

        return true;
    }
}

$config = new Structure(array());

$app = new MyApplication(new Input, $config);

$app->execute();
```

## WebApplication

`AbstractWebApplication` add `WebEnvironment` and `Output` object to itself that help us handle HTTP request and output.

### Environment

Use `WebClient` to get information of browser or web client.

``` php
$this->environment->client->getBrowser(); // Get browser name
```

Use `Server` to get server information.

``` php
$this->environment->server->isUnix();
```

See: [Environment Package](https://github.com/ventoviro/windwalker-environment)

### Output

All HTTP output can use `Output` object to operate.

``` php
$this->output->setHeader('status', 200); // HTTP Status

$this->output->setBody($data);

$this->output->respond(); // Send all data to client
```

## Cli Application

This is a example of a simple cli application.

``` php
use Windwalker\Application\AbstractCliApplication;

class MyCliApp extends AbstractCliApplication
{
    public function doExecute()
    {
        // Get options
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

See: [Windwalker IO](https://github.com/ventoviro/windwalker-io)
