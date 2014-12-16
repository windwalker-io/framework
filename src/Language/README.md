# Windwalker Language

Windwalker Language is a simple i18n handler to process multi-language text.

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/language": "~2.0"
    }
}
```

## Getting Started

Create an ini language file:

``` ini
[en-GB]
WINDWALKER_LANGUAGE_TEST_FLOWER="Flower"
WINDWALKER_LANGUAGE_TEST_SAKURA="Sakura"
```

``` ini
[zh-TW]
WINDWALKER_LANGUAGE_TEST_FLOWER="花"
```

Now create Language instance and load language files.

``` php
use Windwalker\Language\Language;

$language = new Language;

// Load default language first
$language->load(__DIR__ . '/lang/en-GB.ini', 'ini');

// Load current language to override default.
$language->load(__DIR__ . '/lang/zh-TW.ini', 'ini');
```

Translate string:

``` php
// zh-TW has this language key, so it will be translated
$language->translate('WINDWALKER_LANGUAGE_TEST_FLOWER'); // 花

// This key not exists in zh-TW, use en-GB as default
$language->translate('WINDWALKER_LANGUAGE_TEST_SAKURA'); // Sakura
```

### Key Format

All language key will be normalised to lowercase and separated by dot (`.`).

These cases all get same result:

``` php
$language->translate('WINDWALKER_LANGUAGE_TEST_FLOWER'); // 花
$language->translate('WINDWALKER_language_TEST FLOWER'); // 花
$language->translate('windwalker.language.test.flower'); // 花
$language->translate('Windwalker Language Test Flower'); // 花
$language->translate('Windwalker Language, Test Flower~~~!'); // 花
```

### Replace String

``` ini
WINDWALKER_LANGUAGE_TEST_BEAUTIFUL_FLOWER="The %s is beautiful~~~!!!"
```

``` php
$this->instance->sprintf('WINDWALKER_LANGUAGE_TEST_BEAUTIFUL_FLOWER', 'Sunflower');

// Result: The Sunflower is beautiful~~~!!!
```

### Plural String

Create a Localise class:

``` php
// An example of EnGB Localise
namespace Windwalker\Language\Localise;

class EnGBLocalise implements LocaliseInterface
{
	public function getPluralSuffix($count = 1)
	{
		if ($count == 0)
		{
			return '0';
		}
		elseif ($count == 1)
		{
			return '';
		}

		return 'more';
	}
}
```

And prepare this language keys.

``` ini
WINDWALKER_LANGUAGE_TEST_SUNFLOWER="Sunflower"
WINDWALKER_LANGUAGE_TEST_SUNFLOWER_0="No Sunflower"
WINDWALKER_LANGUAGE_TEST_SUNFLOWER_MORE="Sunflowers"
```

Now we can translate plural string.

``` php
$this->instance->plural('Windwalker Language Test Sunflower', 0); // No Sunflower
$this->instance->plural('Windwalker Language Test Sunflower', 1); // Sunflower
$this->instance->plural('Windwalker Language Test Sunflower', 2); // Sunflowers
```

Set locale and default language key in construct that language object can get default Localise to translate plural string 
if this string dose not exists in current locale:

``` php
$language = new Language('zh-TW', 'en-GB);
```

## If Language Key Not Exists

Language object will return raw string which we send into it.

``` php
echo $language->translate('A Not Translated String');
echo "\n";
echo $language->translate('A_NOT_TRANSLATED_STRING');
```

Result:

```
A Not Translated String
A_NOT_TRANSLATED_STRING
```

## Using Other Formats

### Yaml

Yaml language file can write as nested structure.

``` yaml
windwalker:
    language.test:
        sakura: Sakura
        olive: Olive
```

``` php
$language->load(__DIR__ . '/lang/en-GB.yml', 'yaml');

$language->translate('windwalker.language.test.sakura'); // Sakura
$language->translate('WINDWALKER_LANGUAGE_TEST_OLIVE'); // Olive
```

### Json

``` json
{
	"windwalker" : {
		"language-test" : {
			"sakura" : "Sakura",
			"olive" : "Olive"
		}
	}
}
```

The usage same as yaml.

``` php
$language->load(__DIR__ . '/lang/en-GB.json', 'json');
```

### PHP

``` php
<?php

return array(
	'WINDWALKER_LANGUAGE_TEST_FLOWER' => "Flower",
	'WINDWALKER_LANGUAGE' => array(
			'TEST' => array(
				'SAKURA' => "Sakura"
			)
		)
);
```

The usage same as yaml.

$language->load(__DIR__ . '/lang/en-GB.php', 'php');

## Used Keys

``` php
// Get keys which have been used.
$language->getUsed();
```

## Debug Mode
 
``` php
$language->setDebug(true);
```

We can get non-translated keys in debug mode.

``` php
echo $language->translate('A Translation Exists String');
echo "\n";
echo $language->translate('A Not Translated String');

$language->getOrphan(); // Array([0] => A Not Translated String);
```

And the output will be:

```
**A Translation Exists String**
??A Not Translated String??
```
