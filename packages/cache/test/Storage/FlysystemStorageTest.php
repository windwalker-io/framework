<?php

declare(strict_types=1);

namespace Windwalker\Cache\Test\Storage;

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\MimeTypeDetection\ExtensionMimeTypeDetector;
use League\MimeTypeDetection\GeneratedExtensionToMimeTypeMap;
use Windwalker\Cache\Storage\FlysystemStorage;

/**
 * The FileStorageTest class.
 */
class FlysystemStorageTest extends FileStorageTest
{
    /**
     * @var FlysystemStorage
     */
    protected $instance;

    /**
     * setUp
     *
     * @return  void
     */
    protected function setUp(): void
    {
        $this->root = $path = dirname(__DIR__) . '/fixtures/';

        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $fly = new Filesystem(
            new LocalFilesystemAdapter(
                $path,
                null,
                LOCK_EX,
                LocalFilesystemAdapter::DISALLOW_LINKS,
                new ExtensionMimeTypeDetector(
                    new GeneratedExtensionToMimeTypeMap()
                )
            )
        );

        $this->instance = new FlysystemStorage($fly);

        $this->instance->clear();
    }

    protected function tearDown(): void
    {
        $this->instance->clear();
    }
}
