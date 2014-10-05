# Windwalker Filesystem PathLocator

## PathLocator object

A Path locator, help us locate a system path or relative path.

We can use this object to find files or list files, and also support using callback to filter files.

#### Create a new PathLocator

``` php
$path = new PathLocator('/var/www/flower');
```

#### Convert to string

``` php
echo $path

//or

(string) $path;
```

#### Path operation

Use chaining to operate path.

##### Child

``` php
$path->child('plugins')           // => /var/www/flower/plugins
    ->child('system/flower/lib'); // => /var/www/flower/plugins/system/flower/lib
```

##### Parent

``` php
$path->parent()       // => /var/www/flower/plugins/system/flower (Up one level)
    ->parent(2)       // => /var/www/flower/plugins               (Up 2 levels)
    ->parent('www');  // => /var/www                              (Find a parent and go this level)
```

##### Prefix

Add a prefix of system path, we can change it, only when converting to string, the prefix will be added to path.

``` php
$path2 = new PathLocator('src/Sakura/Olive');

echo $path->addPrefix($_SERVER['DOCUMENT_ROOT']); // => /var/www/src/Sakura/Olive
```

#### Filesystem Operation

``` php
echo $path->isDir();  // true or false
echo $path->isFile(); // true or false
echo $path->exists(); // true or false
```

##### Get file info

This function has not prepared yet.

``` php
$path->getInfo();            // return SplFileInfo of current directory

$path->getInfo('index.php'); // return SplFileInfo of this file
```

##### Scan dir

Get Folders

``` php
$dirs = $path->getFolders([true to recrusive]);

foreach($dirs as $dir)
{
    echo $dir . '<br />'; // print all dir's name
}
```

Get Files

``` php
$files = $path->getFiles([true to recrusive]);

foreach($files as $file)
{
    echo $file->getPathname() . '<br />'; // print all file's name
}
```

##### Find files

Find by string or regex

``` php
echo $path->find('config.json');     // Find one file and return fileinfo object

echo $path->find(array('^config'));  // Find one file by regex

// Second argument set to TRUE for recursive
foreach($path->findAll(array('^config_*.json', '!^..'), true) as $file)
{
    // Find all files as array, param 2 to recursive
}
```

Find by callback

``` php
$callback = function($current, $key, $iterator)
{
    return return @preg_match('^Foo', $current->getFilename())  && ! $iterator->isDot();
};

foreach($path->findAll($callback, true) as $file)
{
    // ...
}
```

Find by Comparator

This comparator object may contain the filter logic, but not prepared yet.

``` php
$comparator = new FileComparator;

foreach($path->findAll(FileComparatorInterface $comparator, true) as $file)
{
    // ...
}
```

#### Strict Mode

This function not prepared yet.

``` php
$dl2 = new \DirectoryLocator($_SERVER['DOCUMENT_ROOT'] . '/src', true);
$dls->child('Campenont');        // throw PathNotExistsException();
$dls->child('../www/index.php'); // throw PathNotDirException();
```

*

## PathCollection object

A collection of paths, we can put many paths to this object, and use it as array.

And we can use this collection to iterate all sub dirs and files, also find files.

The iterator will travel to every `PathLocator` in this collection object, and return an SplFileInfo for us.

#### Create a new PathCollection

##### Add with no key

``` php
$paths = new PathCollection(array(
    new PathLocator('templates/' . $template . '/html/' . $option),
    new PathLocator('Sakuras/' . $option . '/view/tmpl/'),
    'layouts/' . $option ,     // Auto convert to PathLocator
));
```

##### Add with key name

``` php
$paths = new PathColleciotn(array(
    'Template'  => new PathLocator('templates/' . $template . '/html/' . $option),
    'Sakura' => new PathLocator('Sakuras/' . $option . '/view/tmpl/'),
    'Layout'    => new PathLocator('layouts/' . $option)
));
```

#### Paths operations

##### Add path

``` php
$paths->addPath(new PathLocator('Foo'));        // No key name, will using number as key

$paths->addPath(new PathLocator('Foo'), 'Foo'); // With key name
```

##### Add paths

``` php
$paths->addPaths(array(new PathLocator('Bar'))); // Add by array
```

##### Remove path

``` php
$paths->removePath('Foo');  // Remove by key name
$paths->removePath(0);      // Remove by number
```

##### Set prefix to all paths

We can change this prefix, only when converting to string,
the prefix will have been added to path.

``` php
// Prepend all path with a prefix path.

$paths->setPrefix('/var/www/flower');
```

#### Iterator

List all PathLocator

``` php
foreach($paths as $path)
{
    echo $path // print path string
}
```

Return a raw array

``` php
foreach($paths->toArray() as $path)
{
    echo $path // print path string
}
```

List all files and folders of all paths

``` php
foreach($paths->getAllChildren([true to recrusive]) as $file)
{
    echo $file // SplFileInfo
}
```

List all files

``` php
foreach($paths->getFiles([true to recrusive]) as $file)
{
    echo $file->getFilename() // SplFileInfo
}
```

List all folders

``` php
foreach($paths->getFolders([true to recrusive]) as $dir)
{
    echo $file->getPathname() // SplFileInfo
}
```

#### Find Files and Folders

Same as PathLocator, but return all paths' file & folders.

``` php
$paths->find('config.json');

$paths->findAll('config_*.json');
```

-------

### Using it as array or string

``` php
$cache  = new PathLocator($_SERVER['DOCUMENT_ROOT'] . '/cache');
$loader = new \Twig_Loader_Filesystem($paths);

$twig = new \Twig_Environment($loader, array(
    'cache' => (string) $cache,
));
```
