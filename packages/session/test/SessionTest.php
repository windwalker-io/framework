<?php

declare(strict_types=1);

namespace Windwalker\Session\Test;

use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Windwalker\Session\Bridge\BridgeInterface;
use Windwalker\Session\Bridge\NativeBridge;
use Windwalker\Session\Bridge\PhpBridge;
use Windwalker\Session\Cookie\ArrayCookies;
use Windwalker\Session\Cookie\Cookies;
use Windwalker\Session\Cookie\CookiesInterface;
use Windwalker\Session\FlashBag;
use Windwalker\Session\Handler\ArrayHandler;
use Windwalker\Session\Session;

/**
 * The SessionTest class.
 */
class SessionTest extends TestCase
{
    use SessionVfsTestTrait;

    protected ?Session $instance;

    /**
     * @see                 Session::start
     *
     * @session             native
     * @handler             native
     * @cookie              native
     * @autoCommit          true
     */
    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function startWithNativeBridgeAndNativeCookieWithoutIdCreatesNewSession(): void
    {
        error_reporting(-1);

        $sess = $this->createInstance(
            [],
            new NativeBridge([]),
        );

        // With no name
        $sess->start();

        self::assertTrue($sess->isStarted());
        self::assertEquals(
            [],
            $_SESSION
        );
        self::assertEquals(
            session_id(),
            $sess->getId()
        );
    }

    /**
     * @see                 Session::start
     *
     * @session             native
     * @handler             native
     * @cookie              native
     * @autoCommit          true
     */
    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function startWithNativeBridgeAndNativeCookieWithIdRestoresSession(): void
    {
        $sess = $this->createInstance(
            [],
            new NativeBridge([]),
        );

        $_COOKIE[$sess->getName()] = static::$sess1;

        // With no name
        $sess->start();

        self::assertTrue($sess->isStarted());
        self::assertEquals(
            [],
            $_SESSION
        );
        self::assertEquals(
            static::$sess1,
            $sess->getId()
        );
    }

    /**
     * @see                 Session::start
     *
     * @session             native
     * @handler             native
     * @cookie              native
     * @autoCommit          true
     */
    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function startWithNativeBridgeAndArrayCookieWithIdRestoresSession(): void
    {
        $sess = $this->createInstance(
            [
                'ini' => [
                    'save_path' => self::getSessionPath(),
                    'use_cookies' => '0',
                ],
            ],
            new NativeBridge([], new ArrayHandler()),
            ArrayCookies::create(
                [
                    'WW_SESS_ID' => static::$sess1,
                ]
            )
        );

        // With name
        $sess->setName('WW_SESS_ID');
        $sess->start();

        self::assertTrue($sess->isStarted());
        self::assertEquals(
            [],
            $_SESSION
        );
        self::assertEquals(
            static::$sess1,
            $sess->getId()
        );
    }

    /**
     * @see                 Session::start
     *
     * @session             php
     * @handler             php
     * @cookie              array
     * @autoCommit          true
     */
    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function startWithPhpBridgeAndNativeCookieWithIdRestoresSession(): void
    {
        $sess = $this->createInstance(
            [],
            new PhpBridge(
                [],
                $handler = new ArrayHandler(
                    [
                        static::$sess1 => ArrayHandler::createData('a:1:{s:6:"flower";s:6:"Sakura";}'),
                    ]
                )
            )
        );

        $sess->setName('FOO_SESS');

        $_COOKIE['FOO_SESS'] = static::$sess1;

        $sess->start();

        self::assertTrue($sess->isStarted());
        self::assertEquals(
            ['flower' => 'Sakura'],
            $sess->all()
        );
        self::assertEquals(
            static::$sess1,
            $sess->getId()
        );

        $sess->set('tree', 'Oak');

        // Write
        $sess->stop(true);

        self::assertEquals(
            'a:2:{s:6:"flower";s:6:"Sakura";s:4:"tree";s:3:"Oak";}',
            $handler->getSessions()[static::$sess1]['data']
        );
    }

    /**
     * @see                 Session::start
     *
     * @session             php
     * @handler             php
     * @cookie              native
     * @autoCommit          true
     */
    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function startWithPhpBridgeAndNativeCookieWithoutIdCreatesNewSession(): void
    {
        $sess = $this->createInstance(
            [],
            new PhpBridge(
                [
                    BridgeInterface::OPTION_WITH_SUPER_GLOBAL => true,
                ],
                $handler = new ArrayHandler(
                    [
                        static::$sess1 => ArrayHandler::createData('a:1:{s:6:"flower";s:6:"Sakura";}'),
                    ]
                )
            )
        );

        $sess->setName('FOO_SESS');

        $sess->start();

        self::assertTrue($sess->isStarted());
        self::assertEquals(
            [],
            $_SESSION
        );
        self::assertNotEquals(
            static::$sess1,
            $sess->getId()
        );
    }

    /**
     * @see                 Session::start
     *
     * @session             php
     * @handler             php
     * @cookie              array
     * @autoCommit          true
     */
    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function startWithPhpBridgeAndArrayCookieWithIdRestoresSession(): void
    {
        $sess = $this->createInstance(
            [],
            new PhpBridge(
                [
                    BridgeInterface::OPTION_WITH_SUPER_GLOBAL => true,
                ],
                new ArrayHandler(
                    [
                        static::$sess1 => ArrayHandler::createData('a:1:{s:6:"flower";s:6:"Sakura";}'),
                    ]
                )
            ),
            ArrayCookies::create(
                [
                    'FOO_SESS' => static::$sess1,
                ]
            )
        );

        $sess->setName('FOO_SESS');
        $sess->start();

        self::assertTrue($sess->isStarted());
        self::assertEquals(
            ['flower' => 'Sakura'],
            $_SESSION
        );
        self::assertEquals(
            static::$sess1,
            $sess->getId()
        );
    }

    /**
     * @see  Session::addFlash
     */
    #[Test]
    public function addFlashStoresMessagesInFlashBag(): void
    {
        $sess = $this->createInstance(
            [],
            new PhpBridge(
                [],
                new ArrayHandler(
                    [
                        static::$sess1 => ArrayHandler::createData('a:1:{s:6:"flower";s:6:"Sakura";}'),
                    ]
                )
            ),
            ArrayCookies::create(
                [
                    'PHPSESSID' => static::$sess1,
                ]
            )
        );

        $sess->start();
        $sess->addFlash(
            [
                'Hello',
                'World',
            ],
            'info'
        );

        self::assertTrue($sess->isStarted());
        self::assertEquals(
            [
                'info' => [
                    'Hello',
                    'World',
                ],
            ],
            $sess->getFlashBag()->peek()
        );

        $sess->addFlash('Run', 'danger');

        self::assertEquals(
            [
                'info' => [
                    'Hello',
                    'World',
                ],
                'danger' => [
                    'Run',
                ],
            ],
            $sess->getFlashes()
        );

        self::assertEquals(
            [],
            $sess['_flash']
        );
    }

    /**
     * @see  Session::fork
     */
    #[Test]
    public function forkCreatesSeparateSessionWithNewOrGivenId(): void
    {
        $sess = $this->createInstance(
            [],
            new PhpBridge(
                [],
                new ArrayHandler(
                    [
                        static::$sess1 => ArrayHandler::createData('a:1:{s:6:"flower";s:6:"Sakura";}'),
                    ]
                )
            ),
            ArrayCookies::create(
                [
                    'PHPSESSID' => static::$sess1,
                ]
            )
        );

        $sess->start();
        $sess2 = $sess->fork();

        self::assertNotSame(
            $sess,
            $sess2
        );

        $sess2['tree'] = 'Oak';

        self::assertTrue($sess->isStarted());
        self::assertNotEquals(
            $sess['tree'] ?? null,
            $sess2['tree']
        );
        self::assertNotEquals(
            $sess->getId(),
            $sess2->getId()
        );

        $sess3 = $sess2->fork(static::$sess2);

        self::assertEquals(
            static::$sess2,
            $sess3->getId()
        );
    }

    /**
     * @see  Session::getCookies
     */
    #[Test]
    public function getCookiesReturnsCookiesInstance(): void
    {
        $cookies = new ArrayCookies();

        self::assertSame($cookies, $this->createInstance([], new PhpBridge([], new ArrayHandler()), $cookies)->getCookies());
        self::assertInstanceOf(Cookies::class, $this->createInstance([], new PhpBridge([], new ArrayHandler()))->getCookies());
    }

    /**
     * @see  Session::stop
     */
    #[Test]
    public function stopWritesAndClosesSession(): void
    {
        $handler = new ArrayHandler();
        $sess = $this->createInstance([], new PhpBridge([], $handler));
        $sess->setId('stop-id');
        $sess->set('flower', 'Sakura');

        self::assertTrue($sess->stop());
        self::assertFalse($sess->isStarted());

        $restored = new Session([], new PhpBridge([], $handler));
        $restored->setId('stop-id');

        self::assertSame('Sakura', $restored->get('flower'));
    }

    /**
     * @see  Session::__construct
     */
    #[Test]
    public function constructAppliesOptionsBridgeAndCookies(): void
    {
        $bridge = new PhpBridge([], new ArrayHandler());
        $cookies = new ArrayCookies();
        $sess = $this->createInstance(['name' => 'CUSTOM_SESS'], $bridge, $cookies);

        self::assertSame($bridge, $sess->getBridge());
        self::assertSame($cookies, $sess->getCookies());
        self::assertSame('CUSTOM_SESS', $sess->getName());
    }

    /**
     * @see  Session::setCookieParams
     */
    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function setCookieParamsUpdatesCookieOptions(): void
    {
        $sess = $this->createInstance([], new NativeBridge([]));

        $sess->setCookieParams(['path' => '/session-test', 'httponly' => true]);

        self::assertSame('/session-test', session_get_cookie_params()['path']);
        self::assertTrue(session_get_cookie_params()['httponly']);
    }

    /**
     * @see  Session::registerINI
     */
    #[Test]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function registerIniAppliesSessionIniOptions(): void
    {
        $sess = $this->createInstance(
            ['ini' => ['gc_maxlifetime' => '777', 'session.cache_expire' => '55', 'cache_limiter' => null]],
            new NativeBridge([]),
        );

        $sess->registerINI();

        self::assertSame('777', ini_get('session.gc_maxlifetime'));
        self::assertSame('55', ini_get('session.cache_expire'));
    }

    /**
     * @see  Session::restart
     */
    #[Test]
    public function restartRestartsSessionWithNewId(): void
    {
        $sess = $this->createInstance([], new PhpBridge([], new ArrayHandler()));
        $sess->start();
        $oldId = $sess->getId();

        self::assertTrue($sess->restart());
        self::assertNotSame($oldId, $sess->getId());
    }

    /**
     * @see  Session::getFlashBag
     */
    #[Test]
    public function getFlashBagReturnsFlashBag(): void
    {
        $sess = $this->createInstance([], new PhpBridge([], new ArrayHandler()));

        $flashBag = $sess->getFlashBag();

        self::assertSame($flashBag, $sess->getFlashBag());

        $flashBag->add('Hello');

        self::assertSame(['info' => ['Hello']], $sess->get('_flash'));
    }

    /**
     * @see  Session::setFlashBag
     */
    #[Test]
    public function setFlashBagReplacesFlashBag(): void
    {
        $sess = $this->createInstance([], new PhpBridge([], new ArrayHandler()));
        $flashBag = new FlashBag();

        self::assertSame($sess, $sess->setFlashBag($flashBag));
        self::assertSame($flashBag, $sess->getFlashBag());

        $flashBag->add('Linked', 'warning');

        self::assertSame(['warning' => ['Linked']], $sess->get('_flash'));
    }

    /**
     * @see  Session::jsonSerialize
     */
    #[Test]
    public function jsonSerializeEncodesSessionData(): void
    {
        $sess = $this->createInstance(
            [],
            new PhpBridge([], new ArrayHandler()),
        );

        $sess['flower'] = 'Sakura';
        $sess['bird'] = 'Eagle';

        self::assertEquals(
            '{"flower":"Sakura","bird":"Eagle"}',
            json_encode($sess, JSON_THROW_ON_ERROR)
        );
    }

    /**
     * @see  Session::getName
     */
    #[Test]
    public function getNameReturnsSessionName(): void
    {
        $sess = $this->createInstance([], new PhpBridge([], new ArrayHandler()));
        $sess->setName('FOO_SESS');

        self::assertSame('FOO_SESS', $sess->getName());
    }

    /**
     * @see  Session::clear
     */
    #[Test]
    public function clearRemovesAllSessionData(): void
    {
        $sess = $this->createInstance([], new PhpBridge([], new ArrayHandler()));
        $sess->set('a', 1)->set('b', 2);

        self::assertTrue($sess->clear());
        self::assertSame([], $sess->all());
    }

    /**
     * @see  Session::setCookies
     */
    #[Test]
    public function setCookiesReplacesCookiesInstance(): void
    {
        $sess = $this->createInstance([], new PhpBridge([], new ArrayHandler()));
        $cookies = new ArrayCookies();

        self::assertSame($sess, $sess->setCookies($cookies));
        self::assertSame($cookies, $sess->getCookies());
    }

    /**
     * @see  Session::setName
     */
    #[Test]
    public function setNameChangesSessionName(): void
    {
        $sess = $this->createInstance([], new PhpBridge([], new ArrayHandler()));

        self::assertSame($sess, $sess->setName('NEW_NAME'));
        self::assertSame('NEW_NAME', $sess->getBridge()->getSessionName());
    }

    /**
     * @see  Session::all
     */
    #[Test]
    public function allReturnsAllSessionData(): void
    {
        $sess = $this->createInstance([], new PhpBridge([], new ArrayHandler()));
        $sess->set('a', 1)->set('b', 2);

        self::assertSame(['a' => 1, 'b' => 2], $sess->all());
    }

    /**
     * @see  Session::getStorage
     */
    #[Test]
    public function getStorageReturnsBridgeStorage(): void
    {
        $bridge = new PhpBridge([], new ArrayHandler());
        $sess = $this->createInstance([], $bridge);
        $sess->set('a', 1);

        self::assertSame($bridge->getStorage(), $sess->getStorage());
        self::assertSame(['a' => 1], $sess->getStorage());
    }

    /**
     * @see  Session::getFlashes
     */
    #[Test]
    public function getFlashesReturnsAndClearsFlashMessages(): void
    {
        $sess = $this->createInstance([], new PhpBridge([], new ArrayHandler()));
        $sess->addFlash(['One', 'Two'], 'info')->addFlash('Bad', 'error');

        self::assertSame(['info' => ['One', 'Two'], 'error' => ['Bad']], $sess->getFlashes());
        self::assertSame([], $sess->get('_flash'));
    }

    /**
     * @see  Session::count
     */
    #[Test]
    public function countReturnsNumberOfSessionItems(): void
    {
        $sess = $this->createInstance([], new PhpBridge([], new ArrayHandler()));

        self::assertCount(0, $sess);

        $sess->set('a', 1)->set('b', 2);

        self::assertCount(2, $sess);
    }

    /**
     * @see  Session::def
     */
    #[Test]
    public function defSetsDefaultOnlyWhenMissing(): void
    {
        $sess = $this->createInstance([], new PhpBridge([], new ArrayHandler()));

        self::assertSame('x', $sess->def('key', 'x'));
        self::assertSame('x', $sess->def('key', 'y'));
    }

    /**
     * @see  Session::remove
     */
    #[Test]
    public function removeDeletesExistingKeyAndIgnoresMissing(): void
    {
        $sess = $this->createInstance([], new PhpBridge([], new ArrayHandler()));
        $sess->set('a', 1);

        $sess->remove('a')->remove('missing');

        self::assertFalse($sess->has('a'));
    }

    /**
     * @see  Session::dump
     */
    #[Test]
    public function dumpReturnsStorageOrRecursiveArray(): void
    {
        $sess = $this->createInstance([], new PhpBridge([], new ArrayHandler()));
        $sess->set('obj', (object) ['a' => 1]);

        self::assertInstanceOf(\stdClass::class, $sess->dump()['obj']);
        self::assertSame(['obj' => ['a' => 1]], $sess->dump(true));
    }

    /**
     * @see  Session::overrideWith
     */
    #[Test]
    public function overrideWithReadsOrReplacesValue(): void
    {
        $sess = $this->createInstance([], new PhpBridge([], new ArrayHandler()));
        $sess->set('a', 'old');

        self::assertSame('old', $sess->overrideWith('a'));
        self::assertSame('new', $sess->overrideWith('a', 'new'));
        self::assertSame('new', $sess->get('a'));
    }

    /**
     * @see  Session::destroy
     */
    #[Test]
    public function destroyRemovesStoredSession(): void
    {
        $handler = new ArrayHandler();
        $sess = $this->createInstance([], new PhpBridge([], $handler));
        $sess->setId('destroy-id');
        $sess->set('a', 1);
        $sess->stop();

        $sess->start();
        $sess->destroy();

        self::assertSame('', $handler->read('destroy-id'));
    }

    #[Before]
    protected function resetSessionAndVfs(): void
    {
        $this->instance = null;

        $this->prepareVfs();
    }

    protected function createInstance(
        array $options = [],
        ?BridgeInterface $bridge = null,
        ?CookiesInterface $cookies = null
    ): Session {
        return $this->instance = new Session(
            $options,
            $bridge,
            $cookies
        );
    }
}
