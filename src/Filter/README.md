# Windwalker Filter

Windwalker Filter package is a simple tool help us clean the input and output string. 

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/filter": "~3.0"
    }
}
```

## Input Filter

Create a filter object and use `clean()` to filter input string.

``` php
use Windwalker\Filter\InputFilter;

$filter = new InputFilter;

$username = $_REQUEST['username'];

$username = $filter->clean($username, InputFilter::STRING);
```

Available filter rules:

| Name                    | Description |
| ----------------------- | ------------|
| InputFilter::INTEGER    | Only use the first integer value |
| InputFilter::UINT       | Unsigned integer, Only use the first integer value |
| InputFilter::FLOAT      | Float number, Only use the first integer value |
| InputFilter::BOOLEAN    | Return true or false |
| InputFilter::WORD       | Only allow characters a-z, and underscores |
| InputFilter::ALNUM      | All numbers and letters (alphanumerics) |
| InputFilter::CMD        | Allow a-z, 0-9, underscore, dot, dash. Also remove leading dots from result. |
| InputFilter::BASE64     | Allow a-z, 0-9, slash, plus, equals. |
| InputFilter::STRING     | Converts the input to a plain text string; strips all tags / attributes. |
| InputFilter::HTML       | HTML format, but will clean tags in black list |
| InputFilter::ARRAY_TYPE | Attempts to convert the value to an array. |
| InputFilter::PATH       | Converts the input into a string and validates it as a path. (e.g. path/to/file.png or path/to/dir)|
| InputFilter::USERNAME   | Strips all invalid username characters. |
| InputFilter::EMAIL      | Strips all invalid email characters. |
| InputFilter::URL        | Strips all invalid url characters. |
| InputFilter::RAW        | No filter |

### Custom Rules

Using closure as filter rule.

``` php
$closure = function($value)
{
    return str_replace('Tony Stark', 'Iron Man', $value);
};

$filter->setHandler('armor', $closure);

$string = $filter->clean("Hi I'm Tony Stark~~~", 'armor');

// $string will be "Hi I'm Iron Man"
```

Using Cleaner object

``` php
use Windwalker\Filter\Cleaner\CleanerInterface;

class ArmorCleaner implements CleanerInterface
{
    public function clean($source)
    {
        return str_replace('Tony Stark', 'Iron Man', $value);
    }
}

$filter->setHandler('armor', new ArmorCleaner);

$string = $filter->clean("Hi I'm Tony Stark~~~", 'armor');

// $string will be "Hi I'm Iron Man"
```

## Output Filter

OutputFilter provides some methods help us strip or escape unsafe code to prevent XSS attack.

``` php
use Windwalker\Filter\OutputFilter;

// Makes an object safe to display in forms
$object = (object) array('flower' => '<sakura>');

OutputFilter::objectHTMLSafe($object); // &lt;sakura&gt;

// This method processes a string and replaces all instances of & with &amp; in links only.
OutputFilter::linkXHTMLSafe($input);

// This method processes a string and replaces all accented UTF-8 characters by unaccented
// ASCII-7 "equivalents", whitespaces are replaced by hyphens and the string is lowercase.
OutputFilter::stringURLSafe($url);

// This method implements unicode slugs instead of transliteration.
OutputFilter::stringURLUnicodeSlug($url);

// Replaces &amp; with & for XHTML compliance
OutputFilter::ampReplace($text);

// Cleans text of all formatting and scripting code
OutputFilter::cleanText($text);

// Strip img-tags from string
OutputFilter::stripImages($html);

// Strip iframe-tags from string
OutputFilter::stripIframes($html);

// Strip script-tags from string
OutputFilter::stripScript($html);

// Strip style-tags from string
OutputFilter::stripStyle($html);
```
