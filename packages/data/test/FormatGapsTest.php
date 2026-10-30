<?php

declare(strict_types=1);

namespace Windwalker\Data\Test;

use ErrorException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Data\Collection;
use Windwalker\Data\Format\CallbackFormatHandler;
use Windwalker\Data\Format\FormatRegistry;
use Windwalker\Data\Format\IniFormat;
use Windwalker\Data\Format\PhpFormat;
use Windwalker\Data\Format\TomlFormat;
use Windwalker\Data\Format\XmlFormat;

class FormatGapsTest extends TestCase
{
    #[Test]
    public function callbackHandlerUsesGivenOrDefaultCallables(): void
    {
        $handler = new CallbackFormatHandler(
            static fn(string $s, array $o) => strtoupper($s),
            static fn(mixed $d, array $o) => json_encode($d)
        );

        self::assertSame('ABC', $handler->parse('abc'));
        self::assertSame('[1]', $handler->dump([1]));
        self::assertIsCallable($handler->getParser());
        self::assertIsCallable($handler->getDumper());

        $default = new CallbackFormatHandler();

        self::assertSame('', $default->parse('abc'));
        self::assertSame('', $default->dump(['a']));
    }

    #[Test]
    public function iniDumpSkipsListsAndNumericKeysAndEscapesStrings(): void
    {
        $ini = new IniFormat();

        $result = $ini->dump([
            'list' => [1, 2],
            'text' => "a\"b\nc",
            'num' => 1.5,
            'off' => false,
            'sec' => ['a' => 1, 5 => 'skip'],
        ]);

        self::assertSame("text=\"a\\\"b\\nc\"\nnum=1.5\noff=false\n\n[sec]\na=1", $result);
    }

    #[Test]
    public function iniParseWrapsErrorsWithLineNumbers(): void
    {
        set_error_handler(static function (int $no, string $str): never {
            throw new \ErrorException($str, 0, $no);
        });

        try {
            $this->expectException(ErrorException::class);

            new IniFormat()->parse("a=1\n[b");
        } finally {
            restore_error_handler();
        }
    }

    #[Test]
    public function phpFormatSupportsReturnOptionsAndNestedValues(): void
    {
        $php = new PhpFormat();

        $str = $php->dump(
            ['a' => null, 'b' => true, 'c' => 1.5, 'd' => "it's", 'e' => ['x'], 'f' => (object) ['k' => 1]],
            ['return' => true, 'strict' => false, 'header' => '// head', 'closingtag' => true]
        );

        self::assertStringStartsWith("<?php\n// head\n\nreturn [", $str);
        self::assertStringNotContainsString('strict_types', $str);
        self::assertStringEndsWith("];\n\n?>", $str);

        $data = eval('?>' . $str);

        self::assertNull($data['a']);
        self::assertTrue($data['b']);
        self::assertSame("it's", $data['d']);
        self::assertSame(['x'], $data['e']);
        self::assertSame(['k' => 1], $data['f']);
        self::assertStringContainsString('declare(strict_types=1);', $php->dump([1], ['return' => true]));
    }

    #[Test]
    public function xmlFormatParsesAllScalarTypesAndObjects(): void
    {
        $xml = new XmlFormat();

        $data = $xml->parse(
            '<Data>'
            . '<node name="s" type="string">x</node>'
            . '<node name="d" type="double">1.5</node>'
            . '<node name="b" type="boolean">1</node>'
            . '<node name="o" type="object"><node name="k" type="integer">3</node></node>'
            . '</Data>'
        );

        self::assertSame('x', $data['s']);
        self::assertSame(1.5, $data['d']);
        self::assertTrue($data['b']);
        self::assertEquals((object) ['k' => 3], $data['o']);
    }

    #[Test]
    public function tomlDumpsObjectsTraversablesAndNestedTables(): void
    {
        $toml = new TomlFormat();

        $expected = $toml->dump(['a' => 1, 'sec' => ['b' => 'x']]);

        self::assertSame($expected, $toml->dump((object) ['a' => 1, 'sec' => ['b' => 'x']]));
        self::assertSame($expected, $toml->dump(new \ArrayIterator(['a' => 1, 'sec' => ['b' => 'x']])));
        self::assertStringContainsString('[sec]', $expected);
    }

    #[Test]
    public function registryValidatesHandlersAndRegistersCallbacks(): void
    {
        $registry = new FormatRegistry();

        $registry->registerFormat('up', static fn($s) => strtoupper($s), static fn($d) => (string) $d);

        self::assertSame('ABC', $registry->parse('abc', 'up'));

        $registry->clearAlias()->clearExtMap();

        $this->expectException(\InvalidArgumentException::class);

        $registry->registerFormat('bad', static fn($s) => $s);
    }

    #[Test]
    public function collectionConvertsToFormatsByMagicMethods(): void
    {
        $c = new Collection(['a' => 1, 'b' => ['c' => 2]]);

        self::assertSame('{"a":1,"b":{"c":2}}', $c->toJson());
        self::assertStringContainsString('<node name="a" type="integer">1</node>', $c->toXml());
        self::assertStringContainsString('a: 1', $c->toYaml());
        self::assertStringContainsString('a=1', $c->toIni());
        self::assertStringContainsString("'a' => 1", $c->toPhpString());
        self::assertStringContainsString('a = 1', $c->toToml());
        self::assertStringContainsString('a: 1', $c->toHjson());
    }

    #[Test]
    public function collectionFromReturnsSameInstanceAndExtractsReferences(): void
    {
        $c = new Collection(['a' => ['b' => 1]]);

        self::assertSame($c, Collection::from($c));
        self::assertSame(['b' => 1], Collection::from(['a' => ['b' => 1]])->extract('a')->dump());
        self::assertTrue($c->proxy('new')->isProxy());
        self::assertSame([], $c->extract('new')->dump());
    }
}
