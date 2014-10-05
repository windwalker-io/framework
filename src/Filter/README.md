# Windwalker Filter Package

Windwalker Filter package is a simple tool help us clean the input and output string. 

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/filter": "~2.0"
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

| Name                  | Description |
| --------------------- | ------------|
| InputFilter::INTEGER  | Only first part of int in a string |
| InputFilter::UINT     | Unsigned integer |
| InputFilter::FLOAT    | Float number |
| InputFilter::BOOLEAN  | Return true or false |
| InputFilter::WORD     | Contains spaces and special characters |
| InputFilter::ALNUM    | All numbers and letters (alphanumerics) |
| InputFilter::CMD      | Only letters and `-`, `_` |
| InputFilter::BASE64   | Base64 string |
| InputFilter::STRING   | Letters and spaces |
| InputFilter::HTML     | HTML format, but will clean tags in black list |
| InputFilter::ARRAY    | An array |
| InputFilter::PATH     | System path contains letters and `/`, `\` |
| InputFilter::USERNAME | Letters allow to use as username |
| InputFilter::EMAIL    | Letters allow to use as email |
| InputFilter::URL      | Letters allow to use in URL |
| InputFilter::RAW      | No filter |

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

