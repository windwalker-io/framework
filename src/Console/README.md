# Windwalker Console

The Windwalker Console package provides an elegant and nested command structure for your cli application.

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/console": "~2.0"
    }
}
```

## The Nested Command Structure

```
          Console Application (RootCommand)
                         |
              ----------------------
              |                    |
          CommandA              CommandB
              |                    |
        ------------          ------------
        |          |          |          |
    CommandC   CommandD    CommandE   CommandF
```

If we type:

``` bash
$ php cli/console.php commandA commandC foo bar -a -bc -d=e --flower=sakura
```

Then we will been direct to `CommandC` class, and the following `foo bar` will be arguments.

``` php
class CommandC extend AbstractCommand
{
    public function execute()
    {
        $arg1 = $this->getArgument(0); // foo
        $arg2 = $this->getArgument(0); // bar
        
        $opt = $this->io->get('d') // e
        $opt = $this->io->get('flower') // sakura
    }
}
```

## Initialising Console

Console is the main application help us create a command line program.

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

### Default RootCommand

`RootCommand` is a command object extends from base `Command`. It provides some useful helpers,
we can list all commands by typing:

``` bash
$ php cli/app.php
```

By default, the output is:

``` bash
Windwalker Console - version: 1.0
------------------------------------------------------------

[console.php Help]

The default application command

Usage:
  console.php <command> [option]


Options:

  -h | --help       Display this help message.
  -q | --quiet      Do not output any message.
  -v | --verbose    Increase the verbosity of messages.
  --ansi            Set 'off' to suppress ANSI colors on unsupported terminals.


Welcome to Windwalker Console.
```

### Set Handler for RootCommand

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

This code will do same action:

``` php
<?php
// cli/console.php

// ...

$console->getRootCommand()
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

# OR

$ cli/console.php --help
```

> Note: Command only return integer between 0 and 255, `0` means success, while others means failure or other status.
  The exit code of Unix/Linux meaning please see: [Exit Codes Meanings](http://www.tldp.org/LDP/abs/html/exitcodes.html)

## Add Help Message to Console

Console includes some help message like: `name`, `version`, `description`, `usage` and `help`.

If we add this messages to Console:

``` php
// cli/console.php

// ...

$console = new \Windwalker\Console\Console;

$console->setName('Example Console')
	->setVersion('1.2.3')
	->setUsage('console.php <commands> <arguments> [-h|--help] [-q|--quiet]')
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

![console example](https://cloud.githubusercontent.com/assets/1639206/4477512/bae50278-497e-11e4-92a6-0f998461442b.png)

## Add First Level Command to Console

Now, we just use the default root command. But there are no first level command are available to call except `HelpCommand`.

We can add a new command by this code:

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

## Declaring Command Class

We can create our own command object instead setting it in runtime.

This is an example FooCommand declaration:

``` php
<?php
// src/Myapp/Command/FooCommand.php

namespace Myapp\Command;

use Windwalker\Console\Command\Command;

class FooCommand extends Command
{
    protected $name  = 'foo';
    protected $usage = 'foo command [--option]';
    protected $help  = 'foo help';
    protected $description = 'This is first level foo command.';

    public function initialise()
    {
        // We can also set help message in initialise method 
        $this->setDescription('This is first level foo command.')
            ->setUsage('foo command [--option]')
            ->setHelp('foo help');
    }

    public function doExecute()
    {
        $this->out('This is Foo Command executing.');
    }
}

```

Then we register it in Console:

``` php
<?php
// cli/console.php

$console->addCommand(new FooCommand);
```

## Get Arguments and Options

We can use this code to get arguments and options, setting them in `FooCommand`.

``` php
// src/Myapp/Command/FooCommand.php

public function initialise()
{
    // Define options first that we can set option aliases.
    $this->addOption(array('y', 'yell')) // First element `y` will be option name, others will be alias
        ->alias('Y') // Add a new alias
        ->defaultValue(0)
        ->description('Yell will make output upper case.');
        
    // Global options will pass to every child.
    $this->addGlobalOption('s')
        ->defaultValue(0)
        ->description('Yell will make output upper case.');
}

public function doExecute()
{
    $name = #this->getArgument(0);

    if (!$name)
    {
        $this->io->in('Please enter a name: ');
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

# OR

$ php cli/console.php foo Asika -y
```

The `getOption()` method will auto detect option aliases, then we can get:

```
HELLO: ASIKA
```

> Note: We have to use `addOption()` to define options first, then the `$this->getOption('x')` will be able to 
get the input option which we want. If we didn't do this, we have to use `$this->io->get('x')` 
to get option value, but this way do not support option aliases.

## Add Second Level Commands and more...

Now, FooCommand is the first level commands in our command tree, if we want to add several commands under FooCommand, 
we can use `addCommand()` method. Now we add two `bar` and `yoo` command under `FooCommand`.

### Adding command in runtime.

We can use `addCommand()` to add a command as other commands' child.

If a command has one or more children, the arguments means to call children which the name equals to this argument.

If a command has no child, Command object will run handler closure if has set, or run `doExecute()` if handler not set. 
Then the remaining arguments will be able to get by `$this->getArgument({offset})`.

``` php
<?php
// src/Myapp/Command/FooCommand.php

use Windwalker\Console\Option\Option;

//...

    public function initialise()
    {
        $this->addCommand('bar')
            ->description('Bar description.');
            
        $this->addCommand('yoo')
            ->description('Yoo description.')
            ->addOption(new Option(array('y', 'yell'), 0))
            ->addGlobalOption(new Option('s', 0, 'desc'));
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
    protected $usage = 'bar command [--option]';
    protected $help  = 'bar help';
    protected $description = 'This is second level bar command.';

    public function initialise()
    {
        $this->addOption(new Option(array('y', 'yell'), 0))
            ->addGlobalOption(new Option('s', 0, 'desc'));
    }

    public function doExecute()
    {
        $this->out('This is Bar Command executing.');
        
        $arg1 = $this->getArgument(0);
        
        if ($arg1)
        {
            $this->out('Argument1: ' . $arg1);
        }
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

    public function initialise()
    {
        $this->addCommand(new BarCommand)
            ->addCommand(new YooCommand);
    }
```

OK, let's typing:

``` bash
$ cli/console.php foo bar
```

We get:

```
This is Bar Command executing code.
```

And typing

``` bash
$ cli/console.php foo bar sakura
```

get:

```
This is Bar Command executing code.
Argument1: sakura
```

### Get Child by Path

``` php
$command = $console->getCommand('foo/bar'); // BarCommand

// OR

$command = $command->getChild('foo/bar/baz');
```

## The Prompter

Prompter is a set of dialog tools help us asking questions for user.

``` php
$prompter = new \Windwalker\Console\Prompter\TextPrompter;

$name = $prompter->ask('Tell me your name:', 'default');
```

OR set question in constructor.

``` php
$prompter = new TextPrompter('Tell me your name: ', $this->io);

// If argument not exists, auto ask user.
$name = $this->getArgument(0, $prompter);
```

### Validate Input Value

``` php
$prompter = new \Windwalker\Console\Prompter\ValidatePrompter;

$prompter->setAttempt(3);

$prompter->ask('Please enter username: ');
```

If we didn't type anything, ValidatePrompter will try ask us three times (We set this number by `setAttempt()`).

```
Please enter username:
  Not a valid value.

Please enter username:
  Not a valid value.

Please enter username:
  Not a valid value.
```

We can set closure to validate our rule:

``` php
$prompter->setAttempt(3)
    ->setNoValidMessage('No valid number.')
    ->setHandler(
    function($value)
    {
        return $value == 9;
    }
);

$prompter->ask('Please enter right number: ');
```

Result

```
Please enter right number: 1
No valid number.

Please enter right number: 2
No valid number.

Please enter right number: 3
No valid number.
```

If validate fail, we can choose shut down our process:
 
``` php
// ...

$prompter->failToClose(true, 'Number validate fail and close');

$prompter->ask('Please enter right number: ');
```

Result

```
Please enter right number:
No valid number.

Please enter right number:
No valid number.

Please enter right number:
No valid number.

Number validate fail and close
```

### Select List

``` php
$options = array(
    's' => 'sakura',
    'r' => 'Rose',
    'o' => 'Olive'
);

$prompter = new \Windwalker\Console\Prompter\SelectPrompter('Which do you want: ', $options);

$result = $prompter->ask();

$command->out('You choose: ' . $result);
```

Output

```
  [s] - sakura
  [r] - Rose
  [o] - Olive

Which do you want: r
You choose: r
```

### Boolean Prompter

BooleanPrompter convert input string to boolean type, the (y, yes, 1) weill be `true`, (n, no, 0, null) will be `false`.

``` php
$prompter = new \Windwalker\Console\Prompter\BooleanPrompter;

$result = $prompter->ask('Do you wan to do this [Y/n]: ');

var_dump($result);
```

Result

```
Do you wan to do this [Y/n]: y
bool(true)
```

### Available Prompters

- TextPrompter
- SelectPrompter
- CallbackPrompter
- ValidatePrompter
- NotNullPrompter
- PasswordPrompter

### Available Prompters

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

We can using `Command` without, please see [Command README](Command).

## Credits

Windwalker Console incorporated many ideas from other CLI packages. 
Below is a short list of projects which Windwalker drew inspiration.

- [Symfony Console](https://github.com/symfony/Console)
- [Commando](https://github.com/symfony/Console)
- [CLIFramework](https://github.com/c9s/CLIFramework)
- [Composer](https://github.com/composer/composer)


