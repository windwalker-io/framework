<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test\Cast;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\ORM\Attributes\CastAttributeInterface;
use Windwalker\ORM\Cast\JsonCast;
use Windwalker\ORM\Cast\JsonCastArrayList;

/**
 * The JsonCastArrayListTest class.
 */
class JsonCastArrayListTest extends TestCase
{
    #[Test]
    public function extractReturnsExistingJsonUntouched(): void
    {
        self::assertSame('{"a":1}', new JsonCastArrayList()->extract('{"a":1}'));
    }

    #[Test]
    public function extractEncodesEmptyArrayAsList(): void
    {
        self::assertSame('[]', new JsonCastArrayList()->extract([]));
    }

    #[Test]
    public function extractEncodesArrayWithoutForcingObject(): void
    {
        self::assertSame('["a","b"]', new JsonCastArrayList()->extract(['a', 'b']));
    }

    #[Test]
    public function hydrateForcesListWhenValueIsNotJsonString(): void
    {
        $cast = new JsonCast(JsonCast::FORCE_ARRAY_LIST);

        self::assertSame([1, 2], $cast->hydrate(['x' => 1, 'y' => 2]));
    }

    #[Test]
    public function hydrateReturnsNullForEmptyString(): void
    {
        self::assertNull(new JsonCast()->hydrate(''));
    }

    #[Test]
    public function hydrateReturnsEmptyArrayForJsonNull(): void
    {
        self::assertSame([], new JsonCast()->hydrate('null'));
    }

    #[Test]
    public function extractForcesListWhenOptionEnabled(): void
    {
        $cast = new JsonCast(JsonCast::FORCE_ARRAY_LIST);

        self::assertSame('[1,2]', $cast->extract(['x' => 1, 'y' => 2]));
    }

    #[Test]
    public function getOptionsAddsNullableFlagsWhenNullable(): void
    {
        $cast = new JsonCast(0, nullable: true);

        self::assertSame(
            CastAttributeInterface::NULLABLE | CastAttributeInterface::EMPTY_STRING_TO_NULL,
            $cast->getOptions()
        );
        self::assertSame(0, new JsonCast(0)->getOptions());
    }

    #[Test]
    public function serializeReturnsNullForEmptyWhenNullable(): void
    {
        $cast = new JsonCast(nullable: true);

        self::assertNull($cast->serialize(null));
        self::assertNull($cast->serialize(''));
    }

    #[Test]
    public function serializeForcesListOrObjectByOptions(): void
    {
        self::assertSame([1, 2], new JsonCast(JsonCast::FORCE_ARRAY_LIST)->serialize(['x' => 1, 'y' => 2]));
        self::assertEquals(
            new \stdClass(),
            new JsonCast(JsonCast::EMPTY_ARRAY_AS_OBJECT)->serialize([])
        );
        self::assertSame(['a' => 1], new JsonCast()->serialize(['a' => 1]));
    }
}
