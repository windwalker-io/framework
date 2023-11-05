<?php

declare(strict_types=1);

namespace Windwalker\Filesystem;

/**
 * The TempFileObject class.
 */
class TempFileObject extends FileObject
{
    public bool $deleteWhenDestruct = false;

    public function deleteWhenShutdown(): void
    {
        register_shutdown_function(fn () => $this->deleteIfExists());
    }

    public function deleteWhenDestruct(bool $value = false): void
    {
        $this->deleteWhenDestruct = $value;
    }

    /**
     * @inheritDoc
     */
    public function __destruct()
    {
        if ($this->deleteWhenDestruct) {
            $this->deleteIfExists();
        }
    }
}
