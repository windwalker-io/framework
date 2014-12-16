# Windwalker Dom

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/dom": "~2.0"
    }
}
```

## Html & Dom Builder

This is a convenience class to create XML and HTML element in an OO way.

### DomElement

DomElement and DomElements is use to create XML elements.

``` php
use Windwalker\Dom\DomElement;

$attrs = array('id' => 'foo', 'class' => 'bar');

echo $dom = (string) new DomElement('field', 'Content', $attrs);
```

Output:

``` xml
<field id="foo" class="bar">Content</field>
```

Add Children

``` php
use Windwalker\Dom\DomElement;

$attrs = array('id' => 'foo', 'class' => 'bar');

$content = array(
    new DomElement('option', 'Yes', array('value' => 1)),
    new DomElement('option', 'No', array('value' => 0))
)

echo $dom = (string) new DomElement('field', $content, $attrs);
```

The output wil be:

``` xml
<field id="foo" class="bar">
    <option value="1">Yes</option>
    <option value="0">No</option>
</field>
```

### HtmlElement

HtmlElement is use to create HTML elements, some specific tags will force to unpaired.

``` php
use Windwalker\Dom\HtmlElement;

$attrs = array(
    'class' => 'btn btn-mini',
    'onclick' => 'return fasle;'
);

$html = (string) new HtmlElement('button', 'Click', $attrs);
```

Then we will get this HTML:

``` html
<button class="btn btn-mini" onclick="return false;">Click</button>
```

#### Get Attributes by Array Access

``` php
$class = $html['class'];
```

### DomElements & HtmlElements

It is a collection of HtmlElement set.

``` php
$html = new HtmlElements(
    array(
        new HtmlElement('p', $content, $attrs),
        new HtmlElement('div', $content, $attrs),
        new HtmlElement('a', $content, $attrs)
    )
);

echo $html;
```

OR we can iterate it:

``` php
foreach ($html as $element)
{
    echo $element;
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
