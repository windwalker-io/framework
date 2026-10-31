<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Response;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\Output\OutputInterface;
use Windwalker\Http\Response\CallbackResponse;
use Windwalker\Http\Test\Mock\MockOutput;

/**
 * Test class of CallbackResponse
 */
class CallbackResponseTest extends TestCase
{
    #[Test]
    public function respondInvokesCallbackWithOutput(): void
    {
        $output = new MockOutput();
        $received = null;

        $response = new CallbackResponse(
            function (OutputInterface $o) use (&$received) {
                $received = $o;
            },
        );

        $response->respond($output);

        self::assertSame($output, $received);
    }

    #[Test]
    public function respondDoesNothingWithoutCallback(): void
    {
        $output = new MockOutput();

        new CallbackResponse()->respond($output);

        self::assertSame('', (string) $output->output);
    }

    #[Test]
    public function constructorAppliesStatusAndHeaders(): void
    {
        $response = new CallbackResponse(null, 202, ['X-Foo' => 'bar']);

        self::assertSame(202, $response->getStatusCode());
        self::assertSame('bar', $response->getHeaderLine('X-Foo'));
    }

    #[Test]
    public function withCallbackReturnsCloneAndKeepsOriginal(): void
    {
        $first = static function () {};
        $second = static function () {};

        $a = new CallbackResponse($first, 201);
        $b = $a->withCallback($second);

        self::assertNotSame($a, $b);
        self::assertSame($first, $a->callback);
        self::assertSame($second, $b->callback);
        self::assertSame(201, $b->getStatusCode());
    }
}
