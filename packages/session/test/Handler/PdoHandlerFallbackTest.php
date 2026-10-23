<?php

declare(strict_types=1);

namespace Windwalker\Session\Test\Handler;

use PDO;
use PDOStatement;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Windwalker\Session\Handler\PdoHandler;

class PdoHandlerFallbackTest extends TestCase
{
    #[DataProvider('sessionProvider')]
    public function testWriteUpdatesOnlyMatchingSession(string $id, array $options): void
    {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            self::markTestSkipped('The pdo_sqlite extension is required.');
        }

        $storage = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $table = $options['table'];
        $columns = $options['columns'];

        $storage->exec(
            sprintf(
                'CREATE TABLE "%s" ("%s" TEXT PRIMARY KEY, "%s" TEXT NOT NULL, "%s" INTEGER NOT NULL)',
                $table,
                $columns['id'],
                $columns['data'],
                $columns['time']
            )
        );

        $insert = $storage->prepare(
            sprintf(
                'INSERT INTO "%s" ("%s", "%s", "%s") VALUES (?, ?, ?)',
                $table,
                $columns['id'],
                $columns['data'],
                $columns['time']
            )
        );
        $insert->execute([$id, 'old data', 100]);
        $insert->execute(['other-session', 'untouched data', 200]);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('getAttribute')->with(PDO::ATTR_DRIVER_NAME)->willReturn('pgsql');
        $pdo->expects($this->once())->method('beginTransaction')->willReturnCallback($storage->beginTransaction(...));
        $pdo->expects($this->once())->method('commit')->willReturnCallback($storage->commit(...));
        $pdo->method('rollBack')->willReturnCallback($storage->rollBack(...));
        $pdo->expects($this->exactly(2))->method('prepare')->willReturnCallback(
            static function (string $sql) use ($storage): PDOStatement {
                // Exercise PostgreSQL's fallback SQL; SQLite only needs the row-lock suffix removed.
                $sql = preg_replace('/\s+FOR UPDATE\s*$/i', '', $sql);

                return $storage->prepare($sql);
            }
        );

        $before = time();
        $handler = new PdoHandler($pdo, $options);

        self::assertTrue($handler->write($id, 'new data'));

        $select = $storage->prepare(sprintf('SELECT * FROM "%s" WHERE "%s" = ?', $table, $columns['id']));
        $select->execute([$id]);
        $updated = $select->fetch(PDO::FETCH_ASSOC);

        self::assertSame($id, $updated[$columns['id']]);
        self::assertSame('new data', $updated[$columns['data']]);
        self::assertGreaterThanOrEqual($before, (int) $updated[$columns['time']]);

        $select->execute(['other-session']);

        self::assertSame(
            [
                $columns['id'] => 'other-session',
                $columns['data'] => 'untouched data',
                $columns['time'] => 200,
            ],
            $select->fetch(PDO::FETCH_ASSOC)
        );
        self::assertSame(2, (int) $storage->query(sprintf('SELECT COUNT(*) FROM "%s"', $table))->fetchColumn());
    }

    public static function sessionProvider(): iterable
    {
        $defaults = [
            'table' => 'windwalker_sessions',
            'columns' => ['id' => 'id', 'data' => 'data', 'time' => 'time'],
        ];

        yield 'default columns' => ['target-session', $defaults];
        yield 'bound ID' => ["target' OR '1'='1", $defaults];
        yield 'custom columns' => [
            'target-session',
            [
                'table' => 'custom_sessions',
                'columns' => ['id' => 'session_id', 'data' => 'payload', 'time' => 'updated_at'],
            ],
        ];
    }
}
