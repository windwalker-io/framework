<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Accessible;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Accessible\AccessorBCTrait;

class AccessorBCTraitTest extends TestCase
{
    protected function createSubject(): object
    {
        return new class () {
            use AccessorBCTrait;

            protected string $foo = 'bar';

            private ?int $count = 3;

            public bool $active = true;

            protected bool $isReady = false;
        };
    }

    #[Test]
    public function getterReadsPropertyCaseInsensitively(): void
    {
        $subject = $this->createSubject();

        self::assertSame('bar', $subject->getFoo());
        self::assertSame(3, $subject->getCOUNT());
    }

    #[Test]
    public function setterWritesPropertyAndReturnsSelf(): void
    {
        $subject = $this->createSubject();

        self::assertSame($subject, $subject->setFoo('yoo'));
        self::assertSame('yoo', $subject->getFoo());
    }

    #[Test]
    public function setterWithoutArgumentAssignsNull(): void
    {
        $subject = $this->createSubject();

        $subject->setCount();

        self::assertNull($subject->getCount());
    }

    #[Test]
    public function isMethodReadsPropertyByNameOrFullMethodName(): void
    {
        $subject = $this->createSubject();

        self::assertTrue($subject->isActive());
        self::assertFalse($subject->isReady());
    }

    #[Test]
    public function unknownMethodsThrowBadMethodCallException(): void
    {
        $subject = $this->createSubject();

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessageIsOrContains('::doSomething() does not exist.');

        $subject->doSomething();
    }

    #[Test]
    public function getterOfUnknownPropertyThrowsBadMethodCallException(): void
    {
        $subject = $this->createSubject();

        $this->expectException(\BadMethodCallException::class);

        $subject->getNothing();
    }

    #[Test]
    public function isOfUnknownPropertyThrowsBadMethodCallException(): void
    {
        $subject = $this->createSubject();

        $this->expectException(\BadMethodCallException::class);

        $subject->isNothing();
    }
}
