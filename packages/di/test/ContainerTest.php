<?php

declare(strict_types=1);

namespace Windwalker\DI\Test;

use Attribute;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use SplPriorityQueue;
use SplQueue;
use SplStack;
use Windwalker\Data\Collection;
use Windwalker\DI\Attributes\AttributeType;
use Windwalker\DI\Attributes\Inject;
use Windwalker\DI\Container;
use Windwalker\DI\Definition\StoreDefinition;
use Windwalker\DI\Exception\DefinitionException;
use Windwalker\DI\Exception\DefinitionResolveException;
use Windwalker\DI\Exception\DependencyResolutionException;
use Windwalker\DI\Parameters;
use Windwalker\DI\Test\Injection\Attrs\ToUpper;
use Windwalker\DI\Test\Injection\HelloInner;
use Windwalker\DI\Test\Injection\HelloWrapper;
use Windwalker\DI\Test\Injection\StubInject;
use Windwalker\DI\Test\Injection\StubService;
use Windwalker\DI\Test\Mock\Bar;
use Windwalker\DI\Test\Mock\Bar2;
use Windwalker\DI\Test\Mock\Foo;
use Windwalker\DI\Test\Mock\FooEnum;
use Windwalker\DI\Test\Mock\IntersectionTypeStub;
use Windwalker\DI\Test\Mock\StubStack;
use Windwalker\DI\Test\Mock\UnionTypeStub;
use Windwalker\DI\Test\Mock\WithEnum;
use Windwalker\DI\Test\Mock\WithVariadic;
use Windwalker\DI\Test\Stub\StubInstantService;
use Windwalker\DI\Test\Stub\StubLangCode;
use Windwalker\DI\Test\Stub\StubLangEnum;
use Windwalker\DI\Test\Stub\StubLazy;
use Windwalker\DI\Test\Stub\StubServiceProvider;
use Windwalker\Scalars\ArrayObject;
use Windwalker\Scalars\StringObject;
use Windwalker\Test\Traits\BaseAssertionTrait;
use Windwalker\Utilities\Reflection\ReflectAccessor;
use Windwalker\Utilities\TypeCast;

use function Windwalker\DI\create;
use function Windwalker\raw;
use function Windwalker\ref;
use function Windwalker\str;

/**
 * The ContainerTest class.
 */
class ContainerTest extends TestCase
{
    use BaseAssertionTrait;

    protected Container $instance;

    #[Test]
    public function registersDefinitionsWithSharedAndTransientLifetimes(): void
    {
        $container = new Container();

        // Not share, not protect
        $container->set(
            'flower',
            fn() => new \ArrayObject()
        );

        self::assertInstanceOf(\ArrayObject::class, $container->get('flower'));

        self::assertNotSame($container->get('flower'), $container->get('flower'));

        // Share, not protect
        $container->set(
            'sakura',
            fn() => new SplPriorityQueue(),
            Container::SHARED
        );

        self::assertInstanceOf(SplPriorityQueue::class, $container->get('sakura'));

        self::assertSame($container->get('sakura'), $container->get('sakura'));

        // Override it
        $container->set(
            'sakura',
            fn() => new SplStack(),
            Container::SHARED
        );

        // Should be override
        self::assertInstanceOf(SplStack::class, $container->get('sakura'));
    }

    #[Test]
    public function preventsOverridingProtectedDefinitions(): void
    {
        $this->expectException(DefinitionException::class);

        $container = new Container();

        // Share, Protect
        $container->set(
            'olive',
            fn() => new SplStack(),
            Container::SHARED | Container::PROTECTED
        );

        self::assertInstanceOf(SplStack::class, $container->get('olive'));

        $container->set(
            'olive',
            fn() => new SplQueue(),
            Container::SHARED
        );

        // Should not be override
        self::assertInstanceOf(SplStack::class, $container->get('olive'));
    }

    #[Test]
    public function resolvesDirectDefinitionsBeforeAliases(): void
    {
        $container = new Container();

        $container->share('Flower', 'Sakura')->alias('FlowerAlias', 'Flower');

        $container->share('FlowerAlias', 'Olive');

        self::assertEquals('Olive', $container->get('FlowerAlias'));
    }

    /**
     * testGetFromParent
     *
     * @return  void
     */
    #[Test]
    public function resolvesDefinitionsFromParentContainers(): void
    {
        $container = new Container($this->instance);

        self::assertEquals('World', $container->get('Hello'));
    }

    /**
     * @covers \Windwalker\DI\Container::protect
     */
    #[Test]
    public function protectsDefinitionsFromReplacement(): void
    {
        $this->expectException(DefinitionException::class);

        $container = new Container();

        // Share, Protect
        $container->protect(
            'olive',
            fn() => new SplStack(),
            Container::SHARED
        );

        self::assertInstanceOf(SplStack::class, $container->get('olive'));

        $container->set(
            'olive',
            fn() => new SplQueue(),
            Container::SHARED
        );

        // Should not be override
        self::assertInstanceOf(SplStack::class, $container->get('olive'));
    }

    /**
     * @covers \Windwalker\DI\Container::share
     */
    #[Test]
    public function sharesInstancesAndAllowsDefinitionReplacement(): void
    {
        $container = new Container();

        // Share, not protect
        $container->share(
            'sakura',
            fn() => new SplPriorityQueue()
        );

        self::assertInstanceOf(SplPriorityQueue::class, $container->get('sakura'));

        self::assertSame($container->get('sakura'), $container->get('sakura'));

        // Override it
        $container->set(
            'sakura',
            fn() => new SplStack(),
            Container::SHARED
        );

        // Should be override
        self::assertInstanceOf(SplStack::class, $container->get('sakura'));
    }

    /**
     * @covers \Windwalker\DI\Container::alias
     */
    #[Test]
    public function resolvesDefinitionsByAlias(): void
    {
        $this->instance->alias('foo', 'flower');

        self::assertEquals('sakura', $this->instance->get('foo'));
    }

    /**
     * Method to test exists().
     *
     * @return  void
     */
    #[Test]
    public function reportsWhetherDefinitionsExist(): void
    {
        self::assertTrue($this->instance->has('Hello'));
        self::assertFalse($this->instance->has('Wind'));
    }

    /**
     * Method to test getNewInstance().
     *
     * @return  void
     */
    #[Test]
    public function createsNewInstancesWhenRequested(): void
    {
        $container = new Container();

        $container->share(
            'flower',
            fn() => new \ArrayObject()
        );

        self::assertInstanceOf('ArrayObject', $container->get('flower'));

        self::assertNotSame($container->get('flower'), $container->get('flower', true));
    }

    #[Test]
    public function resolvesAttributedServicesAsSharedInstances(): void
    {
        $container = new Container();

        $service = $container->get(StubInstantService::class);

        self::assertInstanceOf(
            StubInstantService::class,
            $service
        );

        // Must be singleton
        self::assertSame(
            $service,
            $container->get(StubInstantService::class)
        );
    }

    #[Test]
    public function injectsAttributedServicesIntoCallables(): void
    {
        $container = new Container();

        $service = $container->call(
            function (StubInstantService $service) {
                return $service;
            }
        );

        self::assertInstanceOf(
            StubInstantService::class,
            $service
        );
    }

    /**
     * @covers \Windwalker\DI\Container::createObject
     * @covers \Windwalker\DI\Container::createSharedObject
     * @covers \Windwalker\DI\Container::newInstance
     * @throws  DefinitionException
     */
    #[Test]
    public function createsObjectsAndResolvesTheirDependencies(): void
    {
        $container = new Container(null, Container::AUTO_WIRE);

        $foo = $container->createObject(Foo::class);

        self::assertInstanceOf(Foo::class, $foo);
        self::assertInstanceOf(Bar::class, $foo->bar);
        self::assertInstanceOf(SplPriorityQueue::class, $foo->bar->queue);
        self::assertInstanceOf(SplStack::class, $foo->bar->stack);

        // Bind a sub class
        $container->clear();

        $container->share('SplStack', new StubStack());

        $foo = $container->createObject(Foo::class);

        self::assertInstanceOf(StubStack::class, $foo->bar->stack);

        // Bind not shared classes
        $container->clear();

        $container->set(
            'SplPriorityQueue',
            fn() => new SplPriorityQueue()
        );

        $queue = $container->get('SplPriorityQueue');

        $foo = $container->createObject(Foo::class);

        self::assertNotSame($queue, $foo->bar->queue, 'Non shared class should be not same.');

        // Auto created classes should be not shared
        $container->clear();

        $bar1 = $container->createObject(Bar::class);
        $bar2 = $container->createObject(Bar2::class);

        self::assertNotSame($bar1->queue, $bar2->queue);

        // Not shared object
        $container->clear();

        $foo = $container->createObject(Foo::class);
        $foo2 = $container->get(Foo::class);

        self::assertNotSame($foo, $foo2);

        // Shared object
        $container->clear();

        $foo = $container->createSharedObject(Foo::class);
        $foo2 = $container->get(Foo::class);

        self::assertSame($foo, $foo2);
    }

    #[Test]
    public function createsInstancesFromCallables(): void
    {
        $container = new Container(null, Container::AUTO_WIRE);

        /** @var Foo $foo */
        $foo = $container->newInstance(fn(Bar $bar, StubLazy $lazy) => new Foo($bar, $lazy));

        self::assertInstanceOf(Bar::class, $foo->bar);
    }

    #[Test]
    public function resolvesUnionTypedDependencies(): void
    {
        $container = new Container(null, Container::AUTO_WIRE);

        $obj = $container->newInstance(UnionTypeStub::class);

        self::assertInstanceOf(
            ArrayObject::class,
            $obj->iter,
        );
    }

    #[Test]
    public function resolvesDependenciesWithIntersectionTypes(): void
    {
        $container = new Container(null, Container::AUTO_WIRE);

        $obj = $container->newInstance(IntersectionTypeStub::class);

        // Intersection type will be ignored and only get named type.
        self::assertInstanceOf(
            ArrayObject::class,
            $obj->iter,
        );
    }

    /**
     * @see  Container::newInstance
     */
    #[Test]
    public function requiresAutowiringForUnresolvedConstructorArguments(): void
    {
        $container = new Container(null, Container::AUTO_WIRE);

        $container->newInstance(Foo::class, [], 0);

        $container->prepareObject(Bar::class);

        $foo = $container->newInstance(Foo::class, [], 0);

        self::assertInstanceOf(Foo::class, $foo);

        // No autowire on default options
        $container = new Container();

        self::assertExpectedException(
            fn() => $container->newInstance(Foo::class, []),
            DefinitionResolveException::class
        );

        // force autowire at calling
        $foo = $container->newInstance(Foo::class, [], Container::AUTO_WIRE);
    }

    /**
     * @covers \Windwalker\DI\Container::prepareObject
     */
    #[Test]
    public function preparesUnsharedObjects(): void
    {
        $container = new Container();

        $container->prepareObject(Foo::class, null, Container::AUTO_WIRE);

        $foo = $container->get(Foo::class);

        self::assertInstanceOf(Foo::class, $foo);
    }

    /**
     * @covers \Windwalker\DI\Container::prepareSharedObject
     */
    #[Test]
    public function preparesSharedObjects(): void
    {
        $container = new Container();

        $container->prepareSharedObject(Foo::class, null, Container::AUTO_WIRE);

        $foo = $container->get(Foo::class);
        $foo2 = $container->get(Foo::class);

        self::assertSame($foo, $foo2);
    }

    /**
     * Method to test creating a class with arguments which not available for default value.
     *
     * @see  https://github.com/ventoviro/windwalker/issues/318
     *
     * @return  void
     * @throws  ReflectionException
     * @throws  DependencyResolutionException
     */
    #[Test]
    public function createsInstancesWhenConstructorArgumentsHaveNoDefaults(): void
    {
        $container = new Container();

        $obj = $container->newInstance(Collection::class);

        self::assertInstanceOf(Collection::class, $obj);
    }

    /**
     * testNewInstanceWithPropertyAttributes
     *
     * @return  void
     *
     * @throws  ReflectionException
     * @throws  DependencyResolutionException
     *
     * @since  3.4.4
     */
    #[Test]
    public function injectsDependenciesIntoAttributedProperties(): void
    {
        $container = new Container();
        $container->getAttributesResolver()
            ->registerAttribute(Inject::class, Attribute::TARGET_PROPERTY);
        StubService::$counter = 0;

        $container->share(
            'stub',
            function () {
                return new StubService();
            }
        );

        /** @var StubInject $obj */
        $obj = $container->newInstance(StubInject::class);

        self::assertInstanceOf(StubService::class, $obj->foo);
        self::assertInstanceOf(StubService::class, ReflectAccessor::getValue($obj, 'bar'));
        self::assertInstanceOf(StubService::class, $obj->baz);
        self::assertInstanceOf(StubService::class, $obj->yoo);
        self::assertEquals(4, $obj->yoo->getCounter());
    }

    #[Test]
    public function resolvesVariadicConstructorArguments(): void
    {
        $container = new Container();
        $v = $container->newInstance(
            WithVariadic::class,
            [
                str(),
                'foo' => 'bar',
                Collection::class => \Windwalker\collect()
            ]
        );

        self::assertInstanceOf(StringObject::class, $v->args[0]);
        self::assertEquals('bar', $v->args['foo']);
        self::assertInstanceOf(Collection::class, $v->args[Collection::class]);
    }

    #[Test]
    public function injectsEnumDependencies(): void
    {
        $container = new Container();
        $container->share(FooEnum::class, FooEnum::B);

        $v = $container->newInstance(WithEnum::class);

        self::assertEquals(FooEnum::B, $v->foo);
    }

    /**
     * @covers \Windwalker\DI\Container::createChild
     */
    #[Test]
    public function createsChildContainers(): void
    {
        self::assertInstanceOf(Container::class, $this->instance->createChild());
        self::assertNotSame($this->instance, $this->instance->createChild());
        self::assertSame($this->instance, $this->instance->createChild()->getParent());
    }

    /**
     * @covers \Windwalker\DI\Container::extend
     */
    #[Test]
    public function extendsRegisteredServices(): void
    {
        $this->instance->extend(
            'Hello',
            fn($value, $container) => $value . '~~~!!!'
        );

        self::assertEquals('World~~~!!!', $this->instance->get('Hello'));
    }

    #[Test]
    public function appliesExtensionsRegisteredBeforeServices(): void
    {
        $this->instance->extend(
            'Hello2',
            fn($value, $container) => $value . '~~~!!!'
        );

        $this->instance->share(
            'Hello2',
            fn() => 'World2'
        );

        self::assertEquals('World2~~~!!!', $this->instance->get('Hello2'));
    }

    #[Test]
    public function extendsNewInstances(): void
    {
        $this->instance->extendForCreate(
            StubLangCode::class,
            function (StubLangCode $value, $container) {
                $value->tag = StubLangEnum::Japan;
                return $value;
            }
        );

        $item = $this->instance->newInstance(StubLangCode::class);

        self::assertSame(
            'ja-JP',
            $item()
        );
    }

    /**
     * @covers \Windwalker\DI\Container::fork
     */
    #[Test]
    public function forksDefinitionsWithOptionalFreshInstances(): void
    {
        $hello = $this->instance->fork('Hello', 'Hello2');

        self::assertEquals('World', $hello);
        self::assertEquals('World', $this->instance->get('Hello2'));

        $closure = fn() => uniqid();

        $this->instance->share('uniqid', $closure);

        $uid = $this->instance->get('uniqid');

        self::assertEquals($uid, $this->instance->fork('uniqid', 'uniqid2'));
        self::assertEquals($uid, $this->instance->get('uniqid2'));

        self::assertNotEquals($uid, $uid2 = $this->instance->fork('uniqid', 'uniqid3', true));
        self::assertEquals($uid2, $this->instance->get('uniqid3'));
    }

    /**
     * @covers \Windwalker\DI\Container::registerServiceProvider
     */
    #[Test]
    public function registersServicesFromAProvider(): void
    {
        $this->instance->registerServiceProvider(new StubServiceProvider());

        self::assertEquals('Bingo', $this->instance->get('bingo'));
    }

    /**
     * testArrayAccess
     *
     * @return  void
     */
    #[Test]
    public function supportsArrayAccessForServices(): void
    {
        self::assertEquals('World', $this->instance['Hello']);

        $this->instance['your'] = 'welcome';

        self::assertEquals('welcome', $this->instance['your']);
    }

    /**
     * @see  Container::call
     */
    #[Test]
    public function callsCallbacksWithInjectedDependencies(): void
    {
        $this->instance->prepareSharedObject(StubStack::class);

        /** @var StubStack $stack */
        $stack = $this->instance->call(
            function (StubStack $stack) {
                $stack->add(0, 'A');
                return $stack;
            }
        );

        self::assertEquals(
            'A',
            $stack->shift()
        );
    }

    /**
     * @see  Container::call
     */
    #[Test]
    public function callsCallbacksWithNullableDependencies(): void
    {
        /** @var StubStack $stack */
        $stack = $this->instance->call(
            function (?StubStack $stack = null) {
                return $stack;
            }
        );

        self::assertNull($stack);
    }

    /**
     * @see  Container::call
     */
    #[Test]
    public function callsStaticMethodsWithInjectedArguments(): void
    {
        $result = $this->instance->call(
            [
                TypeCast::class,
                'tryInteger',
            ],
            [
                '123',
            ]
        );

        self::assertSame(
            123,
            $result
        );
    }

    #[Test]
    public function callsInvokableObjectsWithInjectedArguments(): void
    {
        $object = new class () {
            public function __invoke(StringObject $str)
            {
                return 'fooooo';
            }
        };

        $result = $this->instance->call($object, [], null, Container::AUTO_WIRE);

        self::assertSame(
            'fooooo',
            $result
        );
    }

    #[Test]
    public function callsFirstClassCallablesWithInjectedArguments(): void
    {
        if (PHP_VERSION_ID < 80100) {
            static::markTestSkipped('First class callable only support PHP 8.1');
        }

        $this->instance->getAttributesResolver()
            ->registerAttribute(ToUpper::class, AttributeType::ALL);

        $object = new class () {
            #[ToUpper]
            public function foo(StringObject $str)
            {
                return 'fooooo';
            }
        };

        $result = $this->instance->call($object->foo(...), [], null, Container::AUTO_WIRE);

        self::assertSame(
            'FOOOOO',
            $result
        );
    }

    /**
     * @see  Container::bind
     */
    #[Test]
    public function bindCreatesANewServiceEachTime(): void
    {
        $this->instance->bind('service', static fn() => new \stdClass());

        self::assertNotSame(
            $this->instance->get('service'),
            $this->instance->get('service')
        );
    }

    #[Test]
    public function bindSharedReusesTheResolvedService(): void
    {
        $this->instance->bindShared('service', static fn() => new \stdClass());

        self::assertSame(
            $this->instance->get('service'),
            $this->instance->get('service')
        );
    }

    #[Test]
    public function setParentAllowsResolvingParentServices(): void
    {
        $parent = new Container();
        $parent->set('parent-service', 'value');

        self::assertSame($this->instance, $this->instance->setParent($parent));
        self::assertSame($parent, $this->instance->getParent());
        self::assertSame('value', $this->instance->get('parent-service'));
    }

    #[Test]
    public function countTracksRegisteredDefinitions(): void
    {
        $count = count($this->instance);

        $this->instance->set('another-service', 'value');

        self::assertSame($count + 1, count($this->instance));
    }

    #[Test]
    public function setParametersReplacesTheCurrentParameters(): void
    {
        $parameters = new Parameters(['answer' => 42]);

        self::assertSame($this->instance, $this->instance->setParameters($parameters));
        self::assertSame($parameters, $this->instance->getParameters());
        self::assertSame(42, $this->instance->getParameters()->get('answer'));

        $this->instance->setParameters(['answer' => 43]);

        self::assertSame(43, $this->instance->getParameters()->get('answer'));
    }

    #[Test]
    public function getIteratorYieldsRegisteredDefinitions(): void
    {
        $definitions = iterator_to_array($this->instance);

        self::assertArrayHasKey('Hello', $definitions);
        self::assertSame($this->instance->getDefinition('Hello'), $definitions['Hello']);
    }

    #[Test]
    public function removeAliasStopsResolvingThroughTheAlias(): void
    {
        $this->instance->alias('flower-alias', 'flower');

        self::assertTrue($this->instance->has('flower-alias'));
        self::assertSame($this->instance, $this->instance->removeAlias('flower-alias'));
        self::assertFalse($this->instance->has('flower-alias'));
    }

    #[Test]
    public function getParentsReturnsTheCompleteParentChain(): void
    {
        $ancestor = new Container();
        $parent = new Container($ancestor);
        $child = new Container($parent);

        self::assertSame([$parent, $ancestor], $child->getParents());
    }

    #[Test]
    public function setDefinitionAssociatesAndResolvesTheDefinition(): void
    {
        $definition = new StoreDefinition('service', static fn() => 'value');

        self::assertSame($definition, $this->instance->setDefinition('service', $definition));
        self::assertSame($this->instance, $definition->getContainer());
        self::assertSame('value', $this->instance->get('service'));
    }

    #[Test]
    public function removeDeletesARegisteredService(): void
    {
        self::assertSame($this->instance, $this->instance->remove('flower'));
        self::assertFalse($this->instance->has('flower'));
    }

    /**
     * @see  Container::resolve
     */
    #[Test]
    public function resolvesClassesCallablesAndDefinitions(): void
    {
        // Test wrapper
        self::assertEquals(
            'Hello',
            $this->instance->resolve(raw('Hello'))
        );

        $this->instance->clear();

        // Resolve params and create instance
        $this->instance->parameters->setDeep('flower.asia.sakura', StubStack::class);

        self::assertInstanceOf(
            StubStack::class,
            $this->instance->resolve(ref('flower.asia.sakura'))
        );

        self::assertInstanceOf(
            StubStack::class,
            $this->instance->resolve('flower.asia.sakura')
        );

        $this->instance->clear();

        // Create instance
        self::assertInstanceOf(
            StubStack::class,
            $this->instance->resolve(StubStack::class)
        );

        self::assertInstanceOf(
            StubStack::class,
            $this->instance->resolve(fn() => new StubStack())
        );

        self::assertSame(
            StubLangEnum::Japan,
            $this->instance->resolve(fn($lang) => StubLangEnum::wrap($lang), ['lang' => 'ja-JP'])
        );

        // Test resolve with definitions
        self::assertInstanceOf(
            Bar::class,
            $this->instance->resolve(
                create(
                    Bar::class,
                    queue: fn() => new SplPriorityQueue(),
                    stack: fn() => new SplStack()
                )
            )
        );
        self::assertInstanceOf(
            Bar::class,
            $this->instance->resolve(
                create(
                    fn(...$args) => new Bar(...$args),
                    queue: fn() => new SplPriorityQueue(),
                    stack: fn() => new SplStack()
                )
            )
        );
        self::assertInstanceOf(
            Bar::class,
            $this->instance->resolve(
                create(fn(...$args) => new Bar(...$args)),
                [
                    'queue' => new SplPriorityQueue(),
                    'stack' => new SplStack()
                ]
            )
        );
        self::assertInstanceOf(
            Bar::class,
            $this->instance->resolve(
                create(Bar::class),
                [
                    'queue' => new SplPriorityQueue(),
                    'stack' => new SplStack()
                ]
            )
        );
        self::assertInstanceOf(
            Bar::class,
            $this->instance->resolve(
                Bar::class,
                [
                    // Instant args from resolve() cannot use function.
                    'queue' => new SplPriorityQueue(),
                    'stack' => new SplStack()
                ]
            )
        );
    }

    /**
     * @see  Container::resolve
     */
    #[Test]
    public function resolvesObjectsWithRegisteredDecorators(): void
    {
        $this->instance->getAttributesResolver()->registerAttribute(
            HelloWrapper::class,
            AttributeType::ALL
        );

        $this->instance->bind(HelloInner::class, create(HelloInner::class, bar: str('Hello')));

        $hello = $this->instance->resolve(HelloInner::class);

        self::assertEquals(
            'World',
            (string) $hello->foo
        );
    }

    /**
     * @see  Container::whenCreating
     */
    #[Test]
    public function whenCreatingReturnsTheCachedBuilderForEachClassAndTag(): void
    {
        $builder = $this->instance->whenCreating(Foo::class);

        self::assertSame($builder, $this->instance->whenCreating(Foo::class));
        self::assertSame(Foo::class, $builder->getClass());
        self::assertNotSame($builder, $this->instance->whenCreating(Foo::class, 'special'));
    }

    #[Before]
    public function initializeContainer(): void
    {
        $this->instance = new Container();

        $this->instance->set(
            'Hello',
            fn() => 'World'
        );

        $this->instance->share(
            'flower',
            fn() => 'sakura'
        );

        $this->instance->protect(
            'olive',
            fn() => 'peace'
        );
    }

}
