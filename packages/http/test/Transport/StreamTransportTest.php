<?php

declare(strict_types=1);

namespace Windwalker\Http\Test\Transport;

use PHPUnit\Framework\Attributes\Test;
use Windwalker\Http\HttpClient;
use Windwalker\Http\Stream\RequestBodyStream;
use Windwalker\Http\Transport\AbstractTransport;
use Windwalker\Http\Transport\StreamTransport;
use Windwalker\Uri\Uri;

class StreamTransportTest extends AbstractTransportTestCase
{
    protected array $options = [
        'options' => [CURLOPT_SSL_VERIFYPEER => false],
    ];

    protected function createTransport(): AbstractTransport
    {
        return new StreamTransport();
    }

    #[Test]
    public function requestSendsMultipartBodyWhenFormDataContainsUploadFile(): void
    {
        $request = $this
            ->createRequest()
            ->withBody(
                new RequestBodyStream(
                    [
                        'foo' => 'bar',
                        'file' => HttpClient::uploadFileData('file-content-123', 'text/plain', 'a.txt'),
                    ],
                ),
            )
            ->withUri(new Uri(static::getTestUrlFromConstant()))
            ->withMethod('POST');

        $response = $this->instance->request(
            $request,
            ['timeout' => 10, 'userAgent' => 'Windwalker-Test'],
        );
        $body = (string) $response->getBody();

        // The test server echoes $_POST, which PHP only fills by parsing a valid multipart body.
        self::assertSame('foo=bar', trim($body));
    }

    #[Test]
    public function requestEncodesArrayBodyAsFormUrlEncoded(): void
    {
        $request = $this
            ->createRequest()
            ->withBody(new RequestBodyStream(['foo' => ['bar' => 'baz']]))
            ->withUri(new Uri(static::getTestUrlFromConstant()))
            ->withMethod('POST');

        $body = (string) $this->instance->request($request)->getBody();

        self::assertStringContainsString('foo%5Bbar%5D=baz', $body);
    }
}
