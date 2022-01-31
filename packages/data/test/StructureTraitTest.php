<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Data\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\Data\Collection;
use Windwalker\Data\StructureTrait;

use function Windwalker\fs;

/**
 * The StructureTraitTest class.
 *
 * @since  __DEPLOY_VERSION__
 */
class StructureTraitTest extends TestCase
{
    /**
     * @var StructureTrait
     */
    protected $instance;

    /**
     * @see  StructureTrait::withLoad
     */
    public function testWithLoad(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  StructureTrait::toString
     */
    public function testToString(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  StructureTrait::setFormatRegistry
     */
    public function testSetFormatRegistry(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  StructureTrait::load
     */
    public function testLoad(): void
    {
        $collection = new Collection();

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
        $collection = new Collection();

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
        $collection = new Collection();

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
    public function testGetFormatRegistry(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    protected function setUp(): void
    {
        $this->instance = null;
    }

    protected function tearDown(): void
    {
    }
}
