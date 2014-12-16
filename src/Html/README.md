# Windwalker Html

Windwalker Html is a tool set using to create html elements and help us handler html string.

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/html": "~2.0"
    }
}
```

## Select List

``` php
use Windwalker\Html\Select\SelectList;
use Windwalker\Html\Option;

$select = new SelectList(
    'form[timezone]',
    array(
        new Option('Asia - Tokyo', 'Asia/Tokyo', array('class' => 'opt')),
        new Option('Asia - Taipei', 'Asia/Taipei'),
        new Option('Europe - Paris', 'Asia/Paris'),
        new Option('UTC', 'UTC'),
    ),
    array('class' => 'input-select'),
    'UTC',
    false
);

echo $select;
```

The result:

``` html
<select class="input-select" name="form[timezone]">
	<option class="opt" value="Asia/Tokyo">Asia - Tokyo</option>
	<option value="Asia/Taipei">Asia - Taipei</option>
	<option value="Asia/Paris">Europe - Paris</option>
	<option value="UTC" selected="selected">UTC</option>
</select>
```

### Group Select

Use two level array to make options grouped.

``` php
use Windwalker\Html\Select\CheckboxList;

$select = new SelectList(
    'form[timezone]',
    array(
        'Asia' => array(
            new Option('Tokyo', 'Asia/Tokyo', array('class' => 'opt')),
            new Option('Taipei', 'Asia/Taipei')
        ),
        'Europe' => array(
            new Option('Europe - Paris', 'Asia/Paris')
        )
        ,
        new Option('UTC', 'UTC'),
    ),
    array('class' => 'input-select'),
    'UTC',
    false
);

echo $select;
```

The result

``` html
<select class="input-select" name="form[timezone]">
	<optgroup label="Asia">
		<option class="opt" value="Asia/Tokyo">Tokyo</option>
		<option value="Asia/Taipei">Taipei</option>
	</optgroup>

	<optgroup label="Europe">
		<option value="Asia/Paris">Europe - Paris</option>
	</optgroup>

	<option value="UTC" selected="selected">UTC</option>
</select>
```

## CheckboxList

``` php
$select = new CheckboxList(
    'form[timezone]',
    array(
        new Option('Asia - Tokyo', 'Asia/Tokyo', array('class' => 'opt')),
        new Option('Asia - Taipei', 'Asia/Taipei'),
        new Option('Europe - Paris', 'Asia/Paris'),
        new Option('UTC', 'UTC'),
    ),
    array('class' => 'input-select'),
    'UTC',
    false
);

echo $select;
```

The result

``` html
<span class="checkbox-inputs input-select">
	<input class="opt" value="Asia/Tokyo" type="checkbox" name="form[timezone][]" id="form-timezone-asia-tokyo" />
	<label class="opt" id="form-timezone-asia-tokyo-label" for="form-timezone-asia-tokyo">Asia - Tokyo</label>

	<input value="Asia/Taipei" type="checkbox" name="form[timezone][]" id="form-timezone-asia-taipei" />
	<label id="form-timezone-asia-taipei-label" for="form-timezone-asia-taipei">Asia - Taipei</label>

	<input value="Asia/Paris" type="checkbox" name="form[timezone][]" id="form-timezone-asia-paris" />
	<label id="form-timezone-asia-paris-label" for="form-timezone-asia-paris">Europe - Paris</label>

	<input value="UTC" checked="checked" type="checkbox" name="form[timezone][]" id="form-timezone-utc" />
	<label id="form-timezone-utc-label" for="form-timezone-utc">UTC</label>
</span>
```

If you want to use `div` to wrap all inputs instead `span`, set tag name to object.

``` php
$select->setName('div');
```

## RadioList

Same as Checkboxes, but the input type will be `type="radio"`

## HtmlHelper

### Repair Tags

We can using `repair()` method to repair unpaired tags by `php tidy`, if tidy extension not exists, will using simple tag close function to fix it.

``` php
$html = '<p>foo</i>';

$html = \Windwalker\Html\Helper\HtmlHelper::repair($html);

echo $html; // <p>foo</p>
```

### Get JS Object

This method convert a nested array or object to JSON format that you can inject it to JS code.

``` php
$option = array(
    'url' => 'http://foo.com',
    'foo' => array('bar', 'yoo')
);

echo $option = \Windwalker\Html\Helper\HtmlHelper::getJSOBject($option);
```

Result

```
{
    "url" : "http://foo.com",
    "foo" : ["bar", "yoo"]
}
```

## More Builder

We'll add more builder object after version 2.1.
