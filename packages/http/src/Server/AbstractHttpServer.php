<?php

/**
 * Part of earth project.
 *
 * @copyright  Copyright (C) 2023 __ORGANIZATION__.
 * @license    __LICENSE__
 */

declare(strict_types=1);

namespace Windwalker\Http\Server;

use Psr\Http\Message\ServerRequestInterface;
use Windwalker\Http\Event\RequestEvent;
use Windwalker\Http\Event\ResponseEvent;
use Windwalker\Http\HttpFactory;
use Windwalker\Http\Output\OutputInterface;
use Windwalker\Http\Output\StreamOutput;

/**
 * The AbstractHttpServer class.
 */
abstract class AbstractHttpServer extends AbstractServer implements HttpServerInterface
{
    /**
     * WebAdapter constructor.
     *
     * @param  HttpFactory|null      $httpFactory
     * @param  OutputInterface|null  $output
     */
    public function __construct(protected ?OutputInterface $output = null, protected ?HttpFactory $httpFactory = null)
    {
        //
    }

    protected function handleRequest(ServerRequestInterface $request, OutputInterface $output): ResponseEvent
    {
        /** @var RequestEvent $event */
        $event = $this->emit(
            RequestEvent::wrap('request')
                ->setRequest($request)
                ->setOutput($output)
        );

        return $this->emit(
            ResponseEvent::wrap('response')
                ->setRequest($event->getRequest())
                ->setResponse($event->getResponse() ?? $this->getHttpFactory()->createResponse())
                ->setOutput($output)
        );
    }

    public function onRequest(callable $listener, ?int $priority = null): static
    {
        $this->on('request', $listener, $priority);

        return $this;
    }

    public function onResponse(callable $listener, ?int $priority = null): static
    {
        $this->on('response', $listener, $priority);

        return $this;
    }

    /**
     * Method to get property Output
     *
     * @return  ?OutputInterface
     */
    public function getOutput(): ?OutputInterface
    {
        return $this->output;
    }

    /**
     * Method to set property output
     *
     * @param  ?OutputInterface  $output
     *
     * @return  static  Return self to support chaining.
     */
    public function setOutput(?OutputInterface $output): static
    {
        $this->output = $output;

        return $this;
    }

    /**
     * @return HttpFactory
     */
    public function getHttpFactory(): HttpFactory
    {
        return $this->httpFactory ??= new HttpFactory();
    }

    /**
     * @param  HttpFactory|null  $httpFactory
     *
     * @return  static  Return self to support chaining.
     */
    public function setHttpFactory(?HttpFactory $httpFactory): static
    {
        $this->httpFactory = $httpFactory;

        return $this;
    }
}
