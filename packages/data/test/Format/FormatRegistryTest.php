<?php

declare(strict_types=1);

namespace Windwalker\Data\Test\Format;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use SplFileInfo;
use Windwalker\Data\Format\FormatRegistry;
use Windwalker\Data\Format\JsonFormat;

class FormatRegistryTest extends TestCase
{
    #[Test]
    public function itUsesTheDefaultFormatAndResolvesAliases(): void
    {
        $registry = new FormatRegistry;

        self::assertSame(['flower' => 'sakura'], $registry->parse('{"flower":"sakura"}'));
        self::assertSame('yaml', $registry->resolveFormatAlias('YML'));
        self::assertSame('yaml', $registry->resolveFileFormat('YML'));

        $registry->alias('short-json', 'JSON');

        self::assertSame('{"flower":"sakura"}', $registry->dump(['flower' => 'sakura'], 'short-json'));
    }

    #[Test]
    public function itRegistersAndRemovesFormatHandlers(): void
    {
        $registry = new FormatRegistry;
        $handler = new JsonFormat;

        self::assertSame($registry, $registry->registerFormat('JSON', $handler));
        self::assertSame($handler, $registry->getFormatHandler('json'));

        $registry->registerFormat(
            'custom',
            static fn(string $input, array $options): array => [$options['key'] => $input],
            static fn(mixed $data): string => $data['value']
        );

        self::assertSame(['result' => 'parsed'], $registry->parse('parsed', 'custom', ['key' => 'result']));
        self::assertSame('dumped', $registry->dump(['value' => 'dumped'], 'custom'));

        $registry->removeHandler('JSON');

        self::assertNotSame($handler, $registry->getFormatHandler('json'));
        self::assertInstanceOf(JsonFormat::class, $registry->getFormatHandler('json'));
    }

    #[Test]
    public function itRequiresAParserAndDumperPair(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIs('Dumper should be callable callable');

        (new FormatRegistry)->registerFormat('custom', static fn(): array => []);
    }

    #[Test]
    public function itLoadsFilesByExtensionAndAcceptsFileInfo(): void
    {
        $registry = new FormatRegistry;
        $json = __DIR__ . '/../fixtures/flower.json';

        self::assertSame('sakura', $registry->loadFile($json)['flower']);
        self::assertSame('sakura', $registry->loadFile(new SplFileInfo($json))['flower']);
        self::assertSame(
            require __DIR__ . '/../fixtures/flower.php',
            $registry->loadFile(__DIR__ . '/../fixtures/flower.php')
        );
    }

    #[Test]
    public function itConvertsNestedObjectsAndSpecialValuesToDumpableArrays(): void
    {
        $resource = fopen('php://memory', 'rb');
        $data = FormatRegistry::makeDumpable(
            [
                'nested' => ['name' => 'flower'],
                'callback' => static fn(): null => null,
                'resource' => $resource
            ]
        );
        fclose($resource);

        self::assertSame(['name' => 'flower'], $data['nested']);
        self::assertSame('[Object Closure]', $data['callback']);
        self::assertMatchesRegularExpression('/^\[resource #\d+]$/', $data['resource']);
    }
}
