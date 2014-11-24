# Windwalker Filesystem

Windwalker Filesystem provides some easy interface to operate file and folders.

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/filesystem": "~2.0"
    }
}
```

## File

File is a abstract class provides methods to operate files.

### Write & Delete files

``` php
use Windwalker\Filesystem\File;

$file = '/path/to/file.txt';

File::write($file, $content);

File::delete($file);
```

### Move & Copy

``` php
$src = '/path/to/file.txt';
$dest = '/path/of/new/file.txt';

// Move
File::move($src, $dest);

// Force move if file exsits
File::move($src, $dest, true);

// Copy
File::copy($src, $dest);

// Force copy if file exists
File::copy($src, $dest, true);
```

### Upload

``` php
File::upload($src, $dest);
```

It is basically the wrapper for the PHP `move_uploaded_file()` function, but also does checks availability
and permissions on both source and destination path.

### File Name

Strip extension and get simple path:

``` php
$file = '/path/to/flower.txt';

$name = File::stripExtension($file); // /path/to/flower
```

Get extension:

``` php
$file = '/path/to/flower.txt';

File::getExtension($file); // txt
```

Get only file name.

``` php
$file = '/path/to/flower.txt';

File::getFilename($file); // flower.txt
```

Make file name safe to store.

``` php
$path = /path!/to/flower sakura.

File::makeSafe($path); // /path/to/flowersakura
```

##  Folder

Folder class help us operate folders and list files of a folder.

### Folder Operation

Move & Copy

``` php
use Windwalker\Filesystem\Folder;

// Move folder
Folder::move($src, $dest);

// Force move if folder exists
Folder::move($src, $dest, true);

// Copy folder, will also copy all children
Folder::copy($src, $dest);

// Force copy if folder exists
Folder::copy($src, $dest, true);
```

Create & Delete

``` php
Folder::create($path);

Folder::delete($path);
```

### List Files

#### List Folders

``` php
// List folders of a folder
Folder::folders($dir);

// List folders recursive
Folder::folders($dir, true);

// Path type: Get basename of every item
Folder::folders($dir, true, Folder::PATH_BASENAME);
```

Available path type:

- `Folder::PATH_ABSOLUTE` - Absolute full path.
- `Folder::PATH_RELATIVE` - Relative path from folder you scan.
- `Folder::PATH_BASENAME` - The folder or file name.

#### List Files

``` php
// List files of a folder
Folder::files($dir);

// List files recursive
Folder::files($dir, true);

// Path type: Get basename of every item
Folder::files($dir, true, Folder::PATH_BASENAME);
```

#### List Files & Folders

``` php
// List files & folders of a folder
Folder::items($dir);

// List files & folders recursive
Folder::items($dir, true);

// Path type: Get basename of every item
Folder::items($dir, true, Folder::PATH_RELATIVE);
```

## Filesystem Class

Filesystem class is a universal finder to find and operate folders & files.

### Folder & File Operation

Filesystem move & copy method will auto detect the path is a folder or file, if is folder, it will call `Folder::move()`,
 if is file, it will call `File::move()`.

``` php
// we don't need to know $src is a folder or file, Filesystem will detect it.
Filesystem::move($src, $dest);
Filesystem::copy($src, $dest);
Filesystem::delete($src, $dest);
```

## Path

A simple helper to handler some path string.

### Clean Path

To strip additional / or \ in a path name.

``` php
$path = '/var/www\flower\sakura/olive';

$path = Path::clean($path); // Will be: '/var/www/flower/sakura/olive'
```

### Permissions

``` php
// Get by number
Path::getPermissions($path); // 755

// Get by string
Path::getPermissions($path, true); // dwrxwrx--x

// Set permissions, if is file, set to 644, if is folder set to 755
Path::setPermissions($path, '0644', '0755');

// Check can change Permissions
Path::canChmod($path); // Bool
```



### Finder

Finder is the most powerful function of Filesystem, it provides an interface to let us use our logic to filter files.

####  Simple Finder

``` php
// Argument 2 is recursive
$folders = Filesystem::folders($path, true);
$files   = Filesystem::files($path, true);
$items   = Filesystem::items($path, true);
```

The simple finder are same as Folder, but it will not scan all files instantly, but returns an Iterator instead,
 we can send this iterator to `foreach` and do some filter by `SplFileInfo` object.

``` php
foreach ($items as $item)
{
    if ($item->isDir() || $item->isDot())
    {
        continue;
    }

    // Do something
}
```

If you really need array not an Iterator, use `Filesystem::iteratorToArray()`.

``` php
$folders = Filesystem::folder($path, true);

// To array
$folders = Filesystem::iteratorToArray($folders);
```

Or set argument 3 to `TRUE`.

``` php
$folders = Filesystem::folder($path, true, true);

is_array($folders); // TRUE
```

### Advanced Finder

Find by name.

``` php
$files = Filesystem::find($path, 'flower.php');
```

Multiple name.

``` php
$files = Filesystem::find($path, array('flower.php', 'sakura.php'));
```

Find by regex

``` php
$files = Filesystem::find($path, '^[a-zA-Z0-9]');
```

Only find first matched.

``` php
$file = Filesystem::findOne($path, array('flower.php', 'sakura.php'));
```

Find recursive

``` php
$files = Filesystem::find($path, array('flower.php', 'sakura.php'), true);
```

To array

``` php
$files = Filesystem::find($path, array('flower.php', 'sakura.php'), true, true);
```

### Callback Finder

Inject our filter logic to find files.

``` php
/**
 * Files callback
 *
 * @param \SplFileInfo                $current  Current item's value
 * @param string                      $key      Current item's key
 * @param \RecursiveDirectoryIterator $iterator Iterator being filtered
 *
 * @return boolean   TRUE to accept the current item, FALSE otherwise
 */
$closure = function($current, $key, $iterator)
{
    return Path::getPermissions($current->getPath()) >= 755;
};

$files = Filesystem::find($path, $closure, true);
```

Or create a `FileComparator` object.

``` php
use Windwalker\Filesystem\Comparator\FileComparatorInterface;

class MyComparator implements FileComparatorInterface
{
   public function compare($current, $key, $iterator)
   {
       return Path::getPermissions($current->getPath()) >= 755;
   }
}

$files = Filesystem::find($path, new MyComparator, true);
```

### Advanced Comparator

We can create our own comparator like an finder object.

``` php
// This is just an example

$comparator = new MyAdvancedComparator;

$comparator->setTimeGreaterThan(new DataTime)
    ->setExtension('ini|php')
    ->setSize('< 2M')
    ->setPermission('>= 644');

$files = Filesystem::find($path, $comparator, true);
```

## PathLocator & PathCollection

See [PathLocator Document](Path)
