<?php

declare(strict_types=1);

namespace Windwalker\Session\Test\Bridge;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Session\Bridge\PhpBridge;
use RuntimeException;
use Windwalker\Data\Format\JsonFormat;
use Windwalker\Session\Handler\ArrayHandler;
use Windwalker\Session\Handler\FilesystemHandler;
use Windwalker\Session\Handler\HandlerInterface;
use Windwalker\Session\Test\SessionVfsTestTrait;

/**
 * The PhpBridgeTest class.
 */
class PhpBridgeTest extends TestCase
{
    use SessionVfsTestTrait;
    use MockeryPHPUnitIntegration;

    protected ?PhpBridge $instance;

    /**
     * @see  PhpBridge::start
     */
    #[Test]
    public function startLoadsWritesAndClosesSessions(): void
    {
        self::assertEquals(
            PHP_SESSION_NONE,
            $this->instance->getStatus()
        );

        $this->instance->setId(static::$sess1);

        $this->instance->start();

        self::assertEquals(
            PHP_SESSION_ACTIVE,
            $this->instance->getStatus()
        );

        $data = &$this->instance->getStorage();
        $data['flower'] = 'Sakura';

        self::assertEquals(
            [
                'flower' => 'Sakura',
                'animal' => 'Cat',
            ],
            $data
        );

        $this->instance->writeClose();

        self::assertEquals(
            PHP_SESSION_NONE,
            $this->instance->getStatus()
        );

        self::assertEquals(
            [],
            $data
        );

        $this->instance->setId(static::$sess2);

        $this->instance->start();

        self::assertEquals(
            [
                'flower' => 'Rose',
                'tree' => 'Oak',
            ],
            $data
        );

        $data['animal'] = 'Bird';

        $this->instance->writeClose();

        self::assertEquals(
            'a:3:{s:6:"flower";s:4:"Rose";s:4:"tree";s:3:"Oak";s:6:"animal";s:4:"Bird";}',
            file_get_contents(self::getSessionPath() . '/' . 'sess_' . static::$sess2),
        );
    }

    #[Test]
    public function startWithoutIdCreatesNewSessionAndPersistsData(): void
    {
        $this->instance->start();

        $data = &$this->instance->getStorage();

        self::assertEquals(
            [],
            $data
        );

        $data['foo'] = 'Bar';

        $id = $this->instance->getId();

        $this->instance->writeClose();

        self::assertEquals(
            'a:1:{s:3:"foo";s:3:"Bar";}',
            file_get_contents('vfs://root/tmp/sess_' . $id)
        );
    }

    #[Test]
    public function startWithSuperGlobalExposesSessionDataInSessionArray(): void
    {
        $this->createInstance(
            [
                PhpBridge::OPTION_WITH_SUPER_GLOBAL => true,
            ]
        );

        $this->instance->setId(static::$sess1);
        $this->instance->start();

        self::assertEquals(
            [
                'flower' => 'Sakura',
                'animal' => 'Cat',
            ],
            $_SESSION
        );

        $_SESSION['hello'] = 'World';

        self::assertEquals(
            'World',
            $this->instance->getStorage()['hello']
        );
    }

    /**
     * @see  PhpBridge::setSessionName
     */
    #[Test]
    public function setSessionNamePassesNameToHandlerOpen(): void
    {
        $this->instance->setSessionName('WW_SESS');

        $handler = Mockery::mock(HandlerInterface::class);
        $handler->shouldReceive('open')
            ->once()
            ->withArgs(fn($savePath, $name) => $name === 'WW_SESS');
        $handler->shouldReceive('write')
            ->once()
            ->andReturnTrue();
        $handler->shouldIgnoreMissing();

        $this->instance->setHandler($handler);

        $this->instance->start();

        $this->instance->writeClose();
    }

    #[Test]
    public function strictModeReplacesIdOfMissingSession(): void
    {
        $this->createInstance(
            [
                'use_strict_mode' => true,
            ]
        );

        $this->instance->setId(static::$sess1);

        unlink('vfs://root/tmp/sess_' . static::$sess1);

        $this->instance->start();

        self::assertNotEquals(
            static::$sess1,
            $this->instance->getId()
        );
    }

    /**
     * @see  PhpBridge::getStatus
     */
    #[Test]
    public function getStatusReturnsSessionStatus(): void
    {
        self::assertSame(PHP_SESSION_NONE, $this->instance->getStatus());

        $this->instance->start();

        self::assertSame(PHP_SESSION_ACTIVE, $this->instance->getStatus());

        $this->instance->close();

        self::assertSame(PHP_SESSION_NONE, $this->instance->getStatus());
    }

    /**
     * @see  PhpBridge::gcEnabled
     */
    #[Test]
    public function gcEnabledReflectsGcProbabilityOption(): void
    {
        $this->createInstance(
            [
                'gc_probability' => 0,
                'gc_divisor' => 1000,
            ]
        );

        self::assertFalse($this->instance->gcEnabled());

        $this->instance->setOption('gc_probability', 1000);

        self::assertTrue($this->instance->gcEnabled());
    }

    /**
     * @see  PhpBridge::getId
     */
    #[Test]
    public function getIdReturnsCurrentSessionId(): void
    {
        self::assertNull($this->instance->getId());

        $this->instance->setId(static::$sess1);

        self::assertSame(static::$sess1, $this->instance->getId());
    }

    /**
     * @see  PhpBridge::__construct
     */
    #[Test]
    public function constructAppliesOptionsAndHandler(): void
    {
        $handler = new ArrayHandler();
        $bridge = new PhpBridge([PhpBridge::OPTION_WITH_SUPER_GLOBAL => true], $handler);

        self::assertTrue($bridge->getOption(PhpBridge::OPTION_WITH_SUPER_GLOBAL));
        self::assertFalse($bridge->getOption(PhpBridge::OPTION_AUTO_COMMIT));

        $bridge->setId('constructed');
        $bridge->start();
        $bridge->getStorage()['a'] = 1;
        $bridge->writeClose();

        self::assertNotSame('', $handler->read('constructed'));
    }

    /**
     * @see  PhpBridge::unset
     */
    #[Test]
    public function unsetClearsStorage(): void
    {
        $this->instance->setId(static::$sess1);
        $this->instance->start();

        $data = &$this->instance->getStorage();

        $this->instance->unset();

        self::assertEquals(
            [],
            $data
        );
    }

    #[Test]
    public function setStorageReplacesSuperGlobalData(): void
    {
        $this->createInstance(
            [
                PhpBridge::OPTION_WITH_SUPER_GLOBAL => true,
            ]
        );

        $this->instance->setId(static::$sess1);
        $this->instance->start();

        $this->instance->setStorage(['foo' => 'bar']);

        self::assertSame(
            ['foo' => 'bar'],
            $_SESSION,
        );
    }

    #[Test]
    public function unsetClearsSuperGlobalData(): void
    {
        $this->createInstance(
            [
                PhpBridge::OPTION_WITH_SUPER_GLOBAL => true,
            ]
        );

        $this->instance->setId(static::$sess1);
        $this->instance->start();

        $this->instance->setStorage(['foo' => 'bar']);
        $this->instance->unset();

        self::assertSame([], $_SESSION);
    }

    /**
     * @see  PhpBridge::setId
     */
    #[Test]
    public function setIdChangesSessionId(): void
    {
        $this->instance->setId('another-id');

        self::assertSame('another-id', $this->instance->getId());
    }

    /**
     * @see  PhpBridge::isStarted
     */
    #[Test]
    public function isStartedReflectsSessionStatus(): void
    {
        self::assertFalse($this->instance->isStarted());

        $this->instance->start();

        self::assertTrue($this->instance->isStarted());

        $this->instance->writeClose();

        self::assertFalse($this->instance->isStarted());
    }

    /**
     * @see  PhpBridge::getStorage
     */
    #[Test]
    public function getStorageReturnsSessionData(): void
    {
        $this->instance->setId(static::$sess1);
        $this->instance->start();

        self::assertSame(['flower' => 'Sakura', 'animal' => 'Cat'], $this->instance->getStorage());
    }

    /**
     * @see  PhpBridge::writeClose
     */
    #[Test]
    public function writeCloseSavesDataAndEndsSession(): void
    {
        $this->instance->setId(static::$sess1);
        $this->instance->start();
        $this->instance->getStorage()['bird'] = 'Eagle';

        self::assertTrue($this->instance->writeClose(false));
        self::assertSame(PHP_SESSION_NONE, $this->instance->getStatus());

        $this->instance->setId(static::$sess1);
        $this->instance->start();

        self::assertSame('Eagle', $this->instance->getStorage()['bird']);
    }

    /**
     * @see  PhpBridge::regenerate
     */
    #[Test]
    public function regenerateKeepsOldSessionWhenDeleteOldIsFalse(): void
    {
        $this->instance->setId(static::$sess1);

        $this->instance->start();

        $data = &$this->instance->getStorage();

        $data['tree'] = 'Palm';

        $this->instance->regenerate(false, true);

        $newId = $this->instance->getId();

        self::assertEquals(
            [
                'flower' => 'Sakura',
                'animal' => 'Cat',
                'tree' => 'Palm',
            ],
            $data
        );

        $this->instance->writeClose();

        self::assertNotEquals(
            $newId,
            static::$sess1
        );

        self::assertEquals(
            [
                'flower' => 'Sakura',
                'animal' => 'Cat',
                'tree' => 'Palm',
            ],
            unserialize(file_get_contents('vfs://root/tmp/sess_' . static::$sess1))
        );
    }

    /**
     * @see  PhpBridge::regenerate
     */
    #[Test]
    public function regenerateDeletesOldSessionWhenDeleteOldIsTrue(): void
    {
        $this->instance->setId(static::$sess1);

        $this->instance->start();

        $data = &$this->instance->getStorage();

        $data['tree'] = 'Palm';

        $this->instance->regenerate(true);

        $newId = $this->instance->getId();

        self::assertEquals(
            [
                'flower' => 'Sakura',
                'animal' => 'Cat',
                'tree' => 'Palm',
            ],
            $data
        );

        $this->instance->writeClose();

        self::assertNotEquals(
            $newId,
            static::$sess1
        );

        self::assertFileDoesNotExist('vfs://root/tmp/sess_' . static::$sess1);
    }

    /**
     * @see  PhpBridge::destroy
     */
    #[Test]
    public function destroyClearsStorageAndDeletesSessionFile(): void
    {
        $this->instance->setId(static::$sess1);

        $this->instance->start();

        $data = &$this->instance->getStorage();

        $this->instance->destroy();

        self::assertEquals(
            [],
            $data
        );

        self::assertFileDoesNotExist('vfs://root/tmp/sess_' . static::$sess1);
    }

    /**
     * @see  PhpBridge::getSessionName
     */
    #[Test]
    public function getSessionNameReturnsConfiguredName(): void
    {
        $this->instance->setSessionName('MY_SESS');

        self::assertSame('MY_SESS', $this->instance->getSessionName());
        self::assertSame(session_name(), new PhpBridge()->getSessionName());
    }

    #[Test]
    public function setSerializerEncodesAndDecodesStorageWithSerializer(): void
    {
        $handler = new ArrayHandler();
        $bridge = new PhpBridge([], $handler);

        self::assertSame($bridge, $bridge->setSerializer(new JsonFormat()));

        $bridge->setId('json-id');
        $bridge->start();
        $bridge->getStorage()['flower'] = 'Sakura';
        $bridge->writeClose();

        self::assertSame('{"flower":"Sakura"}', $handler->read('json-id'));

        $bridge->start();

        self::assertSame(['flower' => 'Sakura'], $bridge->getStorage());
    }

    #[Test]
    public function setSerializerThrowsWhenSessionIsActive(): void
    {
        $bridge = new PhpBridge([], new ArrayHandler());
        $bridge->start();

        $this->expectException(RuntimeException::class);

        $bridge->setSerializer(null);
    }

    #[Test]
    public function cloneDuplicatesHandlerAndSerializer(): void
    {
        $bridge = new PhpBridge([], new ArrayHandler(), new JsonFormat());
        $clone = clone $bridge;

        self::assertNotSame($bridge->getHandler(), $clone->getHandler());
        self::assertNotSame($bridge->getSerializer(), $clone->getSerializer());
    }

    #[Before]
    protected function prepareBridgeAndVfs(): void
    {
        $this->createInstance();
        $this->prepareVfs();
    }

    protected function createInstance(array $options = [])
    {
        $this->instance = new PhpBridge(
            $options,
            new FilesystemHandler(static::getSessionPath())
        );
    }

    protected function resetSessions(): void
    {
        $this->prepareVfs();
    }

    #[After]
    protected function closeBridge(): void
    {
        $this->instance->writeClose();
    }
}
