# Windwalker Html

Windwalker Html is a tool set using to create html elements and help us handler html string.

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/html": "~3.0"
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

## Input List

### CheckboxList

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

### RadioList

Same as Checkboxes, but the input type will be `type="radio"`

## Enumeration List

### UL List

``` php
use Windwalker\Html\Enum\ListItem;
use Windwalker\Html\Enum\UList;

echo new UList([
    new ListItem('Foo'),
    new ListItem('Bar', ['class' => 'baz']),
]);

// OR

echo (new UList)
    ->item('new ListItem('Foo'))
    ->item('Bar', ['class' => 'baz']);
```

Output

``` html
<ul>
    <li>Foo</li>
    <li class="baz">Bar</li>
</ul>
```

### OL List

``` php
echo (new OList)
    ->item('new ListItem('Foo'))
    ->item('Bar', ['class' => 'baz']);
```

Output

``` html
<ol>
    <li>Foo</li>
    <li class="baz">Bar</li>
</ol>
```

### Description List

``` php
use Windwalker\Html\Enum\DList;
use Windwalker\Html\Enum\DListDescription;
use Windwalker\Html\Enum\DListTitle;

echo (new DList)
    ->addDescription('Foo', 'foo desc')
    ->addDescription('Bar', 'bar desc');

// OR

echo (new DList)
    ->title('Foo')->desc('foo desc')
    ->title('Bar')->desc('bar desc');

// OR

echo (new DList)
    ->item(new DListTitle('Foo'))->item(new DListDescription('foo desc'))
    ->item(new DListTitle('Bar'))->item(new DListDescription('bar desc'));
```

Output

``` html
<dl>
    <dt>Foo</dt>
    <dd>foo desc</dd>

    <dt>Bar</dt>
    <dd>bar desc</dd>
</dl>
```

## Form Wrapper

``` php
use Windwalker\Html\Form\FormWrapper;
use Windwalker\Html\Form\InputElement;

echo FormWrapper::create([
    new InputElement('hidden', 'id'),
    new InputElement('text', 'title'),
], ['action' => 'http://foo.com']);
```

Output:

``` html
<form action="http://foo.com">
    <input type="hidden" name="id" value="" />
    <input type="text" name="title" value="" />
</form>
```

Use `start()` and `end()`

``` php
echo FormWrapper::start('my-form', 'post', 'http://foo.com', FormWrapper::ENCTYPE_FORM_DATA, ['id' => 'admin-form']);
    // Echo inputs
echo FormWrapper::end();
```

Output:

``` html
<form name="my-form" id="admin-form" method="post" action="http://foo.com" enctype="multipart/form-data">
    <!-- inputs -->
</form>
```

Add CSRF token automatically:

``` php
$token = MySession::getFormToken();

FormWrapper::setTokenHandler(function () use ($token)
{
    return new InputElement('hidden', $token, 1);
});

echo FormWrapper::start('my-form', 'post', 'http://foo.com', FormWrapper::ENCTYPE_FORM_DATA, ['id' => 'admin-form']);
echo FormWrapper::end();
```

Output:

``` html
<form name="my-form" id="admin-form" method="post" action="http://foo.com" enctype="multipart/form-data">
    <!-- inputs -->
    <input type="hidden" name="e6900955e2cb8d2503f663e85eb2e7e9" value="1" />
</form>
```

## Table Generator

### Grid

Grid is a HTML table generator, see this example:

``` php
use Windwalker\Html\Grid\Grid;

$grid = new Grid(['class' => 'table table-bordered']);

// Pre-set table columns and give them a name.
$grid->setColumns(['a', 'b', 'c']);

// Create first TR row, set it as <thead>
$grid->addRow(['class' => 'head'], Grid::ROW_HEAD);

// Set <th> value
$grid->setRowCell('a', 'A');
$grid->setRowCell('b', 'B');
$grid->setRowCell('c', 'C');

// Loop 3 rows
foreach (range(1, 3) as $i)
{
    // Add a TR with class row-x
    $grid->addRow(['class' => 'row-' . $i]);

    // Set every <td> value
    $grid->setRowCell('a', 'a1');
    $grid->setRowCell('b', 'b1');
    $grid->setRowCell('c', 'c1');
}

// Add <tfoot>
$grid->addRow(['class' => 'foot'], Grid::ROW_FOOT);
$grid->setRowCell('a', 'Table footer', ['colspan' => 3]);

echo $grid;
```

Output:

``` html
<table class="table table-bordered">
    <thead>
        <tr class="head">
            <th>A</th>
            <th>B</th>
            <th>C</th>
        </tr>
    </thead>
    <tbody>
        <tr class="row-1">
            <td>a1</td>
            <td>b1</td>
            <td>c1</td>
        </tr>
        <tr class="row-2">
            <td>a1</td>
            <td>b1</td>
            <td>c1</td>
        </tr>
        <tr class="row-3">
            <td>a1</td>
            <td>b1</td>
            <td>c1</td>
        </tr>
    </tbody>
    <tfoot>
        <tr class="foot">
            <td colspan="3">Table footer</td>
        </tr>
    </tfoot>
</table>
```

<table class="table table-bordered">
    <thead>
        <tr class="head">
            <th>A</th>
            <th>B</th>
            <th>C</th>
        </tr>
    </thead>
    <tbody>
        <tr class="row-1">
            <td>a1</td>
            <td>b1</td>
            <td>c1</td>
        </tr>
        <tr class="row-2">
            <td>a1</td>
            <td>b1</td>
            <td>c1</td>
        </tr>
        <tr class="row-3">
            <td>a1</td>
            <td>b1</td>
            <td>c1</td>
        </tr>
    </tbody>
    <tfoot>
        <tr class="foot">
            <td colspan="3">Table footer</td>
        </tr>
    </tfoot>
</table>

### KeyValue Grid

`KeyValueGrid` provides simple key-value table to show data similar to description list.

``` php
use Windwalker\Html\Grid\KeyValueGrid;

$items = [
    'Foo' => 'foo value',
    'Bar' => 'bar value',
    'Yoo' => 'yoo value',
];

$grid = new KeyValueGrid;

$grid->addHeader('Key', 'Value');

foreach ($items as $key => $value)
{
    $grid->addItem($key, $value);
}

echo $grid;
```

<table>
    <thead>
        <tr>
            <th>Key</th>
            <th>Value</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Foo</td>
            <td>foo value</td>
        </tr>
        <tr>
            <td>Bar</td>
            <td>bar value</td>
        </tr>
        <tr>
            <td>Yoo</td>
            <td>yoo value</td>
        </tr>
    </tbody>
</table>

Add row title:

``` php
$items = [
    'Foo' => 'foo value',
    'Bar' => 'bar value',
    'Yoo' => 'yoo value',
];

$grid = new KeyValueGrid;

$grid->addHeader('Key', 'Value');

// Add a row title
$grid->addTitle('This is a subtitle');

foreach ($items as $key => $value)
{
    $grid->addItem($key, $value);
}

// Add a row title
$grid->addTitle('This is another subtitle');

foreach ($items as $key => $value)
{
    $grid->addItem($key, $value);
}

echo $grid;
```

<table>
    <thead>
        <tr>
            <th>Key</th>
            <th>Value</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2">This is a subtitle</td>
        </tr>
        <tr>
            <td>Foo</td>
            <td>foo value</td>
        </tr>
        <tr>
            <td>Bar</td>
            <td>bar value</td>
        </tr>
        <tr>
            <td>Yoo</td>
            <td>yoo value</td>
        </tr>
        <tr>
            <td colspan="2">This is another subtitle</td>
        </tr>
        <tr>
            <td>Foo</td>
            <td>foo value</td>
        </tr>
        <tr>
            <td>Bar</td>
            <td>bar value</td>
        </tr>
        <tr>
            <td>Yoo</td>
            <td>yoo value</td>
        </tr>
    </tbody>
</table>

Use `addItems()` or `configure()` to batch add items.

``` php
$grid->addItems($items);

// Or run a callback for every item
$grid->configure($items, function (KeyValueGrid $grid, $key, $value)
{
    if ($value)
    {
        $grid->addItem($key, $value);
    }
});
```

## Media Elements

### Audio

``` php
echo (new Audio)
    ->controls(true)
    ->autoplay(true)
    ->loop(true)
    ->setNoSupportHint('Your browser do not support this format')
    ->addMp3Source('http://foo.com/bar.mp3')
    ->addOggSource('http://foo.com/bar.ogg')
    ->addWavSource('http://foo.com/bar.wav');
```

Output:

``` html

<audio controls autoplay loop>
    <source src="http://foo.com/bar.mp3" type="audio/mpeg" />
    <source src="http://foo.com/bar.ogg" type="audio/ogg" />
    <source src="http://foo.com/bar.wav" type="audio/wav" />
    Your browser do not support this format
</audio>
```

See [HTML5 Audio Tag](http://www.w3schools.com/html/html5_audio.asp) and [Tag Source](http://www.w3schools.com/tags/tag_source.asp)

### Video

``` php
echo (new Video)
    ->controls(true)
    ->autoplay(true)
    ->loop(true)
    ->preload(true)
    ->poster('http://foo.com/cover.jpg')
    ->setNoSupportHint('Your browser do not support this format')
    ->setMainSource('http://foo.com/bar.mp4')
    ->addMp4Source('http://foo.com/bar.mp4')
    ->addOggSource('http://foo.com/bar.ogg')
    ->addWebMSource('http://foo.com/bar.webm');
```

Output:

``` html

<video controls autoplay loop preload poster="http://foo.com/cover.jpg" src="http://foo.com/bar.mp4">
    <source src="http://foo.com/bar.mp4" type="video/mp4" />
    <source src="http://foo.com/bar.ogg" type="video/ogg" />
    <source src="http://foo.com/bar.webm" type="video/webm" />
    Your browser do not support this format
</video>
```

See [HTML5 Video Tag](http://www.w3schools.com/html/html5_video.asp) and [Tag Source](http://www.w3schools.com/tags/tag_source.asp)

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
use Windwalker\Html\Helper\HtmlHelper;

$option = array(
    'url' => 'http://foo.com',
    'foo' => array('bar', 'yoo')
);

echo $option = HtmlHelper::getJSOBject($option);
```

Result

```
{
    url: "http://foo.com",
    foo: ["bar", "yoo"]
}
```

Add `\\` before a value that this method will not quote it as string.

``` php
$option = array(
    'callback' => '\\function () { }'
);

echo $option = HtmlHelper::getJSOBject($option);
```

Result

``` javascript
{
    callback: function () { }
}
```

## XmlHelper

`XmlHelper` using on get attributes of `SimpleXmlElement`.

### Get Attributes

``` php
use Windwalker\Dom\SimpleXml\XmlHelper;

$xml = <<<XML
<root>
    <field name="foo" type="bar" readonly="true">
        <option></option>
    </field>
</root>
XML;

$xml = simple_xml_load_string($xml);

$element = $xml->xpath('field');

$name = XmlHelper::getAttribute($element, 'name'); // result: foo

// Same as get()
$name = XmlHelper::get($element, 'name'); // result: foo
```

### Get Boolean

`getBool()` can help us convert some string link `true`, `1`, `yes` to boolean `TRUE` and `no`, `false`, `disabled`, `null`, `none`, `0` string to booleand `FALSE`.

``` php
$bool = XmlHelper::getBool($element, 'readonly'); // result: (boolean) TRUE
```

### Get False

Just an alias of `getBool()` but FALSE will return `TRUE`.

### Set Default

If this attribute not exists, use this value as default, or we use original value from xml.

``` php
XmlHelper::def($element, 'class', 'input');
```
