# Windwalker Controller

The Windwalker Controller package is a simple interface to control some business logic, id didn't dependency to any other packages.
 You may integrate it to any systems.

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/controller": "~2.0"
    }
}
```

## Create Your Controller

``` php
use Windwalker\Controller\Controller;

class IndexController extends AbstractController
{
    public function execute()
    {
        return 'Index';
    }
}

$controller = new IndexController;

$output = $contorller->execute();
```

Windwakler Controller is a "Single Action Controller", follows single responsibility principle,
every controller just maintain one task(action). It is inspired from [Joomla New MVC](http://magazine.joomla.org/issues/issue-nov-2013/item/1580-new-mvc-for-joomla-cms). You can create
`IndexController`, `CreateController`, `UpdateController` and `DeleteController` for [CRUD](http://en.wikipedia.org/wiki/Create,_read,_update_and_delete).

## Using Input and Application

By default, controller maintains an input and an application object. We can set it when construct:

``` php
use Windwalker\Controller\Controller;

class IndexController extends AbstractController
{
    public function execute()
    {
        // Get params from http request
        $method = $this->input->get('_method');

        $this->app->redirect('...');

        return true;
    }
}

$input = new Input;
$app = new WebApplication;

$controller = new IndexController($input, $app);

$output = $contorller->execute();
```

It didn't dependency to Windwalker self, you can push other framework's input and application into it:

``` php
$input = new Request;
$app = new HttpKernel;

$controller = new IndexController($input, $app);

$output = $contorller->execute();
```

## HMVC

Using [HMVC](http://en.wikipedia.org/wiki/Hierarchical_model%E2%80%93view%E2%80%93controller) in Windwalker controller is very easy:

``` php
class IndexController extends AbstractController
{
    public function execute()
    {
        $this->input->set('id', 123);

        $foo = new FooController($this->input, $this->app);

        echo $foo->execute();

        return true;
    }
}
```

## Multi Action Controller

If you are familiar to common multiple action pattern, use `AbstractMultiActionController`:

``` php
use Windwalker\Controller\AbstractMultiActionController;

class ArticleController extends AbstractMultiActionController
{
    public function indexAction()
    {}

    public function saveAction($id = null, $data = array())
    {}

    public function deleteAction($id = null)
    {}
}

$controller = new ArticleController;

// Will call saveAction()
$controller->setActionName('save')
    ->setArguments(array(5, $data))
    ->execute();
```

If no action set, will call `doExecute()` instead, but you still need to override `doExecute()` first.
