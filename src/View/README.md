# Windwalker View

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/view": "~2.0"
    }
}
```

## Create A Simple View

### AbstractView

`AbstractView` is very simple, it only need a render method to render what you want.

``` php
use Windwalker\View\AbstractView;

class MyView extends AbstractView
{
    public function render()
    {
        $tmpl = <<<TMPL
# The is a Markdown Article

Hello %s~~~!
TMPL;

        return MyMarkdown::render(sprintf($tmpl, $this->data['foo']));
    }
}

// Create view and set data
$view = new MyView;

$view->set('foo', 'World');

$view->render();
```

The Result will be:

``` html
<h1>The is a Markdown Article</h1>

Hello World~~~!
```

### SimpleHtmlView

`SimpleHtmlView` can set a php file to render:

``` php
use Windwalker\View\SimpleHtmlView;

class MyHtmlView extends SimpleHtmlView
{
    public function prepare($data)
    {
        // Format dome data
        $data['time'] = $data['time']->format('Y-m-d H:i:s');

        $data['link'] = '/flower/' . OutputFilter::stringUrlSafe($data['name']) . '.html';
    }
}

$view = new MyHtmlView;

$view->set('time', new DateTime);
$view->set('name', $name);

$view->setLayout('/path/to/template.php')->render();
```

The template file:

``` php
<?php

$time = $data['time'];
?>
<p>
    Now is: <?php echo $this->escape($time); ?>

    I'm:
    <a href="<?php echo $data['link'] ?>">
        <?php echo $data['name']; ?>
    </a>
</p>
```

## HtmlView

`HtmlView` is more powerful than `SimpleHtmlView`, we can set [Renderer](https://github.com/ventoviro/windwalker-renderer)
 as a render engine into it, and find template in several paths.

``` php
use Windwalker\View\HtmlView;

$paths = new SplPriorityQueue;
$paths->insert('path/of/system', 300);
$paths->insert('path/of/theme', 500);

$data = array(
    'time' => new DateTime
);

$view = new HtmlView($data, new PhpRenderer($paths));

$view->setLayout('foo')->render(); // Will find foo.php in every paths.
```

See also: [Windwalker Renderer](https://github.com/ventoviro/windwalker-renderer)

### Extends It

``` php
use Windwalker\View\HtmlView;
use Windwalker\Renderer\BladeRenderer;

// A Blade View
class BladeHtmlView extends HtmlView
{
    public function __construct($data = array(), BladeRenderer $renderer = null)
    {
        $renderer = $renderer ? : new BladeRenderer('default/path', array('cache_path' => 'cache/path'))

        parent::__construct($data, $renderer);
    }
}

// View for different MVC structures
class ArticleHtmlView extends BladeHtmlView
{
    public function prepare($data)
    {
        $data['time'] = $data['time']->format('Y-m-d H:i:s');
    }
}

$view = new MyHtmlView;

$view->['time'] = new DateTime; // Use array access

$view->setLayout('template')->render(); // Will find template.blade.php
```

### The Data object

HtmlView use `Windwalker\Data\Data` as data store, we don't need to worry about data exists or not.

``` php
<?php

$time = $data['time']; // Exists
$name = $data['name']; // Not exists, just return null.
$title = $data->title; // Also support object access.
```

See [Windwalker Data](https://github.com/ventoviro/windwalker-data)

## JsonView

JsonView use Registry as data store, we can separate different level by dot(.).

``` php
$view = new JsonView;

$view['foo.bar'] = 'baz';

$view->render();
```

The result will be:

``` json
{
    "foo": {
        "bar": "baz"
    }
}
```

