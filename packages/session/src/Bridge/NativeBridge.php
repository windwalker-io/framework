<?php

declare(strict_types=1);

namespace Windwalker\Session\Bridge;

use Windwalker\Session\Handler\HandlerInterface;
use Windwalker\Session\Handler\NativeHandler;
use Windwalker\Utilities\Options\OptionAccessTrait;

/**
 * The PhpBridge class.
 */
class NativeBridge implements BridgeInterface
{
    use OptionAccessTrait;

    protected HandlerInterface $handler;

    public protected(set) ?array $storageBackup = null;

    /**
     * NativeBridge constructor.
     *
     * @param  array                  $options
     * @param  HandlerInterface|null  $handler
     */
    public function __construct(array $options = [], ?HandlerInterface $handler = null)
    {
        $this->handler = $handler ?? new NativeHandler();

        $this->prepareOptions(
            [
                'auto_commit' => false,
            ],
            $options
        );
    }

    /**
     * start
     *
     * @return  bool
     */
    public function start(): bool
    {
        if ($this->isStarted()) {
            return true;
        }

        session_set_save_handler($this->handler);

        if ($this->getOption('auto_commit')) {
            // Call session_write_close when shutdown.
            session_register_shutdown();
        }

        if (!headers_sent()) {
            session_cache_limiter('private');
        }

        return session_start();
    }

    /**
     * isStarted
     *
     * @return  bool
     */
    public function isStarted(): bool
    {
        return $this->getStatus() === PHP_SESSION_ACTIVE;
    }

    /**
     * getId
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return session_id();
    }

    /**
     * setId
     *
     * @param  string  $id
     *
     * @return void
     */
    public function setId(string $id): void
    {
        session_id($id);
    }

    /**
     * getSessionName
     *
     * @return  string|null
     */
    public function getSessionName(): ?string
    {
        return session_name();
    }

    /**
     * setSessionName
     *
     * @param  string  $name
     *
     * @return  void
     */
    public function setSessionName(string $name): void
    {
        session_name($name);
    }

    /**
     * restart
     *
     * @param  bool  $deleteOld
     *
     * @return  mixed
     */
    public function restart(bool $deleteOld = false): bool
    {
        $return = $this->regenerate($deleteOld);

        $this->start();

        return $return;
    }

    /**
     * regenerate
     *
     * @param  bool  $deleteOld
     *
     * @param  bool  $saveOld
     *
     * @return  bool
     */
    public function regenerate(bool $deleteOld = false, bool $saveOld = true): bool
    {
        return session_regenerate_id($deleteOld);
    }

    /**
     * save
     *
     * @param  bool  $unset
     *
     * @return bool
     */
    public function writeClose(bool $unset = true): bool
    {
        $this->storageBackup = $_SESSION;

        $result = session_write_close();

        if ($unset) {
            $_SESSION = [];
        }

        return $result;
    }

    /**
     * destroy
     *
     * @return  void
     */
    public function destroy(): void
    {
        $this->storageBackup = $_SESSION;

        if ($this->getId()) {
            session_unset();
            session_destroy();
        }
    }

    /**
     * getStorage
     *
     * @return array
     */
    public function &getStorage(): mixed
    {
        if ($this->storageBackup !== null) {
            return $this->storageBackup;
        }

        return $_SESSION;
    }

    /**
     * getStatus
     *
     * @return  int
     */
    public function getStatus(): int
    {
        return session_status();
    }

    /**
     * unset
     *
     * @return  bool
     */
    public function unset(): bool
    {
        return session_unset();
    }

    /**
     * __clone
     *
     * @return  void
     */
    public function __clone()
    {
        $this->handler = clone $this->handler;
    }
}
