<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 Asikart. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */

namespace Windwalker\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Windwalker\Http\Response\Response;
use Windwalker\Http\Transport\CurlTransport;
use Windwalker\Http\Transport\TransportInterface;

/**
 * The AsyncRequest class.
 *
 * @since  3.2
 */
class AsyncHttpClient extends HttpClient
{
    /**
     * Property mh.
     *
     * @var  resource
     */
    protected $mh;

    /**
     * Property handles.
     *
     * @var  resource[]
     */
    protected $handles = [];

    /**
     * Property errors.
     *
     * @var  \RuntimeException[]
     */
    protected $errors = [];

    /**
     * Property stop.
     *
     * @var  bool
     */
    protected $stop = false;

    /**
     * Class init.
     *
     * @param  array         $options   The options of this client object.
     * @param  CurlTransport $transport The Transport handler, default is CurlTransport.
     */
    public function __construct($options = [], CurlTransport $transport = null)
    {
        parent::__construct($options, $transport);
    }

    /**
     * getHandle
     *
     * @return  resource
     */
    public function getMainHandle()
    {
        if (!$this->mh) {
            $this->mh = curl_multi_init();

            $this->errors = [];
        }

        return $this->mh;
    }

    /**
     * reset
     *
     * @return  static
     */
    public function reset()
    {
        foreach ($this->handles as $handle) {
            curl_multi_remove_handle($this->mh, $handle);
        }

        curl_multi_close($this->mh);

        $this->mh      = null;
        $this->handles = [];

        return $this;
    }

    /**
     * Send a request to remote.
     *
     * @param   RequestInterface $request The Psr Request object.
     *
     * @return  ResponseInterface
     * @throws \RangeException
     */
    public function send(RequestInterface $request)
    {
        /** @var CurlTransport $transport */
        $transport = $this->getTransport();

        $handle = $this->handles[] = $transport->createHandle($request);

        curl_multi_add_handle($this->getMainHandle(), $handle);

        return new Response;
    }

    /**
     * resolve
     *
     * @param callable $callback
     *
     * @return  Response[]
     * @throws \RuntimeException
     */
    public function resolve(callable $callback = null)
    {
        $active = null;
        $mh     = $this->getMainHandle();

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
            throw new \RuntimeException("Curl multi read error $mrc\n", E_USER_WARNING);
        }

        /** @var CurlTransport $transport */
        $responses = [];
        $errors    = [];
        $transport = $this->getTransport();

        foreach ($this->handles as $handle) {
            $error = curl_error($handle);

            if (!$error) {
                $responses[] = $transport->getResponse(curl_multi_getcontent($handle), curl_getinfo($handle));
            } else {
                $errors[] = new \RuntimeException($error, curl_errno($handle));
            }
        }

        $this->errors = $errors;

        if ($callback) {
            $callback($responses, $errors, $this);
        }

        $this->reset();

        return $responses;
    }

    /**
     * Method to set property transport
     *
     * @param   TransportInterface $transport
     *
     * @return  static  Return self to support chaining.
     * @throws \InvalidArgumentException
     */
    public function setTransport(TransportInterface $transport)
    {
        if (!$transport instanceof CurlTransport) {
            throw new \InvalidArgumentException(sprintf('%s only supports %s', get_called_class(),
                CurlTransport::class));
        }

        $this->transport = $transport;

        return $this;
    }

    /**
     * Method to get property Errors
     *
     * @return  \RuntimeException[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Method to get property Handles
     *
     * @return  \resource[]
     */
    public function getHandles()
    {
        return $this->handles;
    }
}
