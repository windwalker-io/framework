# Windwalker Validator

Windwalker Validator is a simple interface to help up validate strings.

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/validator": "~3.0"
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
- CallbackValidator
- CompareValidator
- PhpTypeValidator

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

## Composite

Match all.

```php
use Windwalker\Validator\ValidatorComposite;

$validator = new ValidatorComposite([
    AlnumValidator::class,
    new PhoneValidator
]);

$validator->validate('1a2b'); // false
$validator->getResults(); // [true, false]
```

Match one.

```php
use Windwalker\Validator\ValidatorComposite;

$validator = new ValidatorComposite([
    AlnumValidator::class,
    new PhoneValidator
])->setMode(ValidatorComposite::MODE_MATCH_ONE);

$validator->validate('1a2b'); // true
$validator->getResults(); // [true, false]
```

Use methods:

```php
$validator->validateOne($value);
$validator->validateAll($value);
```
