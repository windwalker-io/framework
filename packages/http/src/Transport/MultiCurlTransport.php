<?php

declare(strict_types=1);

namespace Windwalker\Http\Transport;

use CurlHandle;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Windwalker\Http\Exception\HttpRequestException;
use Windwalker\Http\Response\HttpClientResponse;
use Windwalker\Http\Response\Response;
use Windwalker\Promise\Promise;
use Windwalker\Promise\PromiseInterface;
use Windwalker\Utilities\Options\OptionAccessTrait;

use function Windwalker\Promise\async;

/**
 * The MultiCurlHandler class.
 */
class MultiCurlTransport implements AsyncTransportInterface
{
    use OptionAccessTrait;

    protected ?\CurlMultiHandle $mh = null;

    protected ?PromiseInterface $promise = null;

    /**
     * Property handles.
     *
     * @var  array<array{
     *     handle: CurlHandle,
     *     promise: array{ 0: PromiseInterface, 1: callable, 2: callable },
     *     options: array,
     *     headers: array,
     *     content: StreamInterface
     * }>
     */
    protected array $tasks = [];

    /**
     * @var CurlTransportInterface|null
     */
    protected ?CurlTransportInterface $transport;

    /**
     * Class init.
     *
     * @param  array                      $options   The options of this client object.
     * @param CurlTransportInterface|null $transport The Transport handler, default is CurlTransport.
     */
    public function __construct(array $options = [], ?CurlTransportInterface $transport = null)
    {
        $this->prepareOptions([], $options);

        $this->transport = $transport ?? new CurlTransport();
    }

    public function getMainHandle(): \CurlMultiHandle
    {
        $this->mh ??= curl_multi_init();

        return $this->mh;
    }

    /**
     * reset
     *
     * @return  static
     */
    public function reset(): static
    {
        foreach ($this->tasks as $task) {
            curl_multi_remove_handle($this->mh, $task['handle']);
        }

        curl_multi_close($this->mh);

        $this->mh = null;
        $this->tasks = [];
        $this->promise = null;

        return $this;
    }

    /**
     * @inheritDoc
     * @throws \Throwable
     */
    public function sendRequest(RequestInterface $request, array $options = []): PromiseInterface
    {
        $transport = $this->getTransport();

        $options = $transport->prepareRequestOptions($options);

        $this->tasks[] = [
            'handle' => $handle = $transport->createHandle($request, $options, $headers, $content),
            'promise' => $resolvers = Promise::withResolvers(),
            'options' => $options,
            'headers' => &$headers,
            'content' => $content
        ];

        curl_multi_add_handle($this->getMainHandle(), $handle);

        [$promise] = $resolvers;

        return $this->prepareResolvePromise()->then(fn() => $promise);
    }

    /**
     * resolve
     *
     * @return  mixed|PromiseInterface
     * @throws \Throwable
     */
    public function resolve(): mixed
    {
        $this->promise ??= $this->prepareResolvePromise();

        return $this->promise->wait();
    }

    /**
     * @throws \Throwable
     */
    protected function prepareResolvePromise(): PromiseInterface
    {
        return $this->promise ??= async(
            fn () => new Promise(
                function ($resolve, $reject) {
                    if ($this->tasks === []) {
                        $resolve();

                        return;
                    }

                    $active = null;
                    $mh = $this->getMainHandle();
                    $promises = [];

                    do {
                        $mrc = curl_multi_exec($mh, $active);
                    } while ($mrc === CURLM_CALL_MULTI_PERFORM);

                    while ($active && $mrc === CURLM_OK) {
                        if (curl_multi_select($mh) === -1) {
                            usleep(100);
                        }

                        do {
                            $mrc = curl_multi_exec($mh, $active);
                        } while ($mrc === CURLM_CALL_MULTI_PERFORM);
                    }

                    if ($mrc !== CURLM_OK) {
                        throw new RuntimeException(
                            "Curl multi read error $mrc\n",
                            E_USER_WARNING
                        );
                    }

                    $transport = $this->getTransport();

                    foreach ($this->tasks as $task) {
                        $handle = $task['handle'];
                        $options = $task['options'];
                        [$taskPromise, $taskResolve, $taskReject] = $task['promise'];
                        $content = $task['content'];
                        $promises[] = $taskPromise;

                        $error = curl_error($handle);

                        if (!$error) {
                            // $c = curl_multi_getcontent($handle);
                            $content->rewind();

                            $res = $transport->injectHeadersToResponse(
                                new HttpClientResponse($content)->withInfo(curl_getinfo($handle)),
                                (array) $task['headers'],
                                (bool) $options['allow_empty_status_code']
                            );
                            $taskResolve($res);
                        } else {
                            $taskReject(new HttpRequestException($error, curl_errno($handle)));
                        }
                    }

                    $this->reset();

                    $resolve(Promise::all($promises));
                }
            )
        );
    }

    /**
     * download
     *
     * @param  RequestInterface        $request
     * @param  StreamInterface|string  $dest
     * @param  array                   $options
     *
     * @return  mixed
     */
    public function download(RequestInterface $request, StreamInterface|string $dest, array $options = []): mixed
    {
        return null;
    }

    /**
     * isSupported
     *
     * @return  mixed
     */
    public static function isSupported(): bool
    {
        return true;
    }

    /**
     * @return CurlTransport
     */
    public function getTransport(): CurlTransport
    {
        return $this->transport;
    }

    /**
     * @param  CurlTransport  $transport
     *
     * @return  static  Return self to support chaining.
     */
    public function setTransport(CurlTransport $transport): static
    {
        $this->transport = $transport;

        return $this;
    }
}
