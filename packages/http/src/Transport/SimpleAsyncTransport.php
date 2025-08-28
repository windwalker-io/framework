<?php

declare(strict_types=1);

namespace Windwalker\Http\Transport;

use Psr\Http\Message\RequestInterface;
use Windwalker\Http\Transport\Options\TransportOptions;
use Windwalker\Promise\PromiseInterface;

use function Windwalker\Promise\async;

/**
 * The SimpleAsyncTransport class.
 */
class SimpleAsyncTransport implements AsyncTransportInterface
{
    protected TransportInterface $transport;

    /**
     * SimpleAsyncTransport constructor.
     *
     * @param  TransportInterface  $transport
     */
    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /**
     * @inheritDoc
     */
    public function sendRequest(RequestInterface $request, array|TransportOptions $options = []): PromiseInterface
    {
        return async(fn() => $this->getTransport()->request($request, $options));
    }

    /**
     * @return TransportInterface
     */
    public function getTransport(): TransportInterface
    {
        return $this->transport;
    }

    /**
     * @param  TransportInterface  $transport
     *
     * @return  static  Return self to support chaining.
     */
    public function setTransport(TransportInterface $transport): static
    {
        $this->transport = $transport;

        return $this;
    }
}
