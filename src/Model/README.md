# Windwalker Model

Windwalker Model provides an abstract interface to build your own model logic.

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/model": "~2.0"
    }
}
```

## Create Model

Extends the AbstractModel to create your own model.

``` php
use Windwalker\Model\AbstractModel

class MyModel extends AbstractModel
{
    public function getItem()
    {
        return new stdClass;
    }
}

$model = new MyModel;

$item = $model->getItem();
```

## Database Model

Implements the `DatabaseModelInterface`, we will able to get and set a DB object to access database.

Windwalker Model do not dependency to any Database package, you can integrate your favorite data source to get data.

``` php
use Windwalker\Model\AbstractModel
use Windwalker\Model\DatabaseModelInterface;

class MyModel extends AbstractModel implements DatabaseModelInterface
{
    protected $db;

    public function __construct($db, Registry $state = null)
    {
        $this->db = $db;

        parent::__construct($state);
    }

    public function getDb()
    {
        return $this->db;
    }

    public function setDb($db)
    {
        $this->db = $db;
    }

    public function getList()
    {
        $this->db->setQuery('select * from users');

        return $this->db->loadAll();
    }
}
```

## Model State

Model maintains their own state that we can change this state to get different data.

``` php
class MyModel extends AbstractModel implements DatabaseModelInterface
{
    // ...

    public function getUsers()
    {
        $published = $this->state->get('where.published', 1);

        $ordering  = $this->state->get('list.ordering', 'id');
        $direction = $this->state->get('list.direction', 'ASC');

        $sql = "SELECT * FROM users " .
            " WHERE published = " . $published .
            " ORDER BY " . $ordering . " " . $direction;

        try
        {
            return $this->db->setQuery($sql)->loadAll();
        }
        catch (\Exception $e)
        {
            $this->state->set('error', $e->getMessage());

            return false;
        }
    }
}

$model = new MyModel;

$state = $model->getState();

// Let's change model state
$state->set('where.published', 1);
$state->set('list.ordering', 'birth');
$state->set('list.direction', 'DESC');

$users = $model->getUsers();

if (!$users)
{
    $error = $state->get('error');
}
```

### Simple Way to Access State

Using `get()` and `set()`

``` php
// Same as getState()->get();
$model->get('where.author', 5);

// Same as getState()->set();
$model->set('list.ordering', 'RAND()');
```

### State ArrayAccess

``` php
// Same as getState()->get();
$data = $model['list.ordering'];

// Same as getState()->set();
$model['list.ordering'] = 'created_time';
```


