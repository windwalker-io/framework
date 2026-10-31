<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Classes;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Classes\TraitHelper;

class TraitHelperTest extends TestCase
{
    #[Before]
    protected function clearCache(): void
    {
        TraitHelper::$cache = [];
    }

    #[Test]
    public function classUsesRecursiveCollectsTraitsFromParentsAndNestedTraits(): void
    {
        $traits = TraitHelper::classUsesRecursive(StubTraitChild::class);

        self::assertEqualsCanonicalizing(
            [StubOuterTrait::class, StubInnerTrait::class, StubParentTrait::class],
            $traits
        );
    }

    #[Test]
    public function classUsesRecursiveAcceptsObjectAndCachesResult(): void
    {
        $traits = TraitHelper::classUsesRecursive(new StubTraitChild());

        self::assertSame($traits, TraitHelper::$cache[StubTraitChild::class]);

        TraitHelper::$cache[StubTraitChild::class] = ['cached'];

        self::assertSame(['cached'], TraitHelper::classUsesRecursive(StubTraitChild::class));
    }

    #[Test]
    public function classUsesRecursiveReturnsEmptyArrayForClassWithoutTraits(): void
    {
        self::assertSame([], TraitHelper::classUsesRecursive(\stdClass::class));
    }

    #[Test]
    public function usesChecksWhetherTraitIsUsedRecursively(): void
    {
        self::assertTrue(TraitHelper::uses(StubTraitChild::class, StubInnerTrait::class));
        self::assertTrue(TraitHelper::uses(new StubTraitChild(), StubParentTrait::class));
        self::assertFalse(TraitHelper::uses(StubTraitChild::class, \stdClass::class));
    }
}

/**
 * Fixture trait nested inside StubOuterTrait, used by TraitHelperTest.
 *
 * Remove it together with TraitHelperTest.
 */
trait StubInnerTrait {}

/**
 * Fixture trait using StubInnerTrait, used by TraitHelperTest.
 *
 * Remove it together with TraitHelperTest.
 */
trait StubOuterTrait
{
    use StubInnerTrait;
}

/**
 * Fixture trait used by the parent class, used by TraitHelperTest.
 *
 * Remove it together with TraitHelperTest.
 */
trait StubParentTrait {}

/**
 * Fixture parent class using a trait, used by TraitHelperTest.
 *
 * Remove it together with TraitHelperTest.
 */
class StubTraitParent
{
    use StubParentTrait;
}

/**
 * Fixture child class using nested traits, used by TraitHelperTest.
 *
 * Remove it together with TraitHelperTest.
 */
class StubTraitChild extends StubTraitParent
{
    use StubOuterTrait;
}
