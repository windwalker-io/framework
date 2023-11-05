<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Transport;

use Windwalker\Http\Transport\CurlTransport;
use Windwalker\Http\Transport\StreamTransport;

/**
 * Test class of CurlTransport
 *
 * @since 2.1
 */
class StreamTransportTest extends AbstractTransportTest
{
    /**
     * Property options.
     *
     * @var  array
     */
    protected array $options = [
        'options' => [CURLOPT_SSL_VERIFYPEER => false],
    ];

    /**
     * Test instance.
     *
     * @var CurlTransport
     */
    protected $instance;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->instance = new StreamTransport();

        parent::setUp();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown(): void
    {
    }
}
