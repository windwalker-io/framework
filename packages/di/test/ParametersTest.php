<?php

declare(strict_types=1);

namespace Windwalker\DI\Test;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\DI\Parameters;

use function Windwalker\ref;

/**
 * The ParametersTest class.
 */
class ParametersTest extends TestCase
{
    protected Parameters $instance;

    /**
     * @see  Parameters::extract
     */
    #[Test]
    public function extractsReferencedValues(): void
    {
        $foo = $this->instance->extract('foo', true);

        $this->instance->setDeep('foo.goo', 'World');

        self::assertEquals(
            'Hello',
            $foo->getDeep('bar.yoo')
        );

        self::assertEquals(
            'Hello',
            $foo->getDeep('foo.bar.yoo')
        );

        self::assertEquals(
            'World',
            $foo->getDeep('goo')
        );
    }

    #[Test]
    public function resolvesReferencesFromParentParameters(): void
    {
        $foo = $this->instance->extract('foo', true);

        self::assertEquals(
            'Hello',
            $foo->getDeep('ref')
        );

        $foo->set('ref', ref('bar.yoo'));

        self::assertEquals(
            'Hello',
            $foo->getDeep('ref')
        );
    }

    /**
     * @see  Parameters::hasDeep
     */
    #[Test]
    public function hasDeepReportsWhetherPathsResolve(): void
    {
        $foo = $this->instance->extract('foo', true);

        self::assertTrue($foo->hasDeep('bar.yoo'));
        self::assertTrue($foo->hasDeep('foo.bar.yoo'));
        self::assertFalse($foo->hasDeep('foo.goo'));
    }

    /**
     * @see  Parameters::has
     */
    #[Test]
    public function hasReturnsWhetherAValueExists(): void
    {
        self::assertTrue($this->instance->has('foo'));
        self::assertFalse($this->instance->has('missing'));
    }

    #[Test]
    public function getResolvesReferencesAndReturnsNullForMissingValues(): void
    {
        self::assertSame('Hello', $this->instance->get('ref'));
        self::assertNull($this->instance->get('missing'));
    }

    /**
     * @see  Parameters::getParent
     */
    #[Test]
    public function returnsTheParentForReferencedParameters(): void
    {
        $foo = $this->instance->extract('foo', true);

        self::assertSame($this->instance, $foo->getParent());
    }

    #[Test]
    public function createsChildScopesWithParentFallbackAndLocalOverrides(): void
    {
        $child = $this->instance->createChild();

        self::assertSame('Hello', $child->getDeep('foo.bar.yoo'));

        $child->setDeep('foo.bar.yoo', 'World');

        self::assertSame('World', $child->getDeep('foo.bar.yoo'));
        self::assertSame('Hello', $this->instance->getDeep('foo.bar.yoo'));
    }

    #[Test]
    public function iteratesParentValuesOnlyWhenRequested(): void
    {
        $child = $this->instance->createChild();
        $child->set('local', 'child');
        $this->instance->set('added-later', 'parent');

        self::assertArrayNotHasKey('added-later', iterator_to_array($child->getIterator(false)));
        self::assertSame('parent', iterator_to_array($child->getIterator())['added-later']);
    }

    #[Test]
    public function dumpsLocalAndParentValuesAndResolvesReferences(): void
    {
        $child = $this->instance->createChild();
        $child->set('local', 'child');

        $dump = $child->dump();

        self::assertSame('Hello', $dump['ref']);
        self::assertSame('child', $dump['local']);
        self::assertArrayHasKey('foo', $dump);
    }

    #[Before]
    public function initializeParameters(): void
    {
        $this->instance = new Parameters(
            [
                'foo' => [
                    'bar' => [
                        'yoo' => 'Hello',
                    ],
                ],
                'ref' => ref('foo.bar.yoo'),
            ]
        );
    }
}
