<?php

declare(strict_types=1);

namespace Windwalker\Session\Test;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Windwalker\Session\Bridge\NativeBridge;
use Windwalker\Session\Bridge\PhpBridge;
use Windwalker\Session\Cookie\ArrayCookies;
use Windwalker\Session\Handler\ArrayHandler;
use Windwalker\Session\Session;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class SessionRegenerationTest extends TestCase
{
    #[DataProvider('regenerationProvider')]
    public function testRegeneration(string $bridgeClass, bool $throughSession, bool $keepOld): void
    {
        ini_set('session.use_cookies', '0');
        ini_set('session.gc_probability', '0');
        ini_set('session.use_strict_mode', '0');

        $handler = new ArrayHandler();
        $bridge = new $bridgeClass(['gc_probability' => 0], $handler);
        $session = new Session(['auto_commit' => false], $bridge, ArrayCookies::create());
        $session->setId('old-session');
        $session->set('user', 'authenticated');
        $subject = $throughSession ? $session : $bridge;

        if ($keepOld) {
            self::assertTrue($subject->regenerate(false, true));
        } else {
            self::assertTrue($subject->regenerate());
        }

        $newId = $session->getId();
        self::assertNotSame('old-session', $newId);
        self::assertSame('authenticated', $session->get('user'));
        $session->stop();

        self::assertNotSame('', $handler->read($newId));

        if ($keepOld) {
            self::assertNotSame('', $handler->read('old-session'));
        } else {
            self::assertSame('', $handler->read('old-session'));
        }
    }

    public static function regenerationProvider(): iterable
    {
        foreach ([PhpBridge::class, NativeBridge::class] as $bridgeClass) {
            foreach ([true, false] as $throughSession) {
                foreach ([true, false] as $keepOld) {
                    yield "$bridgeClass:$throughSession:$keepOld" => [$bridgeClass, $throughSession, $keepOld];
                }
            }
        }
    }

    public function testNativeRestartDeletesOldIdByDefault(): void
    {
        ini_set('session.use_cookies', '0');
        ini_set('session.gc_probability', '0');

        $handler = new ArrayHandler();
        $bridge = new NativeBridge([], $handler);
        $bridge->setId('old-session');
        $bridge->start();
        $_SESSION['user'] = 'authenticated';

        self::assertTrue($bridge->restart());
        self::assertNotSame('old-session', $bridge->getId());
        self::assertSame('authenticated', $_SESSION['user']);
        $bridge->writeClose();

        self::assertSame('', $handler->read('old-session'));
    }

    public function testForkPreservesSourceSession(): void
    {
        $handler = new ArrayHandler();
        $bridge = new PhpBridge(['gc_probability' => 0], $handler);
        $session = new Session(['auto_commit' => false], $bridge, ArrayCookies::create());
        $session->setId('source-session');
        $session->set('user', 'authenticated');
        $bridge->write();

        $fork = $session->fork();

        self::assertNotSame($session->getId(), $fork->getId());
        self::assertSame('authenticated', $session->get('user'));
        self::assertSame('authenticated', $fork->get('user'));
        self::assertSame(serialize(['user' => 'authenticated']), $handler->read('source-session'));

        $fork->stop();
        $session->stop();
    }
}

