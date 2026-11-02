<?php

declare(strict_types=1);

namespace Windwalker\Filter\Test;

use OutOfRangeException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Filter\CallbackFilter;
use Windwalker\Filter\ChainFilter;
use Windwalker\Filter\FilterFactory;
use Windwalker\Filter\Rule\Alnum;
use Windwalker\Filter\Traits\FilterAwareTrait;

/**
 * @see FilterFactory
 */
class FilterFactoryExtraTest extends TestCase
{
    #[Test]
    public function getFactoryThrowsForUnknownType(): void
    {
        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessageIs('Filter type: nope not found.');

        new FilterFactory()->getFactory('nope');
    }

    #[Test]
    public function addAndRemoveFactoryUpdatesAvailableTypes(): void
    {
        $factory = new FilterFactory([]);

        self::assertFalse($factory->hasFactory('alnum'));

        $factory->addFactory('alnum', Alnum::class);

        self::assertTrue($factory->hasFactory('alnum'));
        self::assertInstanceOf(Alnum::class, $factory->createByFactory('alnum'));

        $factory->removeFactory('alnum');

        self::assertFalse($factory->hasFactory('alnum'));
    }

    #[Test]
    public function addFilterCallbackRegistersCallbackBackedFilter(): void
    {
        $factory = new FilterFactory([])->addFilterCallback('shout', fn($v) => strtoupper($v));

        self::assertSame('HI', $factory->createFromSyntax('shout')->filter('hi'));
    }

    #[Test]
    public function setFactoriesReplacesAllFactories(): void
    {
        $factory = new FilterFactory();
        $factory->setFactories(['x' => fn() => new Alnum()]);

        self::assertSame(['x'], array_keys($factory->getFactories()));
    }

    #[Test]
    public function createReturnsGivenFilterInstanceAndWrapsCallables(): void
    {
        $factory = new FilterFactory();
        $alnum = new Alnum();

        self::assertSame($alnum, $factory->create($alnum));
        self::assertInstanceOf(CallbackFilter::class, $factory->create('strtoupper'));
        self::assertInstanceOf(Alnum::class, $factory->create(Alnum::class));
    }

    #[Test]
    public function createChainFromSyntaxSplitsPipesAndRunsFiltersInOrder(): void
    {
        $chain = new FilterFactory()->createChainFromSyntax('alnum|length(max:3)|func(strtoupper)');

        self::assertInstanceOf(ChainFilter::class, $chain);
        self::assertSame('ABC', $chain->filter('a-b-c-d'));
    }

    #[Test]
    public function createChainFromSyntaxAcceptsArrayOfCallablesAndClasses(): void
    {
        $chain = new FilterFactory()->createChainFromSyntax([Alnum::class, fn($v) => $v . '!']);

        self::assertSame('ab!', $chain->filter('a-b'));
    }

    #[Test]
    public function createFromSyntaxFillsOptionsByReference(): void
    {
        $options = [];

        new FilterFactory()->createFromSyntax('length(max:5, utf8)', $options);

        self::assertSame(['max' => '5', 'utf8' => true], $options);
    }

    #[Test]
    #[DataProvider('syntaxOptionsProvider')]
    public function createFromSyntaxParsesQuotedParameters(string $syntax, array $expected): void
    {
        $options = [];

        new FilterFactory()->createFromSyntax($syntax, $options);

        self::assertSame($expected, $options);
    }

    public static function syntaxOptionsProvider(): array
    {
        return [
            'double quoted value' => ['regex(regex:"\\d+ x")', ['regex' => '\\d+ x']],
            'single quoted value' => ["regex(regex:'a,b)c')", ['regex' => 'a,b)c']],
            'escaped double quote' => ['regex(regex:"say \\"hi\\"")', ['regex' => 'say "hi"']],
            'escaped single quote' => ["regex(regex:'it\\'s')", ['regex' => "it's"]],
            'escaped backslash' => ['regex(regex:"a\\\\\\\\b")', ['regex' => 'a\\\\b']],
            'quoted key' => ['default("hello world")', ['hello world' => true]],
            'mixed params' => ['regex(regex:"/a/", "type" = \'replace\')', ['regex' => '/a/', 'type' => 'replace']],
        ];
    }

    #[Test]
    public function createChainFromSyntaxKeepsPipesInsideQuotes(): void
    {
        $chain = new FilterFactory()->createChainFromSyntax('regex(regex:"/[a|b]+/")|func(strtoupper)');

        self::assertSame('AB|A', $chain->filter('a-b|c-a'));
    }

    #[Test]
    public function defaultTypeFactoriesCastValues(): void
    {
        $factory = new FilterFactory();

        self::assertSame(12, $factory->createFromSyntax('int')->filter('12'));
        self::assertSame('12', $factory->createFromSyntax('string')->filter(12));
        self::assertSame(7, $factory->createFromSyntax('clamp(min:1,max:7)')->filter(99));
    }

    #[Test]
    public function filterAwareTraitLazilyCreatesAndAcceptsFactory(): void
    {
        $object = new class () {
            use FilterAwareTrait;
        };

        $created = $object->getFilterFactory();

        self::assertSame($created, $object->getFilterFactory());

        $custom = new FilterFactory([]);

        self::assertSame($custom, $object->setFilterFactory($custom)->getFilterFactory());
    }
}
