<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver;

use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use RuntimeException;
use Windwalker\Database\Driver\AbstractConnection;
use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\AbstractDatabaseDriverTestCase;
use Windwalker\Utilities\Arr;

/**
 * The AbstractConnectionTest class.
 */
abstract class AbstractConnectionTestCase extends AbstractDatabaseDriverTestCase
{
    protected static DbPlatform $platform = DbPlatform::MySQL;

    /**
     * @var  AbstractConnection
     */
    protected static string $className = AbstractConnection::class;

    /**
     * @var  AbstractConnection
     */
    protected $instance;

    protected static function setupDatabase(): void {}

    /**
     * @inheritDoc
     */
    public static function setUpBeforeClass(): void
    {
        $className = static::$className;

        if (!$className::isSupported()) {
            self::markTestSkipped('Driver for: ' . $className . ' not available.');
        }

        parent::setUpBeforeClass();
    }

    #[Before]
    protected function initializeConnection(): void
    {
        $this->instance = static::createConnection();
    }

    protected static function createConnection(): AbstractConnection
    {
        $className = static::$className;

        $options = Arr::only(
            $params = self::getTestParams(),
            [
                'host',
                'user',
                'password',
                'database',
                'port',
            ]
        );

        $options = static::prepareTestOptions($options);

        return new $className($options);
    }

    /**
     * assertConnected
     *
     * @param  AbstractConnection  $conn
     *
     * @return  void
     */
    abstract public function assertConnected(AbstractConnection $conn): void;

    public function testConnect(): void
    {
        $conn = $this->instance;
        $conn->connect();

        $this->assertConnected($conn);
    }

    public function testConnectWrong()
    {
        $conn = $this->instance;
        $conn->setOption('user', 'notexists');
        $conn->setOption('password', 'This-is-wrong-password');

        $this->expectException(RuntimeException::class);
        $conn->connect();
    }

    public function testDisconnect(): void
    {
        $conn = $this->instance;
        $conn->connect();

        $conn->disconnect();

        self::assertNull($conn->get());
    }

    #[After]
    protected function disconnectConnection(): void
    {
        $this->instance->disconnect();
        $this->instance = null;
    }
}
