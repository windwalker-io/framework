<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2023 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Event\Provider;

use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * The DecorateListenerProvider class.
 */
class DecorateListenerProvider implements ListenerProviderInterface
{
    /**
     * @var ListenerProviderInterface
     */
    protected $provider;

    /**
     * DecorateListenerProvider constructor.
     *
     * @param  ListenerProviderInterface  $provider
     */
    public function __construct(ListenerProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @inheritDoc
     */
    public function getListenersForEvent(object $event): iterable
    {
        return $this->provider->getListenersForEvent($event);
    }

    /**
     * Method to get property Provider
     *
     * @return  ListenerProviderInterface
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getProvider(): ListenerProviderInterface
    {
        return $this->provider;
    }
}
