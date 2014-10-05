# The Windwalker Console Package

The Windwalker Console package provide an elegant and nested command structure for your cli application.

## The Command Calling Flow

If we type:

``` bash
$ php cli/console.php command1 command2 command3 -a -b -cd --help
```

The command calling flow is:

```
rootCommand (console application)
    ->configure
    ->execute

    command1
        ->configure
        ->execute

        commend2
            ->configure
            ->execute

            commend3
                ->configure
                ->execute

            return exitCode

        return exitCode

    return exitCode

return exitCode
```

## Initialising Console

Console is extends from [AbstractCliApplication](https://github.com/ventoviro/windwalker/tree/staging/src/Application#command-line-applications), help us create a command line application.

An example console application skeleton in `cli/console.php` file:

``` php
<?php

// Load the Composer autoloader
include __DIR__ . '/../vendor/autoload.php';

use Windwalker\Console\Console;

$console = new Console;

$console->execute();
```

The `execute()` will find commands matched the cli input argument. If there are not any command registered,
console will execute the `Default Command`.

### Default Command

`RootCommand` is a command object extends from base `Command` object. It provides some useful helper,
we can list all commands by typing:

``` bash
$ php cli/app.php
```

By default, the output is:

``` bash
windwalker! Console - version: 1.0
------------------------------------------------------------

[console.php Help]

The default application command

Usage:
  console.php <command> [option]


Options:

  -h / --help
      Display this help message.

  -q / --quiet
      Do not output any message.

  -v / --verbose
      Increase the verbosity of messages.

  --no-ansi
      Suppress ANSI colors on unsupported terminals.


Available commands:

  help    List all arguments and show usage & manual.


Welcome to windwalker! Console.
```

### Set Executing Code for RootCommand

We can add closure to every commands, that this command will execute this function first. Use `setHandler()` on
`$console`, the Console will auto pass the code to RootCommand:

``` php
<?php
// cli/console.php

// ...

$console->setHandler(
	function($command)
	{
		$command->out('This is default command.');

		return 0; // Return exit code.
	}
);

$console->execute();
```

This will do same action:

``` php
<?php
// cli/console.php

// ...

$console
    ->getRootCommand() // Return the RootCommand
    ->setHandler(
        function($command)
        {
            $command->out('This is default command.');

            return 0; // Return exit code.
        }
    );

$console->execute();
```

Retype `$ php cli/console.php` and output:

```
This is default command.
```

If we want to get help again, just type:

``` bash
$ cli/console.php help
```

OR

``` bash
$ cli/console.php --help
```

> Note: Command only return integer between 0 and 255, `0` means success, while others means failure or other status.
> The exit code of Unix/Linux meaning please see: [Exit Codes Meanings](http://www.tldp.org/LDP/abs/html/exitcodes.html)

## Add Help Message to Console

Console includes some help message like: `name`, `version`, `description`, `usage` and `help`.

If we add this messages to Console:

``` php
// cli/console.php

// ...

$console = with(new Console)
	->setName('Example Console')
	->setVersion('1.2.3')
	->setUsage('console.php <arguments> [-h|--help] [-q|--quiet]')
	->setDescription('Hello World')
	->setHelp(
<<<HELP
Hello, this is an example console, if you want to do something, see above:

$ foo bar -h => foo bar --help

---------

$ foo bar yoo -q => foo bar yoo --quiet
HELP
	);

// ...
```

The help will show:

![help](http://cl.ly/SPTF/cli-help.jpg)

## Add First Level Command to Console

Now, we just use the default command. But there are not first level arguments we can call except `HelpCommand`.

We can add a command by this code:

``` php
<?php
// cli/console.php

$console->register('foo')
	->setDescription('This is first level foo command.')
	->setUsage('foo command [--option]')
	->setHelp('foo help')
	->setHandler(
		function($command)
		{
			$command->out('This is Foo Command executing code.');
		}
	);
```

Then we type:

``` bash
$ cli/console.php foo
```

We will get:

```
This is Foo Command executing code.
```

If we type help:

```
$ cli/console.php -h
```

The foo command description has auto added to default command arguments list.

![foo-help](http://cl.ly/SOfp/p2013-11-10-3.jpg)

## Using My Command Object

We can create our own command object instead setting it in runtime.

This is an example FooCommand:

``` php
<?php
// src/Myapp/Command/FooCommand.php

namespace Myapp\Command;

use Windwalker\Console\Command\Command;

class FooCommand extends Command
{
    protected $name = 'foo';

    public function configure()
    {
        $this->setDescription('This is first level foo command.')
            ->setUsage('foo command [--option]')
            ->setHelp('foo help');
    }

    public function doExecute()
    {
        $this->out('This is Foo Command executing code.');
    }
}

```

And we register it in Console:

``` php
<?php
// cli/console.php

$console->addCommand(new FooCommand);
```

## Using Arguments and Options

We can use this code to get arguments and options

``` php
public function configure()
{
    $this->setDescription('This is first level foo command.')
        ->setUsage('foo command [--option]')
        ->addOption(
            's', // option name
            0,   // default value
            'Add this option can make output lower case.', // option description
            Option::IS_GLOBAL // sub command will extends this global option
        )
        ->addOption(
            array('y', 'yell', 'Y'), // First element will be option name, others will be alias
            0,
            'Yell will make output upper case.',
            Option::IS_PRIVATE // sub command will not extends private option, this is default value, we don't need set private manually.
        )
        ->setHelp('foo help');
}

public function doExecute()
{
    if (empty($this->input->args[0]))
    {
        $this->out('Please enter a name: ');
        $name = fread(STDIN, 8792);
    }
    else
    {
        $name = $this->input->args[0];
    }

    $reply = 'Hello ' . $name;

    if ($this->getOption('y'))
    {
        $reply = strtoupper($reply);
    }

    if ($this->getOption('q'))
    {
        $reply = strtolower($reply);
    }

    $this->out($reply);
}
```

If we type:

``` bash
$ php cli/console.php foo Asika --yell
```

OR

``` bash
$ php cli/console.php foo Asika -y
```

The `getOption()` method will auto detect option aliases, then we can get:

```
HELLO: ASIKA
```

> Note: We have to `addOption()` first, then the `getOption('x')` is able to get the input option which we wanted.
>
> If we don't do this first, we have to use `$this->input->get('x')` to get option value,
> but this way do not support option aliases.

## Add Second Level Commands and more...

If we want to add several commands after FooCommand, we can use `addCommand()` method. Now we add two `bar` and `yoo`
command to `FooCommand`.

### Adding command in runtime.

We use `addCommand()` to add commands.

If a command has one or more sub commands, the arguments means to call sub command which name equals to first argument.

If a command has on sub commands, Command object will run executing code if set, or run `doExecute()` if executing code not set. Then the remaining arguments will save in `$this->input->args`.

``` php
<?php
// src/Myapp/Command/FooCommand.php

use Windwalker\Console\Option\Option;

//...

    public function configure()
    {
        $this->setDescription('This is first level foo command.')
            ->setUsage('foo command [--option]')
            ->setHelp('foo help')
            ->addCommand(
                'bar',
                'Bar description.'
            )
            ->addCommand(
                'yoo',
                'Yoo description.',
                array(
                    new Option(array('y', 'yell'), 0),
                    new Option('s', 0, 'desc', Option::IS_GLOBAL)
                )
            );
    }
```

### Adding command by classes

We declare `BarCommand` and `YooCommand` class first.

``` php
<?php
// src/Myapp/Command/Foo/BarCommand.php

namespace Myapp\Command\Foo;

use Windwalker\Console\Command\Command;

class BarCommand extends Command
{
    protected $name = 'bar';

    public function configure()
    {
        $this->setDescription('This is second level bar command.')
            ->setUsage('bar command [--option]')
            ->setHelp('bar help')
            ->addOption(new Option(array('y', 'yell'), 0))
            ->addOption(new Option('s', 0, 'desc', Option::IS_GLOBAL));
    }

    public function doExecute()
    {
        $this->out('This is Bar Command executing code.');
    }
}
```

Then register them to `FooCommand`:

``` php
<?php
// src/Myapp/Command/FooCommand.php

use Myapp\Command\Foo\BarCommand;
use Myapp\Command\Foo\YooCommand;

//...

    public function configure()
    {
        $this->setDescription('This is first level foo command.')
            ->setUsage('foo command [--option]')
            ->setHelp('foo help')
            ->addCommand(new BarCommand)
            ->addCommand(new YooCommand);
    }
```

OK, typing:

``` bash
$ cli/console.php foo bar
```

We get:

```
This is Bar Command executing code.
```

## HelpCommand

`HelpCommand` will auto generate help list for us.

When we use `addCommand()`, `addOption()` and set some description or other information to these objects, they will save all information in it. Then when we type `$ cli/console.php help somethine` or `$ cli/console.php somethine --help`, The HelpCommand will return the help message to us.

Every command has these information, you can use setter and getter to access them:

* `Name` (Command name. The name of RootCommand is file name.)
* `Description` (Command description, will show after title in help output.)
* `Usage` (Will show in help output of current command.)
* `Help` (Will show in the help output bottom as a manual of current command)

The Console information:

* `Name` (Name of this application, will show as title in help output.)
* `Description` (RootCommand description.)
* `Usage` (RootCommand usage.)
* `Help` (RootCommand help)

### Use Your Own Descriptor

If you want to override the `Descriptor` for your apps, you can do this:

``` php
<?php
use Myapp\Command\Descriptor\XmlDescriptorHelper;
use Myapp\Command\Descriptor\XmlCommandDescriptor;
use Myapp\Command\Descriptor\XmlOptionDescriptor;

// ...

$descriptor = new new XmlDescriptorHelper(
    new XmlCommandDescriptor,
    new XmlOptionDescriptor
);

$console->getRootCommand()
    ->getChild('help')
    ->setDescriptor($descriptor);

// ...
```

## Use Command Without Console

We can using `Command` without `Console` or `CliApplicaion`, please see [Command README](Command).


## Installation via Composer

Add `"windwalker/application": "~2.0"` to the require block in your composer.json,
make sure you have `"minimum-stability": "dev"` and then run composer install.

``` json
{
    "require": {
        "asika/windwalker-console": "~2.0"
    },
    "minimum-stability": "dev"
}
```

Alternatively, you can simply run the following from the command line:

```
composer init --stability="dev"
composer require asika/windwalker-console "~2.0"
```
