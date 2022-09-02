<?php

/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    MIT
 */

declare(strict_types=1);

namespace Windwalker\Http\Test\Transport;

use voku\helper\ASCII;
use Windwalker\Http\Transport\CurlTransport;
use Windwalker\Uri\Uri;
use Windwalker\Uri\UriHelper;

/**
 * Test class of CurlTransport
 *
 * @since 2.1
 */
class CurlTransportTest extends AbstractTransportTest
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
        $this->instance = new CurlTransport();

        parent::setUp();
    }

    public function testCurlCmd(): void
    {
        $request = $this->createRequest();

        $request = $request->withRequestTarget('https://example.com?foo=123&bar=yoo')
            ->withMethod('POST')
            ->withAddedHeader('Content-Type', 'multipart/form-data');

        $request->getBody()->write(
            UriHelper::buildQuery(
                [
                    'foo' => 'bar',
                    'yoo' => 'GOO'
                ]
            )
        );

        $curl = $this->instance->toCurlCmd($request);

        self::assertStringSafeEquals(
            <<<CMD
            curl --location --request POST 'https://example.com?foo=123&bar=yoo' \
            --header 'Content-Length: 15' \
            --header 'Expect:' \
            --form 'foo=bar' \
            --form 'yoo=GOO'
            CMD,
            $curl
        );
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
