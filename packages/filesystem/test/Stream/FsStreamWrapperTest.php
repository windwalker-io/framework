<?php

declare(strict_types=1);

namespace Windwalker\Filesystem\Test\Stream;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Filesystem\Stream\FsStreamWrapper;
use Windwalker\Stream\StringStream;

/**
 * Test class of FsStreamWrapper
 */
class FsStreamWrapperTest extends TestCase
{
    #[Test]
    public function registeredWrapperExposesPsrStreamThroughNativeFunctions(): void
    {
        FsStreamWrapper::register('wwtest');

        try {
            self::assertTrue(FsStreamWrapper::register('wwtest'), 'Registering twice is a no-op');

            $uri = FsStreamWrapper::fetchUri('foo');

            self::assertSame('wwtest://foo', $uri);

            FsStreamWrapper::addStream('foo', new StringStream('Hello World'));

            $handle = fopen($uri, 'r+');

            self::assertSame('Hello', fread($handle, 5));
            self::assertSame(5, ftell($handle));
            self::assertFalse(feof($handle));
            self::assertSame(0, fseek($handle, 6));
            self::assertSame('World', fread($handle, 10));
            self::assertIsArray(fstat($handle));

            fclose($handle);
        } finally {
            FsStreamWrapper::unregister();
        }
    }

    #[Test]
    public function writingToWrappedStreamStoresDataInPsrStream(): void
    {
        FsStreamWrapper::register('wwtest2');

        try {
            $stream = new StringStream('');

            FsStreamWrapper::addStream('bar', $stream);

            $handle = fopen(FsStreamWrapper::fetchUri('bar'), 'w');
            fwrite($handle, 'Written');
            fclose($handle);

            self::assertSame('Written', (string) $stream);
        } finally {
            FsStreamWrapper::unregister();
        }
    }
}
