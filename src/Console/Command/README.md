# Joomla Console Package Command Interface

A Joomla Framework Command package that support nested commend calling.

## The Command Calling Flow

``` bash
$ php cli/app.php command1 command2 command3 -a -b -cd --help
```

``` text
command1
    ->configure
    ->execute
    
    commend2
        ->configure
        ->execute
        
        commend3
            ->configure
            ->execute
        
        return
    
    return

return
```

## Usage

We can using `Command` without `CliApplicaion`, just create this object.

### Simple One Command

``` php
<?php
// cli/app.php

use Joomla\Command\Command;
use Joomla\Command\Option;
use Joomla\Input\Cli as Input;

try
{
    $command = new Command('app', new Input);

    $command->setDescription('This is first level command description')
        ->addOption(
            'q', // option name
            0,   // default value
            'Add this option can make output lower case.', // option description
            Option::IS_GLOBAL // sub command will extends this global option
        )
        ->addOption(
            ['y', 'yell', 'Y'], // First element will be option name, others will be alias
            0,
            'Yell will make output upper case.',
            Option::IS_PRIVATE // sub command will not extends normal option
        )
        ->setHandler(
            function($command, $input, $output)
            {
                if (empty($input->args[0]))
                {
                    $output->out('Please enter a name: ');
                    $name = fread(STDIN, 8792);
                }
                else
                {
                    $name = $input->args[0];
                }

                $reply = 'Hello ' . $name;

                if ($command->getOption('y'))
                {
                    $reply = strtoupper($reply);
                }

                if ($command->getOption('q'))
                {
                    $reply = strtolower($reply);
                }

                $output->out($reply);
            }
        );

    $command->execute();
}
catch(Exception $e)
{
    $command->renderException($e);
}

```

The `execute()` method will execute your command code. We set a `Closure` into `Command` and execute it.

When we type:

``` bash
$ php cli/app.php Asika
```

Output

``` text
Hello Asika
```

-----

Type:

``` bash
$ php cli/app.php Asika -q
```

Output

``` text
hello asika
```
-----

Type:

``` bash
$ php cli/app.php Asika -yell
```

Output

``` text
HELLO ASIKA
```

### Nested Command

``` php
<?php
// cli/app.php

use Joomla\Command\Command;
use Joomla\Command\Option;
use Joomla\Input\Cli as Input;

try
{
    $command = new Command('app', new Input);

    // Default Command
    $command->setDescription('This is first level command description')
        ->addOption(
            'q',
            0,
            'Add this option can make output lower case.',
            Option::IS_GLOBAL
        )
        // First level code
        ->setHandler(
            function($command, $input, $output)
            {
                $output->out('First level command.');
            }
        )
        // Second level commend
        ->addCommand(
            'second',
            'The second level argument',
            array(
                new Option(
                    array('a', 'A', 'ask'),
                    'a default',
                    'a desc'
                )
            ),
            function($command, $input, $output)
            {
                echo 'Second level commend.';
            }
        );

    $command->execute();
}
catch(Exception $e)
{
    $command->renderException($e);
}

```

Type:

``` bash
$ php cli/app.php
```

Output

``` text
First level command.
```

-----

Type:

``` bash
$ php cli/app.php second
```

Output

``` text
Second level commend.
```

### Extends Classes

If you want to execute Command by your class, you can extends it from `Command`:

``` php
<?php
// First level: src/Myapp/FooCommand.php

namespace Myapp;

use Joomla\Command\Command;

class FooCommand extends Command
{
	public function configure()
	{
		$this->setName('foo')
		    ->setDescription('foo desc')
			->addCommand(new Foo\BarCommand)
			->addOption(
			    'q',
			    0,
			    'q desc'
			)
			;
	}

	public function doExecute()
	{
		$this->output->out('Foo');
	}
}
```

And we add a sub command.

``` php
<?php
// Second level: src/Myapp/Foo/BarCommand.php

namespace Myapp\Foo;

use Joomla\Command\Command;

class BarCommand extends Command
{
	public function configure()
	{
		$this->setName('bar')
            ->setDescription('bar desc')
			;
	}

	public function doExecute()
    {
        $this->output->out('Bar');
    }
}
```

Type:

``` bash
$ php cli/app.php foo
```

Output

``` text
Foo
```

-----

Type:

``` bash
$ php cli/app.php foo bar
```

Output

``` text
Bar
```

## Using RootCommand and Help

You can use the `RootCommand` instead base `Command`, it provides some useful functions like `--help`, `--verbose`, `--quiet`.

If we catched an exception, the `--verbose|-v` option can help us print backtrace information.

``` php
<?php

use Windwalker\Console\Command\RootCommand;

try
{
	$command = new RootCommand;

	$command->execute();
}
catch(Exception $e)
{
	$command->renderException($e);
}

```

typing:

``` bash
php cli/app.php --help
```

We can get the help information.

## About Console Package

This is part of Joomla Console package documentation, please see [Console Package](..).

