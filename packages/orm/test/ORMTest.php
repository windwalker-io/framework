<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test;

use Windwalker\Database\Test\AbstractDatabaseTestCase;
use Windwalker\ORM\EntityMapper;
use Windwalker\ORM\ORM;
use Windwalker\ORM\SelectorQuery;
use Windwalker\ORM\Test\Entity\StubArticle;
use Windwalker\Query\Query;

/**
 * The ORMTest class.
 */
class ORMTest extends AbstractDatabaseTestCase
{
    protected ?ORM $instance;

    /**
     * @see  ORM::findOne
     */
    public function testFindOne(): void
    {
        $article = $this->instance->findOne(StubArticle::class, 1);

        self::assertInstanceOf(StubArticle::class, $article);
        self::assertEquals(1, $article->getId());
        self::assertEquals('Corrupti illum.', $article->getTitle());
        self::assertEquals('2009-05-14 17:45:24', $article->getCreated()->format(self::$db->getDateFormat()));
        self::assertTrue($article->getParams()['show_title']);
    }

    public function chainReturn()
    {
        /** @var EntityMapper<StubArticle> $mapper */
        $mapper = $this->instance->mapper(StubArticle::class);

        $item = $mapper->tryEntity();
    }

    /**
     * @see  ORM::__construct
     */
    public function testConstruct(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  ORM::getDb
     */
    public function testGetDb(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  ORM::from
     */
    public function testFrom(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  ORM::setDb
     */
    public function testSetDb(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  ORM::conditionsToWheres
     */
    public function testConditionsToWheres(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  ORM::select
     */
    public function testSelect(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    protected function setUp(): void
    {
        $this->instance = new ORM(self::$db);
    }

    protected function tearDown(): void
    {
    }

    /**
     * @inheritDoc
     */
    protected static function setupDatabase(): void
    {
        self::importFromFile(__DIR__ . '/Stub/data.sql');
    }
}
