<?php

declare(strict_types=1);

namespace Windwalker\Session\Test\Handler;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Session\Bridge\PhpBridge;
use Windwalker\Session\Cookie\ArrayCookies;
use Windwalker\Session\Handler\AbstractHandler;
use Windwalker\Session\Handler\ArrayHandler;
use Windwalker\Session\Handler\HandlerInterface;
use Windwalker\Session\Session;
use Windwalker\Session\Test\SessionVfsTestTrait;

/**
 * The AbstractHandlerTest class.
 */
abstract class AbstractHandlerTestCase extends TestCase
{
    use MockeryPHPUnitIntegration;
    use SessionVfsTestTrait;

    protected ?Session $session;

    /**
     * @var  AbstractHandler
     */
    protected ?AbstractHandler $instance = null;

    /**
     * @see  ArrayHandler::read
     */
    #[Test]
    public function readReturnsStoredSessionData(): void
    {
        $session = $this->createSession();

        $session->setId(static::$sess1);
        $session->start();

        self::assertEquals(
            [
                'flower' => 'Sakura',
                'animal' => 'Cat',
            ],
            $session->all()
        );
    }

    /**
     * @see  ArrayHandler::read
     */
    #[Test]
    public function readWithoutStrictModeKeepsUnknownIdAndReturnsEmptyData(): void
    {
        $session = $this->createSession(
            [
                'ini' => [
                    'use_strict_mode' => false,
                ],
            ]
        );

        $session->setId($id = session_create_id());
        $session->start();

        self::assertEquals(
            $id,
            $session->getId()
        );
        self::assertEquals(
            [],
            $session->all()
        );
    }

    /**
     * @see  ArrayHandler::read
     */
    #[Test]
    public function readInStrictModeReturnsExistingSessionData(): void
    {
        $session = $this->createSession(
            [
                'use_strict_mode' => true,
            ]
        );

        $session->setId(static::$sess1);
        $session->start();

        self::assertEquals(
            [
                'flower' => 'Sakura',
                'animal' => 'Cat',
            ],
            $session->all()
        );
    }

    /**
     * @see  ArrayHandler::read
     */
    #[Test]
    public function readInStrictModeReplacesUnknownIdWithNewId(): void
    {
        $session = $this->createSession(
            [
                'use_strict_mode' => true,
            ]
        );

        $session->setId($id = session_create_id());
        $session->start();

        self::assertEquals(
            [],
            $session->all()
        );
        self::assertNotEquals(
            $id,
            $session->getId()
        );
    }

    /**
     * @see  ArrayHandler::updateTimestamp
     */
    abstract public function updateTimestampRefreshesStoredTime(): void;

    /**
     * @see  ArrayHandler::updateTimestamp
     */
    #[Test]
    public function lazyWriteOnlyUpdatesTimestampForUnchangedSession(): void
    {
        $session = $this->createSession();

        /** @var PhpBridge $bridge */
        $bridge = $session->getBridge();

        $mock = Mockery::instanceMock($bridge->getHandler());
        $mock->shouldReceive('updateTimestamp')
            ->once()
            ->andReturnTrue();
        $mock->shouldIgnoreMissing();
        $bridge->setHandler($mock);

        $session->setId(static::$sess1);
        $session->start();
        $session->stop();
    }

    /**
     * @see  ArrayHandler::updateTimestamp
     */
    #[Test]
    public function eagerWriteWritesUnchangedSessionWhenLazyWriteDisabled(): void
    {
        $session = $this->createSession();

        /** @var PhpBridge $bridge */
        $bridge = $session->getBridge();
        $bridge->setOption('lazy_write', false);

        $mock = Mockery::instanceMock($bridge->getHandler());
        $mock->shouldReceive('write')
            ->once()
            ->andReturnTrue();
        $mock->shouldIgnoreMissing();
        $bridge->setHandler($mock);

        $session->setId(static::$sess1);
        $session->start();
        $session->stop();
    }

    /**
     * @see  ArrayHandler::write
     */
    #[Test]
    public function writePersistsChangedSessionData(): void
    {
        $session = $this->createSession();

        $session->setId(static::$sess1);
        $session->start();

        $session['animal'] = 'Bear';

        $session->stop();

        self::assertEquals(
            'Bear',
            unserialize($this->instance->read(static::$sess1))['animal']
        );
    }

    /**
     * @see  ArrayHandler::destroy
     */
    #[Test]
    public function destroyRemovesSessionData(): void
    {
        $session = $this->createSession();

        $this->instance->destroy(static::$sess1);

        self::assertEquals(
            '',
            $this->instance->read(static::$sess1)
        );
    }

    /**
     * @see  ArrayHandler::gc
     */
    #[Test]
    public function gcRemovesExpiredSessions(): void
    {
        $session = $this->createSession();

        $this->instance->gc(1440);

        self::assertEquals(
            '',
            $this->instance->read(static::$sess2)
        );
    }

    protected function createSession(array $options = []): Session
    {
        $handler = $this->createInstance();

        return $this->session = new Session(
            [],
            new PhpBridge(
                $options,
                $handler
            ),
            ArrayCookies::create()
        );
    }

    abstract protected function createInstance(): HandlerInterface;

    /**
     * prepareDefaultData
     *
     * @return  array
     */
    protected function prepareDefaultData(): array
    {
        return [
            static::$sess1 => [
                'data' => file_get_contents(self::getSessionPath('sess_' . static::$sess1)),
                'time' => time() - 100,
            ],
            static::$sess2 => [
                'data' => file_get_contents(self::getSessionPath('sess_' . static::$sess2)),
                'time' => time() - 10000,
            ],
        ];
    }

    #[Before]
    protected function resetSession(): void
    {
        $this->session = null;
    }
}
