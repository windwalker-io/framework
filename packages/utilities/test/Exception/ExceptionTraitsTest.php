<?php

declare(strict_types=1);

namespace Windwalker\Utilities\Test\Exception;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Utilities\Exception\CastingException;
use Windwalker\Utilities\Exception\MultiMessagesExceptionTrait;
use Windwalker\Utilities\Exception\VerbosityExceptionInterface;
use Windwalker\Utilities\Exception\VerbosityExceptionTrait;

class ExceptionTraitsTest extends TestCase
{
    #[Test]
    public function multiMessagesAreJoinedByEol(): void
    {
        $e = new StubMultiMessagesException(['a', ['b', 'c'], 'd'], 12);

        self::assertSame('a' . PHP_EOL . 'b' . PHP_EOL . 'c' . PHP_EOL . 'd', $e->getMessage());
        self::assertSame(['a', 'b' . PHP_EOL . 'c', 'd'], $e->getMessages());
        self::assertSame(12, $e->getCode());
    }

    #[Test]
    public function multiMessagesAcceptsStringAndNull(): void
    {
        self::assertSame('hello', (new StubMultiMessagesException('hello'))->getMessage());
        self::assertSame('', (new StubMultiMessagesException())->getMessage());
        self::assertSame([], (new StubMultiMessagesException())->getMessages());
    }

    #[Test]
    public function verbosityReturnsDebugMessageOnlyWhenVerbose(): void
    {
        $e = new StubVerbosityException('plain');

        self::assertSame('plain', $e->getMessageByVerbosity());
        self::assertSame('plain', $e->getMessageByVerbosity(false));
        self::assertSame('debug: plain', $e->getMessageByVerbosity(true));
        self::assertSame('debug: plain', $e->getMessageByVerbosity(2));
        self::assertSame('plain', $e->getMessageByVerbosity(0));
    }

    #[Test]
    public function castingExceptionIsUnexpectedValueException(): void
    {
        self::assertInstanceOf(\UnexpectedValueException::class, new CastingException());
    }
}

/**
 * Fixture applying MultiMessagesExceptionTrait to a real exception, used by ExceptionTraitsTest.
 *
 * Remove it together with the multi messages tests when the trait is removed.
 */
class StubMultiMessagesException extends \Exception
{
    use MultiMessagesExceptionTrait;
}

/**
 * Fixture applying VerbosityExceptionTrait, used by ExceptionTraitsTest.
 *
 * Remove it together with the verbosity tests when the trait is removed.
 */
class StubVerbosityException extends \Exception implements VerbosityExceptionInterface
{
    use VerbosityExceptionTrait;

    public function getDebugMessage(): string
    {
        return 'debug: ' . $this->getMessage();
    }
}
