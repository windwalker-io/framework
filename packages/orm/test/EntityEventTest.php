<?php

declare(strict_types=1);

namespace Windwalker\ORM\Test;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\ORM\EntityMapper;
use Windwalker\ORM\Test\Entity\StubArticle;

/**
 * The EntityEventTest class.
 */
class EntityEventTest extends AbstractORMTestCase
{
    protected EntityMapper $instance;

    #[Test]
    public function energizeInitializesEntityMetadata(): void
    {
        $article = new StubArticle();

        self::$orm->energize($article);

        $str = self::$orm::getObjectMetadata()->get($article, 'str')();

        self::assertEquals('HAHA', (string) $str);
    }

    #[Test]
    public function saveDispatchesStoreEventsAroundWrite()
    {
        StubArticle::$counter = 0;

        $article = new StubArticle();
        $article->setTitle('Hello');
        $article->setCategoryId(1);
        $article->setContent('World');
        $article->setCreated(new DateTimeImmutable('now'));
        $article->setState(1);

        /** @var StubArticle $article */
        $article = self::$orm->mapper(StubArticle::class)->createOne($article);
        $str = self::$orm::getObjectMetadata()->get($article, 'str')();

        self::assertEquals(
            1,
            StubArticle::$counter,
        );
        self::assertEquals(
            'World',
            StubArticle::$extra['content'],
        );
        self::assertEquals(2, $article->getCategoryId());
        self::assertEquals('HAHA', (string) $str);

        StubArticle::$counter = 0;

        // Store event
        $article->setTitle('Hello123');

        /** @var StubArticle $article */
        self::$orm->mapper(StubArticle::class)->updateOne($article);

        self::assertEquals(
            [
                'id' => 16,
                'category_id' => 2,
                'title' => 'Hello123'
            ],
            StubArticle::$diff,
        );
    }

    /**
     * @inheritDoc
     */
    protected static function setupDatabase(): void
    {
        self::importFromFile(__DIR__ . '/Stub/data.sql');
    }
}
