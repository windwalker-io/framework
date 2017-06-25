# Windwalker Form

Windwalker Form package is a HTML form construction tools to create input components.

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/form": "~3.0"
    }
}
```

## Create A Form

Create a new form instance and add fields into it.

``` php
use Windwalker\Form\Field\TextareaField;
use Windwalker\Form\Form;
use Windwalker\Form\Field\TextField;
use Windwalker\Form\Field\PasswordField;

$form = new Form;

$form->addField(new TextField('username', 'Username'));
$form->addField(new PasswordField('password', 'Password'));
$form->addField(new TextField('email', 'Email'));
$form->addField(new TextareaField('description', 'Description'));

echo $form->renderFields();
```

Render all fields, and we get this HTML output.

``` html
<div id="username-control" class="text-field ">
    <label id="username-label" for="username">Username</label>
    <input type="text" name="username" id="username" />
</div>
<div id="password-control" class="password-field ">
    <label id="password-label" for="password">Password</label>
    <input type="password" name="password" id="password" />
</div>
<div id="email-control" class="text-field ">
    <label id="email-label" for="email">Email</label>
    <input type="text" name="email" id="email" />
</div>
<div id="description-control" class="textarea-field ">
    <label id="description-label" for="description">Description</label>
    <textarea name="description" id="description"></textarea>
</div>
```

![img](https://cloud.githubusercontent.com/assets/1639206/5066911/0566a53c-6e77-11e4-9bee-6cc2ee01de21.png)

### Use `add()`

`add()` is a simple alias of `addField()` to make Field support chaining.

``` php
$form->add('username', new TextField)
    ->label('Username')
    ->setClass('input-large')
    ->defauleValue('foo')
    ->set('placeholder', 'Enter username')
    ->required();
```

### Using XML as Configuration

``` xml
<form>
    <field
        name="username"
        type="text"
        label="Username"
        />

    <field
        name="password"
        type="password"
        label="Password"
        />
</form>
```

``` php
$form = new Form;

$form->loadFile('form.xml');
```

## Form Control

``` php
$control = 'user';

$form = new Form($control);

$form->addField(new TextField('username', 'Username'));

echo $form->renderFields();
```

The result will make name as an array.

``` html
<div id="user-username-control" class="text-field ">
    <label id="user-username-label" for="user-username">Username</label>
    <input type="text" name="user[username]" id="user-username" />
</div>
```

## Organize Fields

### Fieldset

Fieldset is a category of fields, we can filter our fields by fieldset:

``` php
$form = new Form;

$form->addField(new TextField('flower', 'Flower'), 'plant');
$form->addField(new TextField('tree', 'Tree'), 'plant');
$form->addField(new TextField('dog', 'Dog'), 'animal');
$form->addField(new TextField('cat', 'Cat'), 'animal');

// Only render Flower & Tree
echo $form->renderFields('plant');

// Only render Dog & Cat
echo $form->renderFields('animal');

// Will render all
echo $form->renderFields();
```

Use callback to wrap fieldset.

``` php
$form->fieldset('plant', function (Form $form)
{
	$form->addField(new TextField('flower', 'Flower'));
	$form->addField(new TextField('tree', 'Tree'));
});

$form->fieldset('animal', function (Form $form)
{
	$form->addField(new TextField('flower', 'Flower'));
	$form->addField(new TextField('tree', 'Tree'));
});
```

Using XML

``` xml
<form>
    <fieldset name="plant">
        <field name="flower" label="Flower"/>
        <field name="tree" label="Tree"/>
    </fieldset>

    <fieldset name="animal">
        <field name="dog" label="Dog"/>
        <field name="cat" label="Cat"/>
    </fieldset>
</form>
```

### Group

Group is like fieldset as a category of fields, but it will make name of fields to be array:

``` php
$form = new Form;

$form->addField(new TextField('flower', 'Flower'), null, 'earth');
$form->addField(new TextField('bird', 'Bird'), null, 'sky');

// The name will be name="earth[flower]"
echo $form->renderFields(null, 'plant');

// The name will be name="sky[dog]"
echo $form->renderFields(null, 'animal');
```

Now we can use fieldset and group to organize our fields.

``` php
$form = new Form;

$form->addField(new TextField('flower', 'Flower'), 'plant', 'earth');
$form->addField(new TextField('tree', 'Tree'), 'plant', 'earth');
$form->addField(new TextField('dog', 'Dog'), 'animal', 'home');
$form->addField(new TextField('cat', 'Cat'), 'animal', 'home');
$form->addField(new TextField('bird', 'Bird'), 'animal', 'sky');

// Only render Bird
echo $form->renderFields('animal', 'sky');

// Only render Dog & Cat & Bird
echo $form->renderFields('animal');

// Only render Flower & Tree
echo $form->renderFields(null, 'earth');

// Will render all
echo $form->renderFields();
```

Use callback to wrap group:

``` php
$form->group('earth', function (Form $form)
{
	$form->addField(new TextField('flower', 'Flower'));
	$form->addField(new TextField('tree', 'Tree'));
});
```

Wrap with fieldset and group:

``` php
$form->wrap('plant', 'earth', function (Form $form)
{
	$form->addField(new TextField('flower', 'Flower'));
	$form->addField(new TextField('tree', 'Tree'));
});

$form->wrap('animal', 'sky', function (Form $form)
{
	$form->addField(new TextField('bird', 'Bird'));
});
```

Using XML

``` xml
<form>
    <group name="earth">
        <fieldset name="plant">
            <field name="flower" label="Flower"/>
            <field name="tree" label="Tree"/>
        </fieldset>
    </group>

    <fieldset name="animal">
        <group name="home">
            <field name="dog" label="Dog"/>
            <field name="cat" label="Cat"/>
        </group>

        <group name="home">
            <field name="bird" label="Bird"/>
        </group>
    </fieldset>
</form>
```

Control and group supports multi-level by `/` or `.` separator:

``` php
$form = new Form('foo/bar');

$form->group('egg/bread', ...);
```

## Attributes of Fields

### Name & Label

``` php
$form->addField(new TextField('name', 'Label'));
```

### Set Attributes

You can set some attributes to field by `set()` or `setAttribute()`, this method will only allow standard
 HTML attributes inject to latest rendered result.

``` php
$form->addField(new TextField('name'))
    ->label('Label')
    ->set('id', 'my-name')
    ->set('class', 'col-md-8 form-input')
    ->set('onclick', 'return false;')
    ->set('labelClass', 'form-label') // <-- This will add to <label class="...">
    ->set('data-my-attr', 'foo'); // <-- This custom attribute will not work
```

If you want to set custom data attributes to make your inputs work with some JS frameworks (e.g. Vue, Angular, Bootstrap),
you can use `attr()` to directly set HTML attributes.

``` php
$form->addField(new TextField('name'))
    ->label('Label')
    ->set('class', 'col-md-8 form-input')
    ->attr('v-on:click', 'foo()')
    ->attr(':value', 'bar')
    ->attr('data-toggle', 'tooltip');
```

These custom directives will directly add to HTML. `attr()` method is like jQuery `attr()`, if you don't pass
second param, this method will be a getter to return attribute value.

You can also set attribute to input wrapper and label, use `controlAttr()` and `labelAttr()`.

### Required, Disabled and Readonly

``` php
$form->addField(new TextField('name', 'Label'))
    ->set('id', 'my-name')
    ->required()
    ->disabled();
```

Set to false.

``` php
$form->addField(new TextField('name', 'Label'))
    ->set('id', 'my-name')
    ->required(false)
    ->disabled(false);
```

### XML

``` xml
<field
    name="name"
    label="Label"
    id="my-name"
    required="true"
    disabled="false"
/>
```

## Filter

``` php
use Windwalker\Filter\InputFilter;

$form->addField(new TextField('id', 'ID'))
    ->addFilter(InputFilter::INTEGER);

// Prepare data
$data['id'] = '123abc';

// Bind data into form
$form->bind($data);

// Do filter
$form->filter();

$values = $form->getValues(); // Array(id = 123)
```

## Validate

``` php
$form->addField(new TextField('name', 'Name'))
	->required();

$form->addField(new TextField('email', 'Email'))
	->addValidator(new EmailValidator);

// Prepare data
$data['name'] = null;
$data['email'] = 'foo';

// Bind data into form
$form->bind($data);

// Do validate
$r = $form->validate();

$results = $form->getErrors();

var_dump($r); // bool(false)

$results[0]->getMessage(); // Field Email validate fail.
$results[1]->getMessage(); // Field Name value not allow empty.
```

## Field Types

### Select List

``` php
use Windwalker\Form\Field\ListField;
use Windwalker\Html\Option;

$selectField = new ListField(
    'flower',
    'Flower',
    array(
        new Option('', ''),
        new Option('Yes', 1),
        new Option('No', 0),
    )
);

echo $selectField->render();
```

The output is:

``` html
<div id="flower-control" class="list-field ">
    <label id="flower-label" for="flower">Flower</label>
    <select name="flower" id="flower" class="stub-flower">
        <option selected="selected"></option>
        <option value="1">Yes</option>
        <option value="0">No</option>
    </select>
</div>
```

Multiple List

``` php
$selectField->set('multiple', true);
```

### RadioList

``` php
$form->addField(new RadioList('flower', 'Flower'))
    ->addOption(new Option('Yes', 1))
    ->addOption(new Option('No', 0));
```

We can also add options in constructor:

``` php
$field = new RadioList(
    'flower',
    'Flower',
    array(
        new Option('Yes', 1),
        new Option('No', 0),
    )
);
```

## Available Fields

| Name | HTML | Description |
| ---- | ---- | ----------- |
| TextField     | `<input type="text">`     | The text input field. |
| TextareaField | `<textarea></textarea>`   | Textarea field. |
| ColorField    | `<input type="color">`    | HTML5 Color picker |
| DateField     | `<input type="date">`     | Date selector |
| DatetimeLocalField | `<input type="datetime-local">` | Datetime selector |
| MonthField    | `<input type="month">`    | Month selector |
| NumberField   | `<input type="number">`   | Number text field |
| RangeField    | `<input type="range">`    | A range bar. |
| TelField      | `<input type="tel">`      | Tel text field. |
| TimeField     |`<input type="time">`      | Time field. |
| UrlField      | `<input type="url">`      | URL text field |
| WeekField     | `<input type="week">`     | Week selector |
| EmailField    | `<input type="email">`    | The email text field. |
| HiddenField   | `<input type="hidden">`   | Hidden input. |
| PasswordField | `<input type="password">` | Password field. |
| SpacerField   | `<hr>`                    | The spacer to separate fields and fields. |
| ListField     | `<select>`                | Select list. |
| CheckboxField | `<input type="checkbox">` | Single checkbox. |
| CheckboxesField | `<input type="checkbox">` | Checkbox list. |
| RadioField    | `<input type="radio">`    | Radio list. |
| TimezoneField | `<select>`                | A timezone select list. |
| ButtonField   | `<button>`                | Create a custom button. |
| CustomHtmlField | `...`                   | Create any HTML you want with `set('content', ...)`. |

See every fields' `prepare()` method you will know available attributes.

## Custom Fields

### Custom TextField.

``` php
namespace MyCode\Fields;

class FooField extends TextField
{
    protected $type = 'foo';

    public function prepare(&$attrs)
    {
        parent::prepare($attrs);

        $attrs['bar'] = $this->getAttribute('bar');
    }
}
```

For XML

``` php
\Windwalker\Form\FieldHelper::addNamespace('MyCode\Filter');
```


``` xml
<field
    name="foo"
    type="foo"
/>
```

### Custom List field

``` php
namespace MyCode\Fields;

class UsersField extends ListField
{
    protected function prepareOptions()
    {
        $users = Database::getList('SELECT * FROM users');

        $options = array();

        foreach ($users as $user)
        {
            $options[] = new Option($user->name, $user->id);
        }

        return $options;
    }
}
```

## Custom Filter

``` php
namespace MyCode\Filter;

class MyFilter implements FilterInterface
{
	public function clean($text)
	{
		return my_filter_function($text);
	}
}

(new TextField('foo', 'Foo'))
    ->setFilter(new MyFilter);
```

For XML

``` php
\Windwalker\Form\FilterHelper::addNamespace('MyCode\Filter');
```

``` xml
<field
    name="foo"
    type="text"
    filter="my"
/>
```

## Custom Validator

``` php
namespace MyCode\Validator;

use Windwalker\Validator\AbstractValidator;

class MyValidator extends AbstractValidator
{
    protected function test($value)
    {
        return (bool) $value;
    }
}

(new TextField('foo', 'Foo'))
    ->setFilter(new MyValidator);
```

For XML

``` php
\Windwalker\Form\ValidatorHelper::addNamespace('MyCode\Validator');
```

``` xml
<field
    name="foo"
    type="text"
    filter="my"
/>
```

## Form Renderer

If you wish to override core HTML output, use `FormRendererInterface` to render your fields.

``` php
class MyFormRenderer implements \Windwalker\Form\Renderer\FormRendererInterface
{
	public function renderField(AbstractField $field, array $attribs = array())
	{
	    $attribs['class'] .= ' my-custom-style';

		return new \Windwalker\Dom\HtmlElement('div', array(
			$field->renderLabel(), // Will load $this->renderLabel()
			$field->renderInput(), // Will load $this->renderInput()
		), $attribs);
	}

	public function renderLabel(AbstractField $field, array $attribs = array())
	{
		return ...; // Render different field types
	}

	public function renderInput(AbstractField $field, array $attribs = array())
	{
		return ...; // Render different field types
	}
}

$form->setRenderer(new MyFormRenderer);
```
