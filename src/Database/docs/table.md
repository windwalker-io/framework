# Table Command

Get Table Command.

``` php
$table = $db->getTable('#__articles');
```

## create()

Create a new table.

``` php
$table = $db->getTable('#__articles');

// First arg true to add "IF NOT EXISTS"
$table->create(true);
```

##
