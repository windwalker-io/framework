<?php

declare(strict_types=1);

namespace Windwalker\Session\Test\Handler;

use PDO;
use PDOException;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use Windwalker\Session\Handler\AbstractHandler;
use Windwalker\Session\Handler\HandlerInterface;
use Windwalker\Session\Handler\PdoHandler;

/**
 * Runs the shared handler contract against a real MySQL connection, covering the upsert SQL branch.
 */
class PdoHandlerMysqlTest extends AbstractHandlerTestCase
{
    protected ?PDO $db = null;

    /**
     * @var  PdoHandler
     */
    protected ?AbstractHandler $instance = null;

    /**
     * @see  PdoHandler::updateTimestamp
     */
    #[Test]
    public function updateTimestampRefreshesStoredTime(): void
    {
        $session = $this->createSession();

        $query = $this->db->prepare('SELECT time FROM windwalker_sessions WHERE id = ?');
        $query->execute([static::$sess1]);
        $lastTime = (int) $query->fetchColumn();

        $session->setId(static::$sess1);
        $session->start();
        $session->stop();

        $query->execute([static::$sess1]);

        self::assertGreaterThanOrEqual($lastTime, (int) $query->fetchColumn());
    }

    protected function createInstance(): HandlerInterface
    {
        $this->instance = new PdoHandler($this->db, ['gc_maxlifetime' => 1440]);
        $this->db->exec('DELETE FROM windwalker_sessions');
        $insert = $this->db->prepare('INSERT INTO windwalker_sessions (id, data, time) VALUES (?, ?, ?)');

        foreach ($this->prepareDefaultData() as $id => $item) {
            $insert->execute([$id, $item['data'], $item['time']]);
        }

        return $this->instance;
    }

    #[Before]
    protected function createMysqlDatabase(): void
    {
        $dsn = defined('WINDWALKER_TEST_DB_DSN_MYSQL') ? (string) constant('WINDWALKER_TEST_DB_DSN_MYSQL') : '';

        if ($dsn === '' || !in_array('mysql', PDO::getAvailableDrivers(), true)) {
            self::markTestSkipped('MySQL DSN and the pdo_mysql extension are required.');
        }

        parse_str(str_replace(';', '&', $dsn), $params);

        $suffix = preg_replace('/[^A-Za-z0-9_]/', '_', (string) getenv('WINDWALKER_TEST_DB_SUFFIX'));

        if ($suffix !== '' && !empty($params['dbname'])) {
            $params['dbname'] .= '_' . $suffix;
        }

        try {
            if ($suffix !== '') {
                $admin = new PDO(
                    sprintf('mysql:host=%s', $params['host'] ?? '127.0.0.1'),
                    $params['user'] ?? null,
                    $params['password'] ?? null,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                $admin->exec('CREATE DATABASE IF NOT EXISTS `' . $params['dbname'] . '`');

                $dbname = $params['dbname'];
                register_shutdown_function(static function () use ($admin, $dbname) {
                    try {
                        $admin->exec('DROP DATABASE IF EXISTS `' . $dbname . '`');
                    } catch (\Throwable) {
                    }
                });
            }

            $this->db = new PDO(
                sprintf('mysql:host=%s;dbname=%s', $params['host'] ?? '127.0.0.1', $params['dbname'] ?? ''),
                $params['user'] ?? null,
                $params['password'] ?? null,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException) {
            self::markTestSkipped('A running MySQL server is required.');
        }

        $this->prepareVfs();
        $this->db->exec('DROP TABLE IF EXISTS windwalker_sessions');
        $this->db->exec(
            'CREATE TABLE windwalker_sessions '
            . '(id varbinary(192) NOT NULL PRIMARY KEY, data text NOT NULL, time int NULL DEFAULT 0)'
        );
    }

    #[After]
    protected function dropTableAndCloseSession(): void
    {
        $this->session?->stop();
        $this->session = null;
        $this->instance = null;
        $this->db?->exec('DROP TABLE IF EXISTS windwalker_sessions');
        $this->db = null;
    }
}
