# Windwalker Validator

Windwalker Validator is a simple interface to help up validate strings.

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/validator": "~2.0"
    }
}
```

## Simple Validate Process

``` php
use Windwalker\Validator\Rule\EmailValidator;

$validator = new EmailValidator;

$validator->validate('sakura@flower.com'); // bool(true)

$validator->validate('sakura'); // bool(false)
```

### Available Validator Rules

- AlnumValidator
- BooleanValidator
- ColorValidator
- CreditcardValidator
- EmailValidator
- EqualsValidator
- IpValidator
- NoneValidator
- PhoneValidator
- RegexValidator
- UrlValidator

## Regex Validator

``` php
use Windwalker\Validator\Rule\RegexValidator;

$validator = new RegexValidator('^[a-zA-Z0-9]*$', 'i');

$validator->validate('abc_123:978'); // bool(false)
```

## Equals Validator

``` php
use Windwalker\Validator\Rule\EqualsValidator;

$validator = new EqualsValidator('ABC');

$validator->validate('ABC'); // bool(true)
```

Strict Mode:

``` php
$validator = new EqualsValidator(123, true);

$validator->validate('123'); // bool(false)
```

## Error Message

``` php
$validator->setMessage('This string is not valid');

if (!$validator->validate('sakura'))
{
    throw new \Exception($validator->getError());
}
```

## Create Your Own Validator

``` php
use Windwalker\Validator\AbstractValidator;

class MyValidator extends AbstractValidator
{
	public function test($string)
	{
		return (bool) strlen($string);
	}
}

$validator = new MyValidator;

$validator->validate('foo');
```

## Extends Regex Validator

``` php
use Windwalker\Validator\Rule\RegexValidator;

class MyRegexValidator extends RegexValidator
{
	protected $modified = 'i';
	protected $regex = '[a-zA-Z]';
}
```
