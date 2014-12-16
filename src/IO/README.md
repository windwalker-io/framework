# Windwalker IO

Windwalker IO package is an input & output handler to get request or send output to user terminal.

This package is heavily based on Joomla Input but has modified a lot, please see original concept of [Joomla Wiki](http://docs.joomla.org/Retrieving_request_data_using_JInput).   

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/io": "~2.0"
    }
}
```

## Web Input

Mostly, we will need to get request data from http, the `$_GET`, `$_POST` or `$_REQUEST` provides us these data.

But it is very unsafe if we only use super global variables, the Input object can help us get values from these variables and clean every string.
  
``` php
use Windwalker\IO\Input;

$input = new Input;

$input->get('flower'); // Same as $_REQUEST['flower']

$input->set('flower', 'sakura');
```

The second argument is default value if request params not exists

``` php
$input->get('flower', 'default');
```

### Filter

Input use [Windwalker Filter](https://github.com/ventoviro/windwalker-filter) package to clean request string, the default filter type is `CMD`.
We can use other filter type:

``` php
// mysite.com/?flower=<p>to be, or not to be.</p>;

$input->get('flower'); // tobeornottobe (Default cmd filter)

$input->get('flower', 'default_value', InputFilter::STRING); // to be, or not to be

$input->getString('flower'); // to be, or not to be (Same as above, using magic method)

$input->getRaw('flower') // <p>to be, or not to be.</p>
```

More filter usage please see: [Windwalker Filter](https://github.com/ventoviro/windwalker-filter)

### Get Array

Input is able to get data as array. 

``` php
// mysite.com/?flower[1]=sakura&flower[2]=olive;

$input->get('flower', InputFilter::ARRAY); // Array( [1] => sakura [2] => olive)
```

Use `getArray()` method

``` php
// mysite.com/?flower=sakura&foo=bar&king=Richard

// Get all request
$input->getArray();

// To retrieve values you want
$array(
    'flower' => '',
    'king' => '',
);

$input->getArray($array); // Array( [flower] => sakura [king] => Richard)

// Specify different filters for each of the inputs:
$array(
    'flower' => InputFilter::CMD,
    'king' => InputFilter::STRING,
);

// Use nested array to get more complicated hierarchies of values

$input->getArray(array(
    'windwalker' => array(
        'title' => InputFilter::STRING,
        'quantity' => InputFilter::INTEGER,
        'state' => 'integer' // Same as above
    )
));
```

### Get And Set Multi-Level

If we want to get value of `foo[bar][baz]`, just use `setByPath()`:

``` php
$value = $input->getByPath('foo.bar.baz', 'default', InputFilter::STRING);

$input->setByPath('foo.bar.baz', $data);
```

### Get Value From Other Methods

We can get other methods as a new input object.

``` php
$post = $input->post;

$value = $post->get('foo', 'bar');

// Other inputs
$get    = $input->get;
$put    = $input->put;
$delete = $input->delete;
```

## Get SUPER GLOBALS

``` php
$env     = $input->env;
$session = $input->session;
$cookie  = $input->cookie;
$server  = $input->server;

$server->get('REMOTE_ADDR'); // Same as $_SERVER['REMOTE_ADDR'];
```

See: [SUPER GLOBALS](http://php.net/manual/en/language.variables.superglobals.php)

### Get method of current request:

``` php
$method = $input->getMethod();
```

## Files Input

The format that PHP returns file data in for arrays can at times be awkward, especially when dealing with arrays of files. 
FilesInput provides a convenient interface for making life a little easier, grouping the data by file.

Suppose you have a form like:

``` html
<form action="..." enctype="multipart/form-data" method="post">
    <input type="file" name="flower[test][]" />
    <input type="file" name="flower[test][]" />
    <input type="submit" value="submit" />
</form>
```

Normally, PHP would put these in an array called `$_FILES` that looked like:

```
Array
(
    [flower] => Array
        (
            [name] => Array
                (
                    [test] => Array
                        (
                            [0] => youtube_icon.png
                            [1] => Younger_Son_2.jpg
                        )

                )

            [type] => Array
                (
                    [test] => Array
                        (
                            [0] => image/png
                            [1] => image/jpeg
                        )

                )

            [tmp_name] => Array
                (
                    [test] => Array
                        (
                            [0] => /tmp/phpXoIpSD
                            [1] => /tmp/phpWDE7ye
                        )

                )

            [error] => Array
                (
                    [test] => Array
                        (
                            [0] => 0
                            [1] => 0
                        )

                )

            [size] => Array
                (
                    [test] => Array
                        (
                            [0] => 34409
                            [1] => 99529
                        )

                )

        )

)
```

FilesInput produces a result that is cleaner and easier to work with:

``` php
$files = $input->files->get('flower');
```

`$files` then becomes:

```
Array
(
    [test] => Array
        (
            [0] => Array
                (
                    [name] => youtube_icon.png
                    [type] => image/png
                    [tmp_name] => /tmp/phpXoIpSD
                    [error] => 0
                    [size] => 34409
                )

            [1] => Array
                (
                    [name] => Younger_Son_2.jpg
                    [type] => image/jpeg
                    [tmp_name] => /tmp/phpWDE7ye
                    [error] => 0
                    [size] => 99529
                )

        )
)
```

## CLI Input & Output

Please see [Cli README](Cli)
