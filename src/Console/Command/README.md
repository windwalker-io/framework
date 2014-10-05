# Windwalker Console Package Command Interface

## The Commands Structure

```
                    RootCommand
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

## Usage

We can using `Command` without `Console` object, just create this object.

### Simple One Command

``` php
<?php
// cli/app.php

use Windwalker\Console\Command\Command;
use Windwalker\Console\Command\Option;
use Windwalker\Console\IO\IO;

$command = new Command('app', new IO);

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
        function($command)
        {
            $name = $command->getArgument(0);
        
            if (!$name)
            {
                $name = $command->in('Please enter a name: ');
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

            $command->out($reply);
        }
    );

try
{
    $command->execute();
}
catch(Exception $e)
{
    $command->renderException($e);
}
```

The `execute()` method will execute your command handler. We set a `Closure` into `Command` and execute it.

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
$ php cli/app.php Asika --yell
```

Output

``` text
HELLO ASIKA
```

### Nested Command

``` php
<?php
// cli/app.php

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
    
try
{
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

### Declaring Command Classes

If you want to execute Command by your class, you can extends it from `Command`:

``` php
<?php
// First level: src/Myapp/FooCommand.php

namespace Myapp;

use Windwalker\Console\Command\Command;

class FooCommand extends Command
{
	public function initialise()
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

use Windwalker\Console\Command\Command;

class BarCommand extends Command
{
	public function initialise()
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

If we catch an exception, the `--verbose|-v` option help us print backtrace information.

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

This is part of Windwalker Console package documentation, please see [Console Package](..).
