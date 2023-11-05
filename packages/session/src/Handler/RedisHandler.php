<?php

declare(strict_types=1);

namespace Windwalker\Session\Handler;

use Predis\ClientInterface;
use Predis\Response\ErrorInterface;
use Redis;
use RedisArray;
use Windwalker\Utilities\Assert\ArgumentsAssert;
use Windwalker\Utilities\Options\OptionAccessTrait;

/**
 * The RedisHandler class.
 */
class RedisHandler extends AbstractHandler
{
    use OptionAccessTrait;

    /**
     * @var Redis|RedisArray|ClientInterface
     */
    protected $driver;

    /**
     * RedisHandler constructor.
     *
     * @param  ClientInterface|Redis|RedisArray|RedisCaster  $driver
     */
    public function __construct($driver, array $options = [])
    {
        ArgumentsAssert::assert(
            $driver instanceof Redis
            || $driver instanceof RedisArray
            || $driver instanceof ClientInterface,
            '{caller} argument 1 should be Redis instance, %s given.',
            $driver
        );

        $this->prepareOptions(
            [
                'prefix' => 'ww_sess_',
                'ttl' => null,
            ],
            $options
        );

        $this->driver = $driver;
    }

    public function getKey(string $id): string
    {
        $key = $id;

        if ($this->getOption('prefix')) {
            $key = $this->getOption('prefix') . $id;
        }

        return $key;
    }

    /**
     * doRead
     *
     * @param  string  $id
     *
     * @return  string|null
     */
    protected function doRead(string $id): ?string
    {
        $r = $this->getDriver()->get($this->getKey($id));

        if ($r === false || $r === null) {
            return null;
        }

        return (string) $r;
    }

    /**
     * isSupported
     *
     * @return  bool
     */
    public static function isSupported(): bool
    {
        return true;
    }

    /**
     * destroy
     *
     * @param  string  $id
     *
     * @return  bool
     */
    public function destroy($id): bool
    {
        $this->getDriver()->del($this->getKey($id));

        return true;
    }

    /**
     * gc
     *
     * @param  int  $maxlifetime
     *
     * @return  int|false  Returns the number of deleted sessions on success, or false on failure.
     */
    public function gc($maxlifetime): int|false
    {
        return 0;
    }

    /**
     * write
     *
     * @param  string  $id
     * @param  string  $data
     *
     * @return  bool
     */
    public function write($id, $data): bool
    {
        $result = $this->getDriver()
            ->setEx(
                $this->getKey($id),
                (int) ($this->ttl ?? $this->getOption('gc_maxlifetime') ?? ini_get('session.gc_maxlifetime')),
                $data
            );

        return $result && !$result instanceof ErrorInterface;
    }

    /**
     * updateTimestamp
     *
     * @param  string  $id
     * @param  string  $data
     *
     * @return  bool
     */
    public function updateTimestamp($id, $data): bool
    {
        return (bool) $this->getDriver()
            ->expire(
                $this->getKey($id),
                (int) ($this->ttl ?? $this->getOption('gc_maxlifetime') ?? ini_get('session.gc_maxlifetime'))
            );
    }

    /**
     * @return ClientInterface|Redis|RedisArray
     */
    public function getDriver(): ClientInterface|RedisCaster|Redis|RedisArray
    {
        return $this->driver;
    }
}
