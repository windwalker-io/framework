<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Driver\Mysqli;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Driver\AbstractConnection;
use Windwalker\Database\Driver\DriverOptions;
use Windwalker\Database\Driver\Mysqli\MysqliConnection;
use Windwalker\Database\Platform\DbPlatform;
use Windwalker\Database\Test\Driver\AbstractConnectionTestCase;

/**
 * The MysqliConnectionTest class.
 */
class MysqliConnectionTest extends AbstractConnectionTestCase
{
    protected static DbPlatform $platform = DbPlatform::MySQL;

    protected static string $className = MysqliConnection::class;

    public function assertConnected(AbstractConnection $conn): void
    {
        $mysqli = $conn->get();

        $r = $mysqli->query('SELECT 1')->fetch_row();

        self::assertEquals([1], $r);
    }

    #[Test]
    public function isSupported(): void
    {
        self::assertSame(extension_loaded('mysqli'), MysqliConnection::isSupported());
    }

    #[Test]
    public function ping(): void
    {
        self::assertTrue($this->instance->ping());

        $this->instance->disconnect();
        $this->instance->setOption('password', 'wrong-password-xyz');
        $this->instance->setOption('user', 'notexists');

        self::assertFalse($this->instance->ping());
    }

    #[Test]
    public function prepareDbOptionsWithoutTls(): void
    {
        $options = MysqliConnection::prepareDbOptions(new DriverOptions());

        self::assertArrayNotHasKey('tls', $options->extra);
    }

    #[Test]
    public function prepareDbOptionsWithTls(): void
    {
        $options = MysqliConnection::prepareDbOptions(
            new DriverOptions(tls: ['ca' => '/ca.pem'])
        );

        self::assertSame(
            ['key' => null, 'cert' => null, 'ca' => '/ca.pem', 'capath' => null, 'cipherAlgos' => null],
            $options->extra['tls']
        );
    }
}
