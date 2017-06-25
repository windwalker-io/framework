# Windwalker Event

Windwalker Event Package provides an interface to create event systems.

## Installation via Composer

Add this to the require block in your `composer.json`.

``` json
{
    "require": {
        "windwalker/event": "~3.0"
    }
}
```

## Getting Started

Create an event object named `onBeforeContentSave`, and set some arguments.

``` php
use Windwalker\Event\Event;

$event = new Event('onBeforeContentSave');

$content = new stdClass;

$event->setArgument('title', 'My content');
$event->setArgument('content', $content);
```

Create your listener:

``` php
use Windwalker\Event\EventInterface;

class ContentListener
{
    public function onBeforeContentSave(EventInterface $event)
    {
        $event->getArgument('content')->title = $event->getArgument('title'); 
    }
    
     public function onAfterContentSave(EventInterface $event)
    {
        // Do something
    }
}
```

Add listener to Dispatcher:

``` php
use Windwalker\Event\Dispatcher;

$dispatcher = new Dispatcher;

$dispatcher->addListener(new ContentListener);
```

Then we trigger the event we created:

``` php
// Trigger the onBeforeContentSave
$dispatcher->triggerEvent($event);

// ContentListener::onBeforeContentSave will set title into $content object.
$content->title === 'My content';
```

If a method name in listener equals to event name, Dispatcher will run this method and inject Event into this method.
Then we can do many things we want.

## Listeners

There can be two types of listeners, using class or closure.

### Class Listeners

Using class, just new an instance

``` php
use Windwalker\Event\ListenerPriority;

$dispatcher->addListener(new ContentListener);
```

You may provides priority for every methods.

``` php
// Add priorities
$dispatcher->addListener(
    new ContentListener,
    array(
        'onBeforeContentSave' => ListenerPriority::LOW,
        'onAfterContentSave' => ListenerPriority::HIGH
    )
);

// Or using an inner method to get all methods
$dispatcher->addListener(new ContentListener, ContentListener::getPriorities());
```

### Closure Listeners

If using closure, you must provide the priority and an event name to listen.

``` php
$dispatcher->addListener(
    function (EventInterface $event)
    {
        // Do something
    }, 
    array('onContentSave' => ListenerPriority::NORMAL)
);
```

Or use `listen()` method.

``` php
$dispatcher->listen('onContentSave', function (EventInterface $event)
{
    // Do something
}, ListenerPriority::NORMAL);
```

## Dispatcher

### Trigger An Event Object

This is the most normal way to trigger an event.

``` php
$event = new Event('onFlowerBloom');

$event->setArgument('flower', 'sakura');

$dispatcher->triggerEvent($event);
```

Add arguments when triggering event, the arguments will merge with previous arguments you set.

``` php
$args = array(
    'foo' => 'bar'
);

$dispatcher->triggerEvent($event, $args);
```

### Add An Event Then Trigger It Later

We can add an event into Dispatcher, then use event name to raise it laster.

``` php
$event = new Event('onFlowerBloom');

$event->setArgument('flower', 'sakura);

$dispatcher->addEvent($event);

// Nothing happen

$dispatcher->triggerEvent('onFlowerBloom');
```

### Trigger A New Event Instantly

We don't need create event first, just trigger a string as event name, Dispatcher will create an event instantly.

``` php
$args = array(
    'foo' => 'bar'
);

$dispatcher->triggerEvent('onCloudMoving', $args);
```

## Stopping Event

If you stop an event, the next listeners in the queue won't be called.

``` php
class ContentListener
{
    public function onBeforeContentSave(EventInterface $event)
    {
        // Stopping the Event propagation.
        $event->stop();
    }
}
```

## Make Arguments Referenced

``` php
$dispatcher->triggerEvent('onSomeEvent', array(
    'foo' => &$foo,
    'bar' => &$bar
));
```

## Observable Pattern

Most of time we use event system to inject some logic before & after our main logic:

``` php
use Windwalker\Event\DispatcherAwareInterface;

class Application implements DispatcherAwareInterface
{
    const BEFORE_INIT_EVENT = 'onBeforeSystemInit';
    const AFTER_INIT_EVENT  = 'onAfterSystemInit';
    const BEFORE_RUN_EVENT  = 'onBeforeSystemRun';
    const AFTER_RUN_EVENT   = 'onAfterSystemRun';

    protected $dispatcher = null;

    public function init()
    {
        $this->triggerEvent(static::BEFORE_INIT_EVENT);
        
        // ...
        
        $this->triggerEvent(static::AFTER_INIT_EVENT);
    }

    public function execute()
    {
        $this->triggerEvent(static::BEFORE_RUN_EVENT);
                
        // ...
        
        $this->triggerEvent(static::AFTER_RUN_EVENT);
    }

    public function triggerEvent($event)
    {
        $this->dispatcher->triggerEvent($event);
    }

    public function setDispatcher($dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }
}
```

In PHP 5.4 or higher, you can use `DispatcherAwareTrait`.

``` php
use Windwalker\Event\DispatcherAwareInterface;
use Windwalker\Event\DispatcherAwareTrait;

class Application implements DispatcherAwareInterface
{
    use DispatcherAwareTrait;
    
    // ...
}
```
