<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\ORM\Attributes\JsonNoSerialize;
use Windwalker\ORM\Attributes\JsonSerializer;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\EntityInterface;
use Windwalker\ORM\EntityTrait;
use Windwalker\ORM\Test\Entity\StubLocation;

/**
 * The EntityTraitTest class.
 */
class EntityTraitTest extends TestCase
{
    #[Test]
    public function tableReturnsNameFromTableAttribute(): void
    {
        self::assertSame('locations', StubLocation::table());
    }

    #[Test]
    public function newInstanceAssignsGivenProperties(): void
    {
        $entity = StubLocation::newInstance(['id' => 3, 'title' => 'Taipei']);

        self::assertSame(3, $entity->getId());
        self::assertSame('Taipei', $entity->getTitle());
    }

    #[Test]
    public function createAcceptsNamedArgumentsOrClosure(): void
    {
        $named = StubLocation::create(title: 'Named');

        self::assertSame('Named', $named->getTitle());

        $closure = StubLocation::create(function (StubLocation $entity) {
            $entity->setTitle('Closure');
        });

        self::assertSame('Closure', $closure->getTitle());

        $replaced = StubLocation::create(fn(StubLocation $entity) => StubLocation::newInstance(['title' => 'New']));

        self::assertSame('New', $replaced->getTitle());
    }

    #[Test]
    public function magicAccessorsUseGettersAndSetters(): void
    {
        $entity = new StubLocation();

        $entity->title = 'Magic';

        self::assertSame('Magic', $entity->title);
        self::assertSame('Magic', $entity->getTitle());
        self::assertTrue(isset($entity->title));
        self::assertFalse(isset($entity->id));
    }

    #[Test]
    public function dumpAndToCollectionIncludeOnlyExposedProperties(): void
    {
        $entity = StubLocation::newInstance(['id' => 2, 'title' => 'Dump']);

        self::assertSame(['data', 'sakuras'], array_keys($entity->dump()));
        self::assertSame(['data', 'sakuras'], array_keys($entity->toCollection()->dump()));
    }

    #[Test]
    public function jsonSerializeReturnsExposedValues(): void
    {
        $entity = new StubLocation();

        self::assertSame(['data' => null, 'sakuras' => null], $entity->jsonSerialize());
    }

    #[Test]
    public function defaultAliasReadsTableAttribute(): void
    {
        self::assertSame('location', StubLocation::defaultAlias());
    }

    #[Test]
    public function jsonSerializeAppliesSerializerAttributes(): void
    {
        $entity = new #[Table('things', 'thing')] class () implements EntityInterface {
            use EntityTrait;

            public string $name = 'a';

            #[JsonNoSerialize]
            public string $secret = 'hidden';

            #[JsonSerializer('strtoupper')]
            public string $title = 'abc';

            protected bool $active = true;

            public function isActive(): bool
            {
                return $this->active;
            }
        };

        self::assertSame(['name' => 'a', 'title' => 'ABC'], $entity->jsonSerialize());
        self::assertTrue(isset($entity->active));
        self::assertTrue($entity->active);
        self::assertTrue(isset($entity->name));
    }
}
