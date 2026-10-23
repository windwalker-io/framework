<?php

declare(strict_types=1);

namespace Windwalker\Session\Test\Handler;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Windwalker\Session\Bridge\PhpBridge;
use Windwalker\Session\Handler\FilesystemHandler;
use Windwalker\Session\Test\SessionVfsTestTrait;

class FilesystemHandlerTest extends TestCase
{
    use SessionVfsTestTrait;

    #[DataProvider('invalidIdProvider')]
    public function testRejectsInvalidId(string $id, string $operation, string $prefix): void
    {
        $handler = new FilesystemHandler(self::getSessionPath(), ['prefix' => $prefix]);

        $this->expectException(InvalidArgumentException::class);

        match ($operation) {
            'write' => $handler->write($id, 'session data'),
            'updateTimestamp' => $handler->updateTimestamp($id, 'session data'),
            default => $handler->$operation($id),
        };
    }

    public static function invalidIdProvider(): iterable
    {
        $ids = [
            '',
            '.',
            '..',
            '../outside',
            'x/../../outside',
            'x\\..\\..\\outside',
            '/tmp/outside',
            'C:\\outside',
            'php://filter/resource=outside',
            '%2e%2e%2foutside',
            "valid\0outside",
            "valid\n",
            'with space',
            'with_underscore',
            '中文',
        ];

        foreach ($ids as $index => $id) {
            foreach (['sess_', '', 'custom_'] as $prefix) {
                foreach (['getFileName', 'getFilePath', 'read', 'write', 'destroy', 'updateTimestamp', 'validateId'] as $operation) {
                    yield "$index:$prefix:$operation" => [$id, $operation, $prefix];
                }
            }
        }
    }

    #[DataProvider('validIdProvider')]
    public function testValidId(string $id, string $prefix): void
    {
        $handler = new FilesystemHandler(self::getSessionPath(), ['prefix' => $prefix]);
        $path = self::getSessionPath($prefix . $id);
        $data = serialize(['user' => 'test']);

        self::assertSame($prefix . $id, $handler->getFileName($id));
        self::assertSame($path, $handler->getFilePath($id));
        self::assertTrue($handler->write($id, $data));
        self::assertSame($data, $handler->read($id));
        self::assertTrue($handler->validateId($id));
        self::assertSame($data, $handler->read($id));

        touch($path, time() - 60);

        self::assertTrue($handler->updateTimestamp($id, $data));
        clearstatcache(true, $path);
        self::assertGreaterThanOrEqual(time() - 1, filemtime($path));
        self::assertTrue($handler->destroy($id));
        self::assertFileDoesNotExist($path);
        self::assertSame('', $handler->read($id));
    }

    public static function validIdProvider(): iterable
    {
        foreach (['abc123', 'ABCdef0123456789,-', str_repeat('a', 32)] as $id) {
            foreach (['sess_', '', 'custom_'] as $prefix) {
                yield "$id:$prefix" => [$id, $prefix];
            }
        }
    }

    #[DataProvider('strictModeProvider')]
    public function testBridgeRejectsTraversalBeforeFilesystemAccess(bool $strictMode): void
    {
        $this->prepareVfs(
            [
                'outside' => serialize(['user' => 'outside']),
                'tmp' => ['sess_x' => []],
            ]
        );

        $handler = new FilesystemHandler(self::getSessionPath());
        $bridge = new PhpBridge(['use_strict_mode' => $strictMode], $handler);
        $bridge->setId('x/../../outside');

        $this->expectException(InvalidArgumentException::class);

        $bridge->start();
    }

    public static function strictModeProvider(): iterable
    {
        yield 'strict' => [true];
        yield 'non-strict' => [false];
    }

    #[DataProvider('gcPrefixProvider')]
    public function testGcOnlyDeletesExpiredSessionFiles(string $prefix): void
    {
        $this->prepareVfs(['tmp' => []]);

        $handler = new FilesystemHandler(self::getSessionPath(), ['prefix' => $prefix]);
        $effectivePrefix = $prefix ?: '';
        $expiredIds = ['expiredABC123,-', 'expired2'];

        foreach ($expiredIds as $id) {
            $handler->write($id, 'expired data');
            touch($handler->getFilePath($id), time() - 3600);
        }

        $handler->write('fresh', 'fresh data');

        $preservedNames = [
            'unrelated.txt',
            'foreign_sess_ABC123',
            $effectivePrefix . 'invalid_id',
            $effectivePrefix . 'valid.bak',
            $effectivePrefix . "invalid\nsuffix",
        ];

        if ($effectivePrefix !== '') {
            $preservedNames[] = $effectivePrefix;
            $preservedNames[] = 'plainABC123';
        }

        foreach ($preservedNames as $name) {
            $path = self::getSessionPath($name);
            file_put_contents($path, 'unrelated data');
            touch($path, time() - 3600);
        }

        $directory = self::getSessionPath($effectivePrefix . 'directory');
        mkdir($directory);
        file_put_contents($directory . '/' . $effectivePrefix . 'nested', 'nested data');
        touch($directory . '/' . $effectivePrefix . 'nested', time() - 3600);
        touch($directory, time() - 3600);
        clearstatcache();

        self::assertSame(2, $handler->gc(60));

        foreach ($expiredIds as $id) {
            self::assertFileDoesNotExist($handler->getFilePath($id));
        }

        self::assertSame('fresh data', $handler->read('fresh'));

        foreach ($preservedNames as $name) {
            self::assertSame('unrelated data', file_get_contents(self::getSessionPath($name)));
        }

        self::assertDirectoryExists($directory);
        self::assertSame('nested data', file_get_contents($directory . '/' . $effectivePrefix . 'nested'));
        self::assertSame(0, $handler->gc(60));
    }

    public static function gcPrefixProvider(): iterable
    {
        yield 'default prefix' => ['sess_'];
        yield 'custom prefix' => ['custom_'];
        yield 'literal regex characters' => ['custom.[+](#)_'];
        yield 'empty prefix' => [''];
        yield 'zero prefix' => ['0'];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->prepareVfs();
    }
}
