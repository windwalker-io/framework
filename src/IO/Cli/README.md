# CLI Input & Output

## The CLI Input

When we are writing command line program, mostly we will use `$_SERVER['argv']` to get command and options.

If we type:

``` bash
php cli/console.php flower sakura -a -bc --olive -d=foo --bar baz
```

The `$_SERVER['argv']` will look like:

```
Array(
    [0] => cli/console.php 
    [1] => flower 
    [3] => sakura 
    [4] => -a 
    [5] => -bc 
    [6] => --olive 
    [7] => -d=foo 
    [8] => --bar
    [9] => baz
)
```

It is hard to use, The `CliInput` object will parse this arguments and provides us an easy using interface to get arguments and options.

``` php
use Windwalker\IO\Cli\Input\CliInput;

$input = new CliInput;

$input->getCalledScript(); // cli/console.php

// Get Arguments
$input->getArgument(0); // flower

$input->getArgument(3, 'default_value'); // Return default

// Get Options
$input->get('a'); // true
$input->get('b'); // true
$input->get('g'); // null
$input->get('olive'); // true

$input->get('d'); // 'foo'
$input->get('bar'); // 'baz'

$input->get('yoo', 'default'); // Return default
```

### Ask From STDIN

``` php
echo 'What is your name: ';
$answer = $input->in(); // Terminal wil ask user question

// Same as 
$answer = fread(STDIN);
```

## The CLI Output

CliOutput help us write text to standard output

``` php
use Windwalker\IO\Cli\Output\CliOutput;

$output = new CliOutput;

// Write to STDOUT
$output->out('It is the east, and Juliet is the sun.');

// Write to STDERR
$output->err('Whoop, there has something wrong.');
```

See php [IO-Streams](http://php.net/manual/en/features.commandline.io-streams.php)

### Colorful Output

It is possible to use colors on an ANSI enabled terminal.

``` php
// Green text
$output->out('<info>foo</info>');

// Yellow text
$output->out('<comment>foo</comment>');

// Black text on a cyan background
$output->out('<question>foo</question>');

// White text on a red background
$output->out('<error>foo</error>');
```

You can also create your own styles.

``` php
use Windwalker\IO\Cli\Color\ColorStyle;
use Windwalker\IO\Cli\Output\ColorfulOutputInterface;

if ($output instanceof ColorfulOutputInterface)
{
    $style = new Colorstyle('yellow', 'red', array('bold', 'blink'));

    $output->getProcessor()->addStyle('fire', $style);
}

$output->out('<fire>foo</fire>');
```

Available foreground and background colors are: 

- black
- red
- green
- yellow
- blue
- magenta
- cyan
- white

And available options are: 

- bold
- underscore
- blink
- reverse

You can also set these colors and options inside the tagname:

``` php
// Green text
$output->out('<fg=green>foo</fg=green>');

// Black text on a cyan background
$output->out('<fg=black;bg=cyan>foo</fg=black;bg=cyan>');

// Bold text on a yellow background
$output->out('<bg=yellow;options=bold>foo</bg=yellow;options=bold>');
```

## The IO Object

IO is an object integrate both Input and Output to provides an universal interface to handler I/O in command line programs.

``` php
use Windwalker\IO\Cli\IO;

$io = new IO;

// Or inject your object
$io = new IO(new CliInput, new CliOutput);
```

The interface is totally same as Input and Output.

``` php
$io->in('Question: ');
$io->out('Brevity is the soul of wit.');

$io->getArgument(0);
$io->getOption('foo');

$io->getCalledScript();
```

### Array Access

The benefit of IO object is that it can use array access to get arguments:

``` php
$arg1 = $io[0];
$arg2 = $io[1] ? : 'baz';
```
