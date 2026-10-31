<?php

declare(strict_types=1);

namespace Windwalker\Session\Test\Bridge;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Filesystem\Filesystem;
use Windwalker\Session\Bridge\NativeBridge;

/**
 * The PhpBridgeTest class.
 */
class NativeBridgeTest extends TestCase
{
    protected ?NativeBridge $instance;

    protected static string $sess1 = '93cd6b3ec9f36b23d68e9385942dc41c';

    protected static string $sess2 = 'fa0a731220e28af75afba7135723015e';

    protected static function resetSessions(): void
    {
        session_save_path(static::getSessionPath());

        foreach (Filesystem::glob(self::getSessionPath() . '/sess_*') as $fileObject) {
            $fileObject->delete();
        }

        $buffer = 'flower|s:6:"Sakura";animal|s:3:"Cat";';

        file_put_contents(self::getSessionPath() . '/' . 'sess_' . static::$sess1, $buffer);

        $buffer = 'flower|s:4:"Rose";tree|s:3:"Oak";';

        file_put_contents(self::getSessionPath() . '/' . 'sess_' . static::$sess2, $buffer);
    }

    protected static function getSessionPath(): string
    {
        return __DIR__ . '/../../tmp';
    }

    /**
     * @see NativeBridge::start
     */
    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function startLoadsAndPersistsSessionData(): void
    {
        self::resetSessions();

        session_id(static::$sess1);

        $this->instance->start();

        self::assertEquals(
            [
                'flower' => 'Sakura',
                'animal' => 'Cat',
            ],
            $_SESSION
        );

        $this->instance->writeClose();

        self::assertEquals(
            [],
            $_SESSION
        );

        session_id(static::$sess2);

        $this->instance->start();

        self::assertEquals(
            [
                'flower' => 'Rose',
                'tree' => 'Oak',
            ],
            $_SESSION
        );

        $_SESSION['animal'] = 'Bird';

        $this->instance->writeClose();

        self::assertEquals(
            'flower|s:4:"Rose";tree|s:3:"Oak";animal|s:4:"Bird";',
            file_get_contents(self::getSessionPath() . '/' . 'sess_' . static::$sess2),
        );
    }

    /**
     * @see NativeBridge::destroy
     */
    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function destroyDeletesSessionFileAndClearsStorage(): void
    {
        self::resetSessions();

        $this->instance->setId(static::$sess1);
        $this->instance->start();

        self::assertFileExists(self::getSessionPath() . '/' . 'sess_' . static::$sess1);

        $this->instance->destroy();

        self::assertFileDoesNotExist(self::getSessionPath() . '/' . 'sess_' . static::$sess1);
        self::assertEquals([], $_SESSION);
    }

    /**
     * @see NativeBridge::start
     */
    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function startUsesSessionIdFromCookie(): void
    {
        static::resetSessions();

        $_COOKIE['PHPSESSID'] = 'windwalkersessions';

        $this->instance->start();

        $_SESSION['framework'] = 'Windwalker';

        $this->instance->writeClose();

        self::assertEquals(
            'framework|s:10:"Windwalker";',
            file_get_contents(self::getSessionPath() . '/sess_' . $_COOKIE['PHPSESSID'])
        );
    }

    /**
     * @see NativeBridge::setSessionName
     */
    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function setSessionNameUsesCustomCookieName(): void
    {
        static::resetSessions();

        $name = 'WINDWALKER_SESSID';

        $_COOKIE[$name] = 'windwalkersessions';

        $this->instance->setSessionName($name);
        $this->instance->start();

        $_SESSION['framework'] = 'Windwalker';

        $this->instance->writeClose();

        self::assertEquals(
            'framework|s:10:"Windwalker";',
            file_get_contents(self::getSessionPath() . '/sess_' . $_COOKIE[$name])
        );

        self::assertEquals(
            $name,
            $this->instance->getSessionName()
        );
    }

    /**
     * @see NativeBridge::isStarted
     */
    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function restartKeepsOldSessionFileWhenDeleteOldIsFalse(): void
    {
        self::resetSessions();

        session_id(static::$sess1);

        self::assertFalse($this->instance->isStarted());

        $this->instance->start();

        self::assertTrue($this->instance->isStarted());

        $this->instance->restart(false);

        $newId = $this->instance->getId();

        $this->instance->writeClose();

        self::assertFileEquals(
            self::getSessionPath() . '/sess_' . static::$sess1,
            self::getSessionPath() . '/sess_' . $newId,
        );
    }

    /**
     * @see NativeBridge::restart
     */
    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function restartDeletesOldSessionFileWhenDeleteOldIsTrue(): void
    {
        self::resetSessions();

        session_id(static::$sess1);

        $this->instance->start();

        $this->instance->restart(true);

        $newId = $this->instance->getId();

        $this->instance->writeClose();

        self::assertFileDoesNotExist(
            self::getSessionPath() . '/sess_' . static::$sess1,
        );

        self::assertEquals(
            'flower|s:6:"Sakura";animal|s:3:"Cat";',
            file_get_contents(self::getSessionPath() . '/sess_' . $newId),
        );
    }

    /**
     * @see NativeBridge::getId
     */
    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function getIdReturnsCurrentSessionId(): void
    {
        session_id('qwe');

        self::assertEquals(
            'qwe',
            $this->instance->getId()
        );
    }

    /**
     * @see NativeBridge::regenerate
     */
    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function regenerateKeepsOldSessionFileWhenDeleteOldIsFalse(): void
    {
        self::resetSessions();

        $this->instance->setId(static::$sess1);

        $this->instance->start();

        $this->instance->regenerate(false, true);

        $newId = $this->instance->getId();

        self::assertFileExists(self::getSessionPath() . '/' . 'sess_' . static::$sess1);

        self::assertNotEquals(
            static::$sess1,
            $newId,
        );
    }

    /**
     * @see NativeBridge::regenerate
     */
    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function regenerateDeletesOldSessionFileWhenDeleteOldIsTrue(): void
    {
        self::resetSessions();

        $this->instance->setId(static::$sess1);

        $this->instance->start();

        $this->instance->regenerate(true);

        $newId = $this->instance->getId();

        self::assertFileDoesNotExist(self::getSessionPath() . '/' . 'sess_' . static::$sess1);

        self::assertNotEquals(
            static::$sess1,
            $newId,
        );
    }

    /**
     * @see  NativeBridge::getStorage
     */
    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function getStorageReturnsSessionSuperGlobal(): void
    {
        self::resetSessions();

        session_id(static::$sess1);

        $this->instance->start();

        self::assertSame(['flower' => 'Sakura', 'animal' => 'Cat'], $this->instance->getStorage());
        self::assertSame($_SESSION, $this->instance->getStorage());
    }

    /**
     * @see  NativeBridge::__clone
     */
    #[Test]
    public function cloneDuplicatesHandler(): void
    {
        $clone = clone $this->instance;
        $property = new \ReflectionProperty(NativeBridge::class, 'handler');

        self::assertNotSame($property->getValue($this->instance), $property->getValue($clone));
    }

    #[Before]
    protected function createBridge(): void
    {
        $this->instance = new NativeBridge();
    }
}
