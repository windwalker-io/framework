<?php

declare(strict_types=1);

namespace Windwalker\Http\Test;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Windwalker\Http\Test\Stub\StubMessage;
use Windwalker\Stream\Stream;
use Windwalker\Test\Traits\BaseAssertionTrait;

/**
 * Test class of AbstractMessage
 *
 * @since 2.1
 */
class MessageTraitTest extends TestCase
{
    use BaseAssertionTrait;

    protected ?StubMessage $message;

    #[Before]
    protected function createMessage(): void
    {
        $this->message = new StubMessage();
    }

    #[Test]
    public function withProtocolVersionReturnsNewInstanceWithVersion(): void
    {
        self::assertEquals('1.1', $this->message->getProtocolVersion());

        $message = $this->message->withProtocolVersion('1.0');

        self::assertNotSame($this->message, $message);
        self::assertEquals('1.0', $message->getProtocolVersion());
    }

    #[Test]
    public function withHeaderSetsHeaderValuesCaseInsensitively(): void
    {
        $message = $this->message->withHeader('Content-Type', 'text/json');

        self::assertNotSame($this->message, $message);
        self::assertEquals(['text/json'], $message->getHeader('Content-Type'));
        self::assertEquals(['text/json'], $message->getHeader('content-type'));

        $message = $this->message->withHeader('X-Foo', ['Foo', 'Bar']);

        self::assertNotSame($this->message, $message);
        self::assertEquals(['Foo', 'Bar'], $message->getHeader('X-Foo'));
    }

    #[Test]
    public function hasHeaderDetectsHeaderCaseInsensitively(): void
    {
        self::assertFalse($this->message->hasHeader('X-Foo'));

        $message = $this->message->withHeader('Content-Type', 'text/json');

        self::assertTrue($message->hasHeader('Content-Type'));
        self::assertTrue($message->hasHeader('content-type'));
    }

    #[Test]
    public function getHeadersReturnsAllHeadersWithOriginalNames(): void
    {
        self::assertEquals([], $this->message->getHeaders());

        $message = $this->message->withHeader('X-Foo', ['Foo', 'Bar']);
        $message = $message->withHeader('X-Bar', ['Flower', 'Sakura']);

        $expected = [
            'X-Foo' => ['Foo', 'Bar'],
            'X-Bar' => ['Flower', 'Sakura'],
        ];

        self::assertEquals($expected, $message->getHeaders());
    }

    #[Test]
    public function getHeaderLineJoinsHeaderValuesWithComma(): void
    {
        self::assertEquals('', $this->message->getHeaderLine('X-Foo'));

        $message = $this->message->withHeader('X-Foo', ['Foo', 'Bar']);

        self::assertEquals('Foo,Bar', $message->getHeaderLine('X-Foo'));
        self::assertEquals('Foo,Bar', $message->getHeaderLine('x-foo'));
        self::assertSame('', $message->getHeaderLine('x-bar'));
    }

    #[Test]
    public function withAddedHeaderAppendsValuesToExistingHeader(): void
    {
        $message = $this->message->withAddedHeader('X-Foo', 'One');

        self::assertNotSame($this->message, $message);
        self::assertEquals(['One'], $message->getHeader('X-Foo'));

        $message = $message->withAddedHeader('X-Foo', 'Two');

        self::assertEquals(['One', 'Two'], $message->getHeader('X-Foo'));

        $message = $message->withAddedHeader('X-Foo', ['Three', 'Four']);

        self::assertEquals(['One', 'Two', 'Three', 'Four'], $message->getHeader('X-Foo'));
    }

    #[Test]
    public function withoutHeaderRemovesHeaderCaseInsensitively(): void
    {
        $message = $this->message->withAddedHeader('X-Foo', 'One');

        self::assertNotSame($this->message, $message);
        self::assertEquals(['One'], $message->getHeader('X-Foo'));

        $message2 = $message->withoutHeader('X-Foo');

        self::assertNotSame($this->message, $message2);
        self::assertEquals([], $message2->getHeader('X-Foo'));

        $message3 = $message->withoutHeader('x-foo');

        self::assertNotSame($this->message, $message3);
        self::assertEquals([], $message3->getHeader('X-Foo'));
    }

    #[Test]
    public function withBodyReturnsNewInstanceWithStreamBody(): void
    {
        $message = $this->message->withBody(new Stream());

        self::assertNotSame($this->message, $message);
        self::assertInstanceOf(StreamInterface::class, $message->getBody());
    }

    #[Test]
    public function withProtocolVersionThrowsOnInvalidVersion(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Invalid version number');

        $this->message->withProtocolVersion('abc');
    }

    #[Test]
    public function getHeaderReturnsEmptyArrayAndHeaderLineEmptyStringForMissingHeader(): void
    {
        self::assertSame([], $this->message->getHeader('X-Missing'));
        self::assertSame('', $this->message->getHeaderLine('X-Missing'));
    }

    #[Test]
    public function withHeaderReplacesExistingValuesAndNormalizesName(): void
    {
        $message = $this->message->withHeader('x-foo', 'a')->withHeader('X-FOO', ['b', 'c']);

        self::assertSame(['b', 'c'], $message->getHeader('x-foo'));
        self::assertCount(1, $message->getHeaders());
    }

    #[Test]
    #[DataProvider('invalidHeaderProvider')]
    public function withAddedHeaderThrowsOnInvalidNameOrValue(string $name, mixed $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->message->withAddedHeader($name, $value);
    }

    public static function invalidHeaderProvider(): array
    {
        return [
            'invalid name' => ['Bad Name', 'foo'],
            'empty name' => ['', 'foo'],
            'non-string value' => ['X-Foo', [1]],
        ];
    }

    #[Test]
    public function withHeaderDoesNotMutateOriginalWhenValueIsInvalid(): void
    {
        $message = $this->message->withHeader('X-Foo', 'a');

        try {
            $message->withHeader('X-Foo', [1]);
        } catch (InvalidArgumentException) {
        }

        self::assertSame(['a'], $message->getHeader('X-Foo'));
    }

    #[Test]
    public function withoutHeaderReturnsCloneWhenHeaderDoesNotExist(): void
    {
        $message = $this->message->withoutHeader('X-Missing');

        self::assertNotSame($this->message, $message);
        self::assertSame([], $message->getHeaders());
    }
}
