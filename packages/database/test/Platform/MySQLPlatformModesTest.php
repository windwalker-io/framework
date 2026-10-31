<?php

declare(strict_types=1);

namespace Windwalker\Database\Test\Platform;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Database\Test\AbstractDatabaseTestCase;

class MySQLPlatformModesTest extends AbstractDatabaseTestCase
{
    protected static function setupDatabase(): void {}

    private function sessionMode(): string
    {
        return (string) self::$db->prepare('SELECT @@SESSION.sql_mode')->result();
    }

    #[Test]
    public function setModesAppliesSessionSqlMode(): void
    {
        $platform = self::$db->getPlatform();
        $original = $this->sessionMode();

        try {
            $platform->setModes(['NO_ENGINE_SUBSTITUTION']);

            self::assertSame('NO_ENGINE_SUBSTITUTION', $this->sessionMode());
        } finally {
            self::$db->execute("SET @@SESSION.sql_mode = '$original'");
        }
    }

    #[Test]
    public function enableStrictModeUsesDefaultModes(): void
    {
        $platform = self::$db->getPlatform();
        $original = $this->sessionMode();

        try {
            $platform->enableStrictMode();

            self::assertStringContainsString('STRICT_TRANS_TABLES', $this->sessionMode());
        } finally {
            self::$db->execute("SET @@SESSION.sql_mode = '$original'");
        }
    }

    #[Test]
    public function listCheckConstraintsMapsNameToClause(): void
    {
        self::$baseConn->exec('DROP TABLE IF EXISTS ww_check_items');

        try {
            self::$baseConn->exec(
                'CREATE TABLE ww_check_items (qty INT, CONSTRAINT ww_qty_positive CHECK (qty > 0))'
            );

            $checks = self::$db->getPlatform()->listCheckConstraints(null, 'ww_check_items');

            self::assertTrue(isset($checks['ww_qty_positive']));
            self::assertStringContainsString('qty', (string) $checks['ww_qty_positive']);
        } finally {
            self::$baseConn->exec('DROP TABLE IF EXISTS ww_check_items');
        }
    }
}
