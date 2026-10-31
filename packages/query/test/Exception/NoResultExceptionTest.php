<?php

declare(strict_types=1);

namespace Windwalker\Query\Test\Exception;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Query\Exception\NoResultException;

class NoResultExceptionTest extends TestCase
{
    #[After]
    protected function resetMessageHandler(): void
    {
        NoResultException::setMessageHandler(null);
    }

    #[Test]
    public function messageUsesSingularPascalCaseTargetName(): void
    {
        $e = new NoResultException('blog_articles', ['id' => 5]);

        self::assertSame('Destination BlogArticle not found.', $e->getMessage());
        self::assertSame(404, $e->getCode());
        self::assertSame('BlogArticle', $e->getTarget());
    }

    #[Test]
    public function fullMessageContainsConditionsAndTable(): void
    {
        $e = new NoResultException('articles', ['id' => 5]);

        self::assertSame('Unable to find result of {"id":5} in: articles', $e->getFullMessage());
    }

    #[Test]
    public function customMessageHandlerReplacesMessage(): void
    {
        NoResultException::setMessageHandler(
            static fn(NoResultException $e) => 'Missing ' . $e->getTable()
        );

        $e = new NoResultException('articles');

        self::assertSame('Missing articles', $e->getMessage());
    }

    #[Test]
    public function previousExceptionIsPreserved(): void
    {
        $previous = new \RuntimeException('boom');

        $e = new NoResultException('articles', null, $previous);

        self::assertSame($previous, $e->getPrevious());
    }
}
