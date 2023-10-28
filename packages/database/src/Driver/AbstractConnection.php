<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Database\Driver;

use Windwalker\Pool\AbstractConnection as AbstractPoolConnection;
use Windwalker\Utilities\Options\OptionAccessTrait;

/**
 * The AbstractConnection class.
 */
abstract class AbstractConnection extends AbstractPoolConnection implements ConnectionInterface
{
    use OptionAccessTrait;

    /**
     * @var string
     */
    protected static string $name = '';

    /**
     * @var mixed
     */
    protected mixed $connection = null;

    /**
     * @var array
     */
    protected array $defaultOptions = [
        'dbname' => null,
        'host' => 'localhost',
        'user' => '',
        'password' => '',
    ];

    /**
     * AbstractConnection constructor.
     *
     * @param  array  $options
     */
    public function __construct(array $options)
    {
        $this->prepareOptions(
            $this->defaultOptions,
            $options
        );

        $this->prepare();
    }

    /**
     * isSupported
     *
     * @return  bool
     */
    abstract public static function isSupported(): bool;

    protected function prepare(): void
    {
        //
    }

    abstract public static function getParameters(array $options): array;

    /**
     * connect
     *
     * @return  mixed
     */
    public function connect(): mixed
    {
        if ($this->connection) {
            return $this->connection;
        }

        return $this->connection = $this->doConnect(static::getParameters($this->options));
    }

    abstract protected function doConnect(array $options);

    /**
     * disconnect
     *
     * @return  mixed
     */
    abstract public function disconnect(): mixed;

    /**
     * isConnected
     *
     * @return  bool
     */
    public function isConnected(): bool
    {
        return $this->connection !== null;
    }

    /**
     * @return mixed
     */
    public function get(): mixed
    {
        return $this->connection;
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return static::$name;
    }
}
