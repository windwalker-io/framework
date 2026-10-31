<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Output;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Http\Output\NoHeaderOutput;
use Windwalker\Http\Response\Response;

class NoHeaderOutputTest extends TestCase
{
    #[Test]
    public function headerDoesNothingAndReturnsSelf(): void
    {
        $output = new NoHeaderOutput();

        self::assertSame($output, $output->header('X-Foo: bar'));
    }

    #[Test]
    public function respondWritesBodyWithoutSendingHeaders(): void
    {
        $output = new NoHeaderOutput();
        $output->headerSent = fn() => false;

        ob_start();
        $output->respond(Response::fromString('Body', 201, ['X-Foo' => 'bar']));
        $content = ob_get_clean();

        self::assertSame('Body', $content);
    }
}
