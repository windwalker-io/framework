<?php

declare(strict_types=1);

namespace Windwalker\Data\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Data\Collection;
use Windwalker\Data\Format\FormatRegistry;

use function Windwalker\fs;

/**
 * The StructureTraitTest class.
 *
 * @since  __DEPLOY_VERSION__
 */
class StructureTraitTest extends TestCase
{
    /**
     * @see  StructureTrait::withLoad
     */
    #[Test]
    public function testWithLoad(): void
    {
        $collection = new Collection(['original' => true]);
        $loaded = $collection->withLoad('{"added": true}', 'json');

        self::assertNotSame($collection, $loaded);
        self::assertSame(['original' => true], $collection->dump());
        self::assertEquals(['original' => true, 'added' => true], $loaded->dump());
    }

    /**
     * @see  StructureTrait::toString
     */
    #[Test]
    public function testToString(): void
    {
        $collection = new Collection(['flower' => 'sakura']);

        self::assertSame('{"flower":"sakura"}', $collection->toString());
        self::assertSame(
            ['flower' => 'sakura'],
            json_decode($collection->toString(), true)
        );
    }

    /**
     * @see  StructureTrait::setFormatRegistry
     */
    #[Test]
    public function testSetFormatRegistry(): void
    {
        $registry = new FormatRegistry;
        $registry->registerFormat(
            'custom',
            static fn(string $input): array => ['value' => $input],
            static fn(mixed $data): string => $data['value']
        );
        $collection = new Collection;

        self::assertSame($collection, $collection->setFormatRegistry($registry));
        $collection->load('parsed', 'custom');

        self::assertSame(['value' => 'parsed'], $collection->dump());
        self::assertSame('parsed', $collection->toString('custom'));
    }

    /**
     * @see  StructureTrait::load
     */
    public function testLoad(): void
    {
        $collection = new Collection;

        $this->assertEquals(
            $collection->reset()->load(
                file_get_contents(__DIR__ . '/fixtures/flower.json'),
                'json'
            )->get('flower'),
            'sakura'
        );
        $this->assertEquals(
            $collection->reset()->load(
                file_get_contents(__DIR__ . '/fixtures/flower.yml'),
                'yaml'
            )->get('flower'),
            'sakura'
        );
        $this->assertEquals(
            $collection->reset()->load(
                file_get_contents(__DIR__ . '/fixtures/flower.ini'),
                'ini'
            )->get('flower'),
            'sakura'
        );
        $this->assertEquals(
            $collection->reset()->load(
                file_get_contents(__DIR__ . '/fixtures/flower.xml'),
                'xml'
            )->get('flower'),
            'sakura'
        );
        // laktak/hjson not support php8.1 now
        // $this->assertEquals(
        //     $collection->reset()->load(
        //         file_get_contents(__DIR__ . '/fixtures/flower.hjson'),
        //         'hjson'
        //     )->get('flower'),
        //     'sakura'
        // );
        $this->assertEquals(
            $collection->reset()->load(
                file_get_contents(__DIR__ . '/fixtures/flower.toml'),
                'toml',
                ['load_raw' => true]
            )->get('flower'),
            'sakura'
        );
    }

    /**
     * @see  StructureTrait::load
     */
    public function testLoadFile(): void
    {
        $collection = new Collection;

        $this->assertEquals(
            $collection->reset()->loadFile(
                __DIR__ . '/fixtures/flower.json',
                'json'
            )->get('flower'),
            'sakura'
        );
        $this->assertEquals(
            $collection->reset()->loadFile(
                __DIR__ . '/fixtures/flower.yml',
                'yaml'
            )->get('flower'),
            'sakura'
        );
        $this->assertEquals(
            $collection->reset()->loadFile(
                __DIR__ . '/fixtures/flower.ini',
                'ini'
            )->get('flower'),
            'sakura'
        );
        $this->assertEquals(
            $collection->reset()->loadFile(
                __DIR__ . '/fixtures/flower.xml',
                'xml'
            )->get('flower'),
            'sakura'
        );
        $this->assertEquals(
            $collection->reset()->loadFile(
                __DIR__ . '/fixtures/flower.hjson',
                'hjson'
            )->get('flower'),
            'sakura'
        );
        $this->assertEquals(
            $collection->reset()->loadFile(
                __DIR__ . '/fixtures/flower.toml',
                'toml',
                ['load_raw' => true]
            )->get('flower'),
            'sakura'
        );
    }

    /**
     * @see  StructureTrait::load
     */
    public function testLoadFileAndStream(): void
    {
        $collection = new Collection;

        $this->assertEquals(
            $collection->reset()->loadFile(
                fs(__DIR__ . '/fixtures/flower.json'),
                'json'
            )->get('flower'),
            'sakura'
        );

        $this->assertEquals(
            $collection->reset()->load(
                fs(__DIR__ . '/fixtures/flower.json')->getStream(),
                'json'
            )->get('flower'),
            'sakura'
        );
    }

    /**
     * @see  StructureTrait::getFormatRegistry
     */
    #[Test]
    public function testGetFormatRegistry(): void
    {
        $collection = new Collection;

        self::assertInstanceOf(FormatRegistry::class, $collection->getFormatRegistry());
        self::assertSame($collection->getFormatRegistry(), $collection->getFormatRegistry());
    }

}
