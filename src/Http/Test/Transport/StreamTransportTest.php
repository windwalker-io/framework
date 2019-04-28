<?php declare(strict_types=1);
/**
 * Part of Windwalker project Test files.  @codingStandardsIgnoreStart
 *
 * @copyright  Copyright (C) 2019 SMS Taiwan, Inc.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

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
    protected $options = [
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
