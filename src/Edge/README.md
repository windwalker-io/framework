# Windwalker Edge

Edge is a [Blade](https://laravel.com/docs/5.1/blade) compatible template engine, provides same syntax to support
Blade template files, but has more powerful extending interfaces.

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/edge": "~3.0"
    }
}
```

## Getting Started

### Render Text Template

``` php
use Windwalker\Edge\Edge;
use Windwalker\Edge\Loader\EdgeStringLoader;

$edge = new Edge(new EdgeStringLoader);

echo $edge->render('<h1>{{ $title }}</h1>', array('title' => 'Hello World~~~!'));
```

Result:

``` html
<h1>Hello World~~~!</h1>
```

### File Loader

``` php
use Windwalker\Edge\Edge;
use Windwalker\Edge\Loader\EdgeFileLoader;

$paths = array(
	__DIR__ . '/tmpl',
	'/template/path1',
	'/template/path2',
);

$edge = new Edge(new EdgeFileLoader($paths));

// This will load `./tmpl/layout/index.edge.php` file as template
echo $edge->render('layout.index', array('title' => 'Hello', 'content' => 'Everyone'));
```

The result:

``` html
<h1>Hello</h1>
<p>Everyone</p>
```

#### File Format (Extension)

Edge is Blade compatible, so we can also use `.blade.php` format as template file.

We can also add our new formats.

``` php
$loader = new EdgeFileLoader($paths);
$loader->addFileExtension('.foo.php');
```

### Cache File

We can cache compiled template to a folder so that we don't need to re-compile them every time.
If the origin file has been modified, the cache file will auto refresh.

``` php
use Windwalker\Edge\Cache\EdgeFileCache;
use Windwalker\Edge\Edge;
use Windwalker\Edge\Loader\EdgeFileLoader;

$paths = array(__DIR__ . '/tmpl');

$edge = new Edge(new EdgeFileLoader($paths), null, new EdgeFileCache(__DIR__ . '/cache'));

echo $edge->render('layout.index', array('title' => 'Hello', 'content' => 'Everyone'));
```

The file will cached at `./cache/~d673948ede8e9982504dd46407f3038d`:

``` html
<h1><?php echo $this->escape($title); ?></h1>
<p><?php echo $this->escape($content); ?></p>
```

## Global Variables

Add global variables to Edge, these variables will inject to template when every time we are rendering.

``` php
$edge = new Edge;
$edge->addGlobal('flower', 'sakura'); // Global variable

$edge->render('layout', array('foo' => 'bar')); // foo is local variable
```

## Edge Syntax

Most of Edge syntax are same as Blade.

### Echoing Data

Display a variable by `{{ ... }}`

``` html
Hello {{ $title }}
```

Unescaped echoing.

``` html
My name is {!! $form->input('name') !!}
```

### Control Structures

#### If Statement

Use `@if ... @else` directive.

``` html
@if (count($flower) == 1)
    I have one flower!
@elseif (count($flower) > 1)
    I have many flkowers!
@else
    I don't have any flower!
@endif
```

Unless directive

``` html
@unless ($user->isAdmin())
    You are not logged in.
@endunless
```

### Loops

Edge provides simple directives similar to PHP loop structure.

``` html
@for ($i = 0; $i < 10; $i++)
    The current value is {{ $i }}
@endfor

@foreach ($users as $user)
    <p>This user is: {{ $user->name }}</p>
@endforeach

@forelse ($articles as $article)
    <li>{{ $article->title }}</li>
@empty
    <p>No article here</p>
@endforelse

@while (true)
    <p>I'm looping forever.</p>
@endwhile
```

You might need to break or skip a loop.

``` html
@foreach ($users as $user)

    @if (!$user->id)
        @continue
    @endif

    <p>This user is: {{ $user->name }}</p>

    @if ($user->id >= 10)
        @break
    @endif

@endforeach
```

Or add conditions to these two directives.

``` html
@continue(!$user->id)

@break($user->id >= 10)
```

## Components & Slots

Components and slots provide similar benefits to sections and layouts; however, some may find the mental model of 
components and slots easier to understand. First, let's imagine a reusable "alert" component we would like to reuse throughout our application:

```html
<div class="alert alert-danger">
    {{ $slot }}
</div>
```

The `{{ $slot }}` variable will contain the content we wish to inject into the component. 
Now, to construct this component, we can use the `@component` directive:

```html
@component('alert')
    <strong>Whoops!</strong> Something went wrong!
@endcomponent
```

Sometimes it is helpful to define multiple slots for a component. Let's modify our alert component to allow for the 
injection of a "title". Named slots may be displayed by "echoing" the variable that matches their name:

```html
<div class="alert alert-danger">
    <div class="alert-title">{{ $title }}</div>

    {{ $slot }}
</div>
```

Now, we can inject content into the named slot using the `@slot` directive. 
Any content not within a `@slot` directive will be passed to the component in the $slot variable:

```html
@component('alert')
    @slot('title')
        Forbidden
    @endslot

    You are not allowed to access this resource!
@endcomponent
```

### Passing Additional Data To Components

Sometimes you may need to pass additional data to a component. For this reason, you can pass an array 
of data as the second argument to the `@component` directive. 
All of the data will be made available to the component template as variables:

```html
@component('alert', ['foo' => 'bar'])
    ...
@endcomponent
```

## Layouts

We can define some sections in a root template.

``` html
<!-- tmpl/layouts/root.edge.php -->
<html>
    <head>
        <title>@yield('page_title')</title>
    </head>
    <body>
        @section('body')
            The is root body
        @show
    </body>
</html>
```

Now we can add an child template to extends root template.

``` html
@extends('layouts.root')

@section('page_title', 'My Page Title')

@section('content')
    <p>This is my body content.</p>
@endsection
```

The final template rendered:

``` html
<html>
    <head>
        <title>My Page Title</title>
    </head>
    <body>
        <p>This is my body content.</p>
    </body>
</html>
```

More directive and usages please see [Blade](https://laravel.com/docs/5.2/blade#defining-a-layout)

## Extending Edge

### Add Directive to EdgeCompiler

``` php
use Windwalker\Edge\Compiler\EdgeCompiler;

$edge = new Edge(new EdgeStringLoader);

$compiler = $edge->getCompiler();

$compiler->directive('upper', function ($expression)
{
	return "<?php echo strtoupper$expression; ?>";
});

echo $edge->render('<h1>@upper("flower")</h1>');
```

Output

``` html
<h1>FLOWER</h1>
```

### Use Extension Class

We can create Extension class to add multiple directives and global variables to Edge.

``` php
class MyExtension implements \Windwalker\Edge\Extension\EdgeExtensionInterface
{
	public function getName()
	{
		return 'my_extension';
	}

	public function getDirectives()
	{
		return array(
			'upper' => array($this, 'upper'),
			'lower' => array($this, 'lower'),
		);
	}

	public function getGlobals()
	{
		return array(
			'flower' => 'sakura'
		);
	}

	public function getParsers()
	{
		return array();
	}

	public function upper($expression)
	{
		return "<?php echo strtoupper$expression; ?>";
	}

	public function lower($expression)
	{
		return "<?php echo strtolower$expression; ?>";
	}
}

// Inject this extension to Edge

$edge->addExtension(new MyExtension[, $name = null]);
```

