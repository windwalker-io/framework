<?php

declare(strict_types=1);

namespace Windwalker\Filesystem\Test;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Filesystem\TempFileObject;

/**
 * Test class of TempFileObject
 */
class TempFileObjectTest extends TestCase
{
    #[Test]
    public function destructDeletesFileWhenFlagEnabled(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'ww-temp-');
        $temp = new TempFileObject($path);
        $temp->deleteWhenDestruct(true);

        unset($temp);

        self::assertFileDoesNotExist($path);
    }

    #[Test]
    public function destructKeepsFileByDefault(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'ww-temp-');
        $temp = new TempFileObject($path);

        unset($temp);

        self::assertFileExists($path);

        unlink($path);
    }

    #[Test]
    public function deleteWhenShutdownRegistersShutdownCallback(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'ww-temp-');
        $script = tempnam(sys_get_temp_dir(), 'ww-script-');

        file_put_contents(
            $script,
            '<?php require ' . var_export(dirname(__DIR__, 3) . '/vendor/autoload.php', true) . ';'
            . '$f = new \Windwalker\Filesystem\TempFileObject(' . var_export($path, true) . ');'
            . '$f->deleteWhenShutdown();'
        );

        try {
            exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($script), $output, $code);

            self::assertSame(0, $code);
            self::assertFileDoesNotExist($path);
        } finally {
            @unlink($script);
            @unlink($path);
        }
    }
}
