<?php

declare(strict_types=1);

namespace Windwalker\Session\Test\Handler;

use PDO;
use Windwalker\Session\Handler\AbstractHandler;
use Windwalker\Session\Handler\HandlerInterface;
use Windwalker\Session\Handler\PdoHandler;

/**
 * The PdoHandlerTest class.
 */
class PdoHandlerTest extends AbstractHandlerTestCase
{
    protected ?PDO $db = null;

    /**
     * @var PdoHandler
     */
    protected ?AbstractHandler $instance = null;

    /**
     * @see  PdoHandler::updateTimestamp
     */
    public function testUpdateTimestamp(): void
    {
        $session = $this->createSession();

        $query = $this->db->prepare('SELECT time FROM windwalker_sessions WHERE id = ?');
        $query->execute([static::$sess1]);
        $lastTime = (int) $query->fetchColumn();

        $session->setId(static::$sess1);
        $session->start();
        $session->stop();

        $query->execute([static::$sess1]);
        $newTime = (int) $query->fetchColumn();

        self::assertGreaterThan($lastTime, $newTime);
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

    protected function setUp(): void
    {
        parent::setUp();

        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            self::markTestSkipped('The pdo_sqlite extension is required.');
        }

        $this->prepareVfs();
        $this->db = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $this->db->exec(
            'CREATE TABLE windwalker_sessions (id TEXT PRIMARY KEY, data TEXT NOT NULL, time INTEGER NOT NULL)'
        );
    }

    protected function tearDown(): void
    {
        $this->session?->stop();
        $this->session = null;
        $this->instance = null;
        $this->db = null;

        parent::tearDown();
    }
}
