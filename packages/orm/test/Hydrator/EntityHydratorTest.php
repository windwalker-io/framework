<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test\Hydrator;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use stdClass;
use UnexpectedValueException;
use Windwalker\ORM\EntityMapper;
use Windwalker\ORM\Exception\CastingException;
use Windwalker\ORM\Hydrator\EntityHydrator;
use Windwalker\ORM\Test\AbstractORMTestCase;
use Windwalker\ORM\Test\Entity\StubFlower;

/**
 * The EntityHydratorTest class.
 */
class EntityHydratorTest extends AbstractORMTestCase
{
    private EntityHydrator $instance;

    private EntityMapper $mapper;

    #[Before]
    protected function createInstance(): void
    {
        $this->instance = self::$orm->getEntityHydrator();
        $this->mapper = self::$orm->mapper(StubFlower::class);
    }

    #[Test]
    public function hydrateCastsColumnsAndAssignsUnknownKeys(): void
    {
        $entity = $this->instance->hydrate(
            ['id' => '5', 'title' => 'Foo', 'params' => '{"a":1}', 'extra' => 'x'],
            new StubFlower()
        );

        self::assertSame(5, $entity->id);
        self::assertSame('Foo', $entity->getTitle());
        self::assertSame(['a' => 1], $entity->params);
        self::assertSame('x', $entity->extra);
    }

    #[Test]
    public function hydrateDelegatesForNonEntityObjects(): void
    {
        $object = $this->instance->hydrate(['a' => 1], new stdClass());

        self::assertSame(1, $object->a);
    }

    #[Test]
    public function extractCastsEntityValuesToColumnStrings(): void
    {
        $entity = new StubFlower();
        $entity->id = 5;
        $entity->setTitle('Foo');
        $entity->params = ['a' => 1];

        $data = $this->instance->extract($entity);

        self::assertSame('5', $data['id']);
        self::assertSame('Foo', $data['title']);
        self::assertSame('{"a":1}', $data['params']);
    }

    #[Test]
    public function extractKeepsNativeTypesWhenForceStringifyDisabled(): void
    {
        $this->instance->extractForceStringify = false;

        try {
            $entity = new StubFlower();
            $entity->id = 5;

            self::assertSame(5, $this->instance->extract($entity)['id']);
        } finally {
            $this->instance->extractForceStringify = true;
        }
    }

    #[Test]
    public function extractDelegatesForNonEntityObjects(): void
    {
        $object = new class () {
            public int $a = 1;
        };

        self::assertSame(['a' => 1], $this->instance->extract($object));
        self::assertSame(1, $this->instance->extractField($object, 'a'));
    }

    #[Test]
    public function extractFieldReturnsCastColumnValue(): void
    {
        $entity = new StubFlower();
        $entity->params = ['a' => 1];

        self::assertSame('{"a":1}', $this->instance->extractField($entity, 'params'));
    }

    #[Test]
    public function extractFieldReturnsPlainObjectAndNonColumnPropertyValues(): void
    {
        self::assertSame(2, $this->instance->extractField((object) ['a' => 1, 'b' => 2], 'b'));
        self::assertNull($this->instance->extractField(new \stdClass(), 'missing'));

        $entity = new StubFlower();
        $entity->c = ['x'];

        self::assertSame(['x'], $this->instance->extractField($entity, 'c'));
    }

    #[Test]
    public function extractFieldThrowsForUnknownField(): void
    {
        $this->expectException(UnexpectedValueException::class);

        $this->instance->extractField(new StubFlower(), 'not_exists');
    }

    #[Test]
    public function castFieldForHydrateResolvesPropertyNameAndFallsBackForUnknownColumn(): void
    {
        $metadata = $this->mapper->getMetadata();

        self::assertSame(
            ['a' => 1],
            EntityHydrator::castFieldForHydrate($metadata, 'params', '{"a":1}', new StubFlower())
        );
        self::assertSame(
            'raw',
            EntityHydrator::castFieldForHydrate($metadata, 'unknown', 'raw', new StubFlower())
        );
        self::assertSame(
            5,
            EntityHydrator::castFieldForHydrate($metadata, 'title', 5, new StubFlower())
        );
    }

    #[Test]
    public function castFieldForHydrateWrapsCastFailures(): void
    {
        $this->expectException(CastingException::class);
        $this->expectExceptionMessageIsOrContains('Error when hydrating');

        EntityHydrator::castFieldForHydrate(
            $this->mapper->getMetadata(),
            'params',
            '{invalid json',
            new StubFlower()
        );
    }

    #[Test]
    public function castFieldForExtractSkipsUnknownColumns(): void
    {
        self::assertSame(
            'raw',
            EntityHydrator::castFieldForExtract($this->mapper->getMetadata(), 'unknown', 'raw', new StubFlower())
        );
    }

    #[Test]
    public function castArrayExtractsEachField(): void
    {
        $data = EntityHydrator::castArray(
            $this->mapper->getMetadata(),
            ['params' => ['a' => 1], 'title' => 'Foo']
        );

        self::assertSame('{"a":1}', $data['params']);
        self::assertSame('Foo', $data['title']);
    }

    protected static function setupDatabase(): void
    {
        self::importFromFile(__DIR__ . '/../Stub/data.sql');
    }
}
