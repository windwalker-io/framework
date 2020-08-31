<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Session\Test\Handler;

use Windwalker\Session\Handler\ArrayHandler;
use Windwalker\Session\Handler\HandlerInterface;

/**
 * The ArrayHandlerTest class.
 */
class ArrayHandlerTest extends AbstractHandlerTest
{
    /**
     * @var ArrayHandler
     */
    protected $instance;

    /**
     * @see  ArrayHandler::updateTimestamp
     */
    public function testUpdateTimestamp(): void
    {
        $session = $this->createSession();

        $lastTime = $this->instance->getSessions()[static::$sess1]['time'];

        $session->setId(static::$sess1);
        $session->start();
        $session->stop();

        self::assertTrue($lastTime < $this->instance->getSessions()[static::$sess1]['time']);
    }

    protected function createInstance(): HandlerInterface
    {
        return $this->instance = new ArrayHandler($this->prepareDefaultData());
    }
}
