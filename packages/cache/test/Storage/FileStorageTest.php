<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test\Storage;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Windwalker\Cache\Storage\FileStorage;
use Windwalker\Cache\Storage\PrunableStorageInterface;

/**
 * The FileStorageTest class.
 */
class FileStorageTest extends TestCase
{
    /**
     * @var FileStorage
     */
    protected $instance;

    /**
     * @var string
     */
    protected $root;

    /**
     * @see  FileStorage::clear
     */
    public function testClear(): void
    {
        foreach (range(1, 5) as $i) {
            $this->instance->save('foo' . $i, 'FOO' . $i);
        }

        $this->instance->clear();

        $path = $this->root . '/' . $this->instance::hashFilename('hello') . '.data';

        $files = glob($path . '/*');

        self::assertEmpty($files);
    }

    /**
     * @see  FileStorage::save
     */
    public function testSave(): void
    {
        $this->instance->save('hello', 'FOOOOOOOOOO');

        $path = $this->root . '/' . $this->instance::hashFilename('hello') . '.data';

        self::assertEquals(
            '/////---------- Expired At: 0 ----------/////FOOOOOOOOOO',
            file_get_contents($path)
        );
    }

    /**
     * @see  FileStorage::__construct
     */
    public function testConstruct(): void
    {
        $this->expectException(RuntimeException::class);

        new FileStorage(__FILE__);
    }

    /**
     * @see  FileStorage::get
     */
    public function testGet(): void
    {
        $this->instance->save('hello', 'FOOOOOOOOOO');

        $value = $this->instance->get('hello');

        self::assertEquals('FOOOOOOOOOO', $value);
    }

    /**
     * @see  FileStorage::fetchStreamUri
     */
    public function testFetchStreamUri(): void
    {
        self::markTestIncomplete(); // TODO: Complete this test
    }

    /**
     * @see  FileStorage::has
     */
    public function testHas(): void
    {
        $this->instance->save('hello', 'FOOOOOOOOOO');

        self::assertTrue($this->instance->has('hello'));
        self::assertFalse($this->instance->has('not_exists'));

        $this->instance->save('flower1', 'Sakura1', time() + 10);

        self::assertTrue($this->instance->has('flower1'));

        $this->instance->save('flower2', 'Sakura2', time() - 10);

        self::assertFalse($this->instance->has('flower2'));
    }

    public function testImplementsPrunableStorageInterface(): void
    {
        self::assertInstanceOf(PrunableStorageInterface::class, $this->instance);
    }

    public function testPrune(): void
    {
        $this->instance->save('expired1', 'Sakura1', time() - 10);
        $this->instance->save('expired2', 'Sakura2', time() - 1);
        $this->instance->save('active', 'Sakura3', time() + 60);
        $this->instance->save('forever', 'Sakura4', 0);

        self::assertSame(2, $this->instance->prune());
        self::assertFalse($this->instance->has('expired1'));
        self::assertFalse($this->instance->has('expired2'));
        self::assertTrue($this->instance->has('active'));
        self::assertTrue($this->instance->has('forever'));
        self::assertSame(0, $this->instance->prune());
    }

    /**
     * @see  FileStorage::remove
     */
    public function testRemove(): void
    {
        $this->instance->save('hello', 'FOOOOOOOOOO');

        $path = $this->root . '/' . $this->instance::hashFilename('hello') . '.data';

        self::assertFileExists($path);
        self::assertTrue($this->instance->has('hello'));

        $this->instance->remove('hello');

        self::assertFileDoesNotExist($path);
        self::assertFalse($this->instance->has('hello'));
    }

    protected function setUp(): void
    {
        $this->root = $path = dirname(__DIR__) . '/fixtures';

        $this->instance = new FileStorage($path, [], 0.0);

        $this->instance->clear();
    }

    protected function tearDown(): void
    {
        $this->instance->clear();
    }
}
