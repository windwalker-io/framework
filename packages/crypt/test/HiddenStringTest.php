<?php

declare(strict_types=1);

namespace Windwalker\Crypt\Test;

use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Windwalker\Crypt\HiddenString;
use Windwalker\Crypt\Key;

class HiddenStringTest extends TestCase
{
    public static function secretClasses(): array
    {
        return [
            'hidden string' => [HiddenString::class],
            'key' => [Key::class],
        ];
    }

    #[DataProvider('secretClasses')]
    public function testDebugOutputIsRedacted(string $class): void
    {
        $secret = new $class('synthetic-secret-value');

        self::assertSame(['value' => '[redacted]'], $secret->__debugInfo());

        ob_start();

        try {
            var_dump($secret);
            $output = ob_get_contents();
        } finally {
            ob_end_clean();
        }

        self::assertStringNotContainsString('synthetic-secret-value', $output);
        self::assertStringContainsString('[redacted]', $output);
        self::assertSame('synthetic-secret-value', $secret->get());
    }

    #[DataProvider('secretClasses')]
    public function testSerializationIsRejected(string $class): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Secret values cannot be serialized.');

        serialize(new $class('synthetic-secret-value'));
    }

    #[DataProvider('secretClasses')]
    public function testUnserializationIsRejected(string $class): void
    {
        $payload = sprintf('O:%d:"%s":0:{}', strlen($class), $class);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Secret values cannot be unserialized.');

        unserialize($payload);
    }

    #[DataProvider('secretClasses')]
    public function testBinaryValueAndRepeatedDestruction(string $class): void
    {
        $binary = pack('C*', ...range(0, 255));
        $secret = new $class($binary);

        self::assertSame($binary, $secret->get());
        self::assertSame($binary, $secret->get(false));

        $secret->__destruct();
        $secret->__destruct();

        self::assertSame(pack('C*', ...range(0, 255)), $binary);
    }

    #[DataProvider('secretClasses')]
    public function testUninitializedSecretCanBeDestroyed(string $class): void
    {
        $this->expectNotToPerformAssertions();

        $secret = new ReflectionClass($class)->newInstanceWithoutConstructor();
        $secret->__destruct();
    }
}
