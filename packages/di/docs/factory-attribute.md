> Draft: to be merged into the official docs.

## `#[Factory]` Attribute

### Introduction

When a value registered in the `Container` is a callable, `Container` normally calls it once and uses the
returned value directly as the resolved instance. This is inconvenient if the callable itself wants to return
*another* closure that should be autowired by the container (for example, a closure whose typed parameters
should be resolved from the container, or that should go through member-attribute resolution).

`#[Factory]` marks a closure as a factory producer: when the outer callable returns a `Closure` tagged with
`#[Factory]`, the container treats that inner closure as the real factory and resolves it through
`Container::resolve()`/`newInstance()` again instead of using it as-is. This lets a single registration produce
a nested, fully autowired factory closure.

### Usage

Return a `#[Factory]`-tagged closure from the callable you pass to `newInstance()` (or register with `set()`/
`bind()`):

```php
use Windwalker\DI\Attributes\Factory;
use Windwalker\DI\Container;

$container = new Container();

$object = $container->newInstance(
    static fn() => #[Factory]
        static fn(SomeService $service): Greeter => new Greeter($service)
);

// $object is a `Greeter` instance; `SomeService` was autowired by the container
// when the inner closure ran through `Container::resolve()`.
```

The same pattern works when registering a service definition:

```php
$container->set(
    Greeter::class,
    static fn() => #[Factory]
        static fn(SomeService $service): Greeter => new Greeter($service)
);

$greeter = $container->get(Greeter::class);
```

### Behaviors and limitations

- `#[Factory]` only has an effect when it is attached to the **returned** `Closure`, not to the outer callable.
  The outer callable is still invoked via `Container::call()` as normal.
- It is detected via `Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD`, so it can also be attached to a
  named method that returns a closure, not only to closures/arrow functions themselves.
- If the outer callable does not return a `Closure`, or the returned `Closure` has no `#[Factory]` attribute,
  the returned value is used directly (existing behavior); it must then be an `object`.
- Resolving the inner closure goes through the same `$args`/`DIOptions` passed to the outer `newInstance()` call,
  so container options (e.g. autowiring, attribute handling) apply consistently to both layers.
