<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Output;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\Output\Output;
use Windwalker\Http\Response\CallbackResponse;
use Windwalker\Http\Response\Response;
use Windwalker\Http\Test\Stub\StubOutput;
use Windwalker\Stream\Stream;

class OutputTest extends TestCase
{
    #[Test]
    public function respondSendsStatusLineHeadersAndBodyWhenHeadersNotSent(): void
    {
        $output = new StubOutput();
        $output->headerSent = fn() => false;
        $output->setOutputStream(new Stream('php://memory', 'wb+'));

        $response = Response::fromString('Hello', 404, ['x-foo' => ['a', 'b'], 'content-type' => 'text/plain']);

        ob_start();
        $output->respond($response);
        ob_end_clean();

        self::assertSame('HTTP/1.1 404 Not Found', $output->status);
        self::assertSame(['a', 'b'], $output->message->getHeader('X-Foo'));
        self::assertSame('text/plain', $output->message->getHeaderLine('Content-Type'));
    }

    #[Test]
    public function respondSkipsStatusLineAndHeadersWhenHeadersAlreadySent(): void
    {
        $output = new StubOutput();
        $output->headerSent = fn() => true;
        $output->setOutputStream(new Stream('php://memory', 'wb+'));

        $output->respond(Response::fromString('Hello', 200, ['x-foo' => 'a']));

        self::assertNull($output->status);
        self::assertFalse($output->message->hasHeader('x-foo'));
    }

    #[Test]
    public function sendStatusLineOmitsMissingReasonPhrase(): void
    {
        $output = new StubOutput();
        $response = new Response('php://memory', 299);

        $output->sendStatusLine($response->withProtocolVersion('2'));

        self::assertSame('HTTP/2 299', $output->status);
    }

    #[Test]
    public function sendBodyWritesBodyToOutputAndClosesStream(): void
    {
        $output = new Output();

        ob_start();
        $output->sendBody(Response::fromString('Body'));
        $content = ob_get_clean();

        self::assertSame('Body', $content);
        self::assertFalse($output->isWritable());
    }

    #[Test]
    public function respondDelegatesToCallbackResponseAndClosesStream(): void
    {
        $output = new StubOutput();
        $output->headerSent = fn() => true;
        $output->setOutputStream(new Stream('php://memory', 'wb+'));

        $received = null;
        $response = new CallbackResponse(function (Output $o) use (&$received) {
            $received = $o;
            $o->header('X-Called: yes');
        });

        $output->respond($response);

        self::assertSame($output, $received);
        self::assertSame('yes', $output->message->getHeaderLine('X-Called'));
        self::assertFalse($output->isWritable());
    }

    #[Test]
    public function writeReturnsWrittenBytesAndHeadersSentUsesConfiguredResolver(): void
    {
        $stream = new Stream('php://memory', 'wb+');
        $output = new Output($stream);

        self::assertSame(3, $output->write('abc'));
        self::assertSame($stream, $output->getOutputStream());
        self::assertSame('abc', (string) $stream);

        $output->headerSent = fn() => true;
        self::assertTrue($output->headersSent());

        $output->headerSent = fn() => false;
        self::assertFalse($output->headersSent());
    }

    #[Test]
    public function setOutputStreamAcceptsStreamAndFallsBackToPhpOutput(): void
    {
        $output = new Output();
        $stream = new Stream('php://memory', 'wb+');

        self::assertSame($output, $output->setOutputStream($stream));
        self::assertSame($stream, $output->getOutputStream());

        $output->setOutputStream(null);

        self::assertNotSame($stream, $output->getOutputStream());
    }

    #[Test]
    public function closeMakesOutputUnwritable(): void
    {
        $file = tempnam(sys_get_temp_dir(), 'ww-output-');
        $output = new Output(new Stream($file, 'wb'));

        try {
            self::assertTrue($output->isWritable());

            $output->close();

            self::assertFalse($output->isWritable());
        } finally {
            unlink($file);
        }
    }
}
