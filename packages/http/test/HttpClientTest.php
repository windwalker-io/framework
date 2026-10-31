<?php

declare(strict_types=1);

namespace Windwalker\Http\Test;

use BadMethodCallException;
use DomainException;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use RangeException;
use Windwalker\Http\Event\HttpClient\AfterRequestEvent;
use Windwalker\Http\Event\HttpClient\BeforeRequestEvent;
use Windwalker\Http\Exception\HttpRequestException;
use Windwalker\Http\File\HttpUploadFile;
use Windwalker\Http\File\HttpUploadStream;
use Windwalker\Http\File\HttpUploadStringFile;
use Windwalker\Http\FormData;
use Windwalker\Http\HttpClient;
use Windwalker\Http\HttpClientOptions;
use Windwalker\Http\Request\Request;
use Windwalker\Http\Response\HttpClientResponse;
use Windwalker\Http\Response\JsonResponse;
use Windwalker\Http\Stream\RequestBodyStream;
use Windwalker\Http\Test\Mock\MockTransport;
use Windwalker\Http\Test\Transport\TestServerExistsTrait;
use Windwalker\Http\Transport\CurlTransport;
use Windwalker\Http\Transport\MultiCurlTransport;
use Windwalker\Http\Transport\Options\TransportOptions;
use Windwalker\Promise\Promise;
use Windwalker\Stream\Stream;
use Windwalker\Test\Traits\BaseAssertionTrait;
use Windwalker\Uri\Uri;
use Windwalker\Uri\UriHelper;
use Windwalker\Utilities\Str;

use function Windwalker\Uri\uri_prepare;

class HttpClientTest extends TestCase
{
    use BaseAssertionTrait;
    use TestServerExistsTrait;

    protected HttpClient $instance;

    protected MockTransport $transport;

    #[Before]
    protected function createClientWithMockTransport(): void
    {
        $this->instance = $this->createClient();
    }

    protected function createClient(array $options = [], ?MockTransport $transport = null): HttpClient
    {
        $this->transport = $transport = $transport ?: new MockTransport();

        return new HttpClient($options, $transport);
    }

    #[Test]
    public function downloadSendsGetRequestAndSetsTargetFile(): void
    {
        $url = 'http://example.com';
        $dest = '/path/to/file';

        $this->instance->download($url, $dest);

        self::assertEquals('GET', $this->transport->request->getMethod());
        self::assertEquals('http://example.com', $this->transport->request->getRequestTarget());
        self::assertEquals('/path/to/file', $this->transport->getOption('target_file'));
    }

    #[Test]
    public function downloadWithPostRequestKeepsMethodAndSetsTargetFile(): void
    {
        $url = 'http://example.com';
        $dest = '/path/to/file';

        $request = $this->instance::createRequest(
            'POST',
            $url,
        );

        $this->instance->download($request, $dest);

        self::assertEquals('POST', $this->transport->request->getMethod());
        self::assertEquals('http://example.com', $this->transport->request->getRequestTarget());
        self::assertEquals('/path/to/file', $this->transport->getOption('target_file'));
    }

    #[Test]
    public function requestGetAppendsParamsToQueryAndSendsHeaders(): void
    {
        $url = new Uri('http://example.com/?foo=bar');

        $this->instance->request(
            'GET',
            $url,
            null,
            [
                'params' => ['flower' => 'sakura'],
                'headers' => ['X-Foo' => 'Bar'],
            ],
        );

        self::assertEquals('GET', $this->transport->request->getMethod());
        self::assertEquals('http://example.com/?foo=bar&flower=sakura', $this->transport->request->getRequestTarget());
        self::assertEquals('', $this->transport->request->getBody()->__toString());
        self::assertEquals(['X-Foo' => ['Bar']], $this->transport->request->getHeaders());
    }

    #[Test]
    public function requestPostEncodesArrayBodyAsJson(): void
    {
        $url = new Uri('http://example.com/?foo=bar');

        $this->instance->request('POST', $url, ['flower' => 'sakura'], ['headers' => ['X-Foo' => 'Bar']]);

        self::assertEquals('POST', $this->transport->request->getMethod());
        self::assertEquals('http://example.com/?foo=bar', $this->transport->request->getRequestTarget());
        self::assertEquals('{"flower":"sakura"}', $this->transport->request->getBody()->__toString());
        self::assertEquals(
            [
                'X-Foo' => ['Bar'],
                'Content-Type' => ['application/json; charset=utf-8'],
            ],
            $this->transport->request->getHeaders(),
        );
    }

    #[Test]
    public function requestPutSendsStringBodyAsIs(): void
    {
        $url = new Uri('http://example.com/?foo=bar');

        $this->instance->request('PUT', $url, 'flower=sakura', ['headers' => ['X-Foo' => 'Bar']]);

        self::assertEquals('PUT', $this->transport->request->getMethod());
        self::assertEquals('http://example.com/?foo=bar', $this->transport->request->getRequestTarget());
        self::assertEquals('flower=sakura', $this->transport->request->getBody()->__toString());
    }

    #[Test]
    public function requestResolvesRelativeUriAgainstBaseUri(): void
    {
        $this->instance->setOption('base_uri', 'http://example.com/');

        $this->instance->request('GET', 'foo/bar?foo=bar&flower=sakura');

        self::assertEquals(
            'http://example.com/foo/bar?foo=bar&flower=sakura',
            $this->transport->request->getRequestTarget(),
        );
    }

    #[Test]
    public function sendRequestPassesRequestToTransportUnchanged(): void
    {
        $request = new Request();

        $this->instance->sendRequest($request);

        self::assertSame($request, $this->transport->request);
    }

    #[Test]
    public function optionsSendsOptionsMethodWithHeaders(): void
    {
        $url = 'http://example.com/?foo=bar';
        $headers = ['X-Foo' => 'Bar'];

        $this->instance->options($url, compact('headers'));

        self::assertEquals('OPTIONS', $this->transport->request->getMethod());
        self::assertEquals($url, $this->transport->request->getRequestTarget());
        self::assertEquals('Bar', $this->transport->request->getHeaderLine('X-Foo'));
    }

    #[Test]
    public function constructorPushesTransportOptionsToDefaultCurlTransport(): void
    {
        $http = new HttpClient(
            [
                'transport' => [
                    'curl' => [
                        CURLOPT_SSL_VERIFYPEER => false,
                    ],
                ],
            ],
        );

        /** @var CurlTransport $transport */
        $transport = $http->getTransport();
        $curlopts = $transport->getOption('curl');

        self::assertFalse($curlopts[CURLOPT_SSL_VERIFYPEER]);
    }

    #[Test]
    public function headSendsHeadMethodWithHeaders(): void
    {
        $url = 'http://example.com/?foo=bar';
        $headers = ['X-Foo' => 'Bar'];

        $this->instance->head($url, compact('headers'));

        self::assertEquals('HEAD', $this->transport->request->getMethod());
        self::assertEquals($url, $this->transport->request->getRequestTarget());
        self::assertEquals('Bar', $this->transport->request->getHeaderLine('X-Foo'));
    }

    #[Test]
    public function getAppendsParamsToQueryAndSendsHeaders(): void
    {
        $url = new Uri('http://example.com/?foo=bar');

        $this->instance->get(
            $url,
            [
                'params' => ['flower' => 'sakura'],
                'headers' => ['X-Foo' => 'Bar'],
            ],
        );

        self::assertEquals('GET', $this->transport->request->getMethod());
        self::assertEquals('http://example.com/?foo=bar&flower=sakura', $this->transport->request->getRequestTarget());
        self::assertEquals('', $this->transport->request->getBody()->__toString());
        self::assertEquals(['X-Foo' => ['Bar']], $this->transport->request->getHeaders());
    }

    #[Test]
    public function postSendsFormDataBodyWithPostMethod(): void
    {
        $url = 'http://example.com/?foo=bar';
        $data = ['flower' => 'sakura'];
        $headers = ['X-Foo' => 'Bar'];

        $this->instance->post($url, FormData::create($data), compact('headers'));

        self::assertEquals('POST', $this->transport->request->getMethod());
        self::assertEquals($url, $this->transport->request->getRequestTarget());
        self::assertEquals('Bar', $this->transport->request->getHeaderLine('X-Foo'));
        self::assertEquals(http_build_query($data), $this->transport->request->getBody()->__toString());
    }

    #[Test]
    public function putSendsFormDataBodyWithPutMethod(): void
    {
        $url = 'http://example.com/?foo=bar';
        $data = ['flower' => 'sakura'];
        $headers = ['X-Foo' => 'Bar'];

        $this->instance->put($url, FormData::create($data), compact('headers'));

        self::assertEquals('PUT', $this->transport->request->getMethod());
        self::assertEquals($url, $this->transport->request->getRequestTarget());
        self::assertEquals('Bar', $this->transport->request->getHeaderLine('X-Foo'));
        self::assertEquals(UriHelper::buildQuery($data), $this->transport->request->getBody()->__toString());
    }

    #[Test]
    public function deleteSendsJsonBodyWithDeleteMethod(): void
    {
        $url = 'http://example.com/?foo=bar';
        $data = ['flower' => 'sakura'];
        $headers = ['X-Foo' => 'Bar'];

        $this->instance->delete($url, $data, compact('headers'));

        self::assertEquals('DELETE', $this->transport->request->getMethod());
        self::assertEquals($url, $this->transport->request->getRequestTarget());
        self::assertEquals('Bar', $this->transport->request->getHeaderLine('X-Foo'));
        self::assertEquals(json_encode($data), $this->transport->request->getBody()->__toString());
    }

    #[Test]
    public function traceSendsTraceMethodWithHeaders(): void
    {
        $url = 'http://example.com/?foo=bar';
        $headers = ['X-Foo' => 'Bar'];

        $this->instance->trace($url, compact('headers'));

        self::assertEquals('TRACE', $this->transport->request->getMethod());
        self::assertEquals($url, $this->transport->request->getRequestTarget());
        self::assertEquals('Bar', $this->transport->request->getHeaderLine('X-Foo'));
    }

    #[Test]
    public function patchSendsJsonBodyWithPatchMethod(): void
    {
        $url = 'http://example.com/?foo=bar';
        $data = ['flower' => 'sakura'];
        $headers = ['X-Foo' => 'Bar'];

        $this->instance->patch($url, $data, compact('headers'));

        self::assertEquals('PATCH', $this->transport->request->getMethod());
        self::assertEquals($url, $this->transport->request->getRequestTarget());
        self::assertEquals('Bar', $this->transport->request->getHeaderLine('X-Foo'));
        self::assertEquals(json_encode($data), $this->transport->request->getBody()->__toString());
    }

    #[Test]
    public function getAsyncResolvesAllConcurrentRequestsWithTheirResponses(): void
    {
        self::checkTestServerRunningOrSkip();

        $http = new HttpClient(['base_uri' => Str::ensureRight(WINDWALKER_TEST_HTTP_URL, '/')]);

        $pall = Promise::all(
            [
                $http->getAsync('json?a=1'),
                $http->getAsync('json?a=2'),
                $http->getAsync('json?a=3'),
            ],
        );

        /** @var HttpClientResponse $res1 */
        /** @var HttpClientResponse $res2 */
        /** @var HttpClientResponse $res3 */
        [$res1, $res2, $res3] = $pall->wait();

        self::assertEquals('{"a":"1"}', $res1->getContent());
        self::assertEquals('{"a":"2"}', $res2->getContent());
        self::assertEquals('{"a":"3"}', $res3->getContent());
    }

    #[Test]
    public function beforeAndAfterRequestEventsModifyRequestAndResponse(): void
    {
        $this->instance->on(
            BeforeRequestEvent::class,
            function (BeforeRequestEvent $event) {
                $options = $event->options;

                $event->httpClient->setOption('base_uri', 'https://api.foo.com/');

                $options['headers']['X-XSRF-Token'] = 'TokenValue';
            },
        );

        $this->instance->on(
            AfterRequestEvent::class,
            function (AfterRequestEvent $event) {
                $event->response = new JsonResponse([123]); // Will be converted to HttpClientResponse
            },
        );

        $res = $this->instance->get('validate');

        self::assertTrue($res->isSuccess());
        self::assertEquals('TokenValue', $this->transport->request->getHeaderLine('X-XSRF-Token'));
        self::assertEquals('https://api.foo.com/validate', $this->transport->request->getRequestTarget());

        self::assertEquals(
            '[123]',
            (string) $res->getBody(),
        );
    }

    #[Test]
    public function getExpandsPreparedUriTemplateBoundValues(): void
    {
        $this->instance->setOption('base_uri', 'https://example.org/');

        $res = $this->instance->get(
            uri_prepare('foo/{foo}{?bar}')
                ->bindValue('foo', '123')
                ->bindValue('bar', 'yoo'),
        );

        self::assertEquals(
            'https://example.org/foo/123?bar=yoo',
            $this->transport->request->getRequestTarget(),
        );
    }

    #[Test]
    public function getExpandsUriTemplateWithVarsOptionIncludingBaseUri(): void
    {
        $this->instance->setOption('base_uri', 'https://example.org/{v}/');

        $res = $this->instance->get(
            'foo/{foo}{?bar}',
            [
                'vars' => [
                    'foo' => 123,
                    'bar' => 'yoo',
                    'v' => 'v3',
                ],
            ],
        );

        self::assertEquals(
            'https://example.org/v3/foo/123?bar=yoo',
            $this->transport->request->getRequestTarget(),
        );
    }

    #[Test]
    public function toCurlCmdBuildsMultipartFormFromRequest(): void
    {
        $request = new Request();

        $request = $request
            ->withRequestTarget('https://example.com?foo=123&bar=yoo')
            ->withMethod('POST')
            ->withAddedHeader('Content-Type', 'multipart/form-data');

        $request->getBody()->write(
            UriHelper::buildQuery(
                [
                    'foo' => 'bar',
                    'yoo' => 'GOO',
                ],
            ),
        );

        $curl = $this->instance->toCurlCmd($request);

        self::assertStringSafeEquals(
            <<<CMD
                curl --location --request POST 'https://example.com?foo=123&bar=yoo' \
                --form 'foo=bar' \
                --form 'yoo=GOO'
                CMD,
            $curl,
        );
    }

    #[Test]
    public function toCurlCmdBuildsUrlEncodedFormFromMethodAndUrl(): void
    {
        $curl = $this->instance->toCurlCmd(
            'POST',
            'https://example.com?foo=123&bar=yoo',
            HttpClient::formData(
                [
                    'foo' => 'bar',
                    'yoo' => 'GOO',
                ],
            ),
            [
                'headers' => [
                    'X-CSRF-Token' => 'qETt34lmfd',
                ],
            ],
        );

        self::assertStringSafeEquals(
            <<<CMD
                curl --location --request POST 'https://example.com?foo=123&bar=yoo' \
                --header 'X-CSRF-Token: qETt34lmfd' \
                --header 'Content-Type: application/x-www-form-urlencoded; charset=utf-8' \
                --data-urlencode 'foo=bar' \
                --data-urlencode 'yoo=GOO'
                CMD,
            $curl,
        );
    }

    #[Test]
    public function getMergesClientAndRequestTransportOptions(): void
    {
        $http = new HttpClient(
            [
                'transport' => [
                    'root' => true,
                ],
            ],
            $transport = new MockTransport(),
        );

        $http->get('.', ['transport' => ['foo' => 'bar']]);

        self::assertTrue(
            $transport->receivedOptions->root,
        );

        self::assertEquals(
            'bar',
            $transport->receivedOptions->foo,
        );

        self::assertTrue(
            $transport->receivedOptions->optionMerged,
        );
    }

    #[Test]
    public function requestMergesClientAndRequestTransportOptions(): void
    {
        $http = new HttpClient(
            [
                'transport' => [
                    'root' => true,
                ],
            ],
            $transport = new MockTransport(),
        );

        $http->request('GET', '.', null, ['transport' => ['foo' => 'bar']]);

        self::assertTrue(
            $transport->receivedOptions->root,
        );

        self::assertEquals(
            'bar',
            $transport->receivedOptions->foo,
        );

        self::assertTrue(
            $transport->receivedOptions->optionMerged,
        );
    }

    #[Test]
    public function sendRequestMergesClientTransportOptionsWithRequestOptions(): void
    {
        $http = new HttpClient(
            [
                'transport' => [
                    'root' => true,
                ],
            ],
            $transport = new MockTransport(),
        );

        $http->sendRequest(
            new Request(),
            ['foo' => 'bar'],
        );

        self::assertTrue(
            $transport->receivedOptions->root,
        );

        self::assertEquals(
            'bar',
            $transport->receivedOptions->foo,
        );
    }

    // ------------------------------------------------------------------
    // mustXxx() tests
    // ------------------------------------------------------------------

    #[Test]
    public function mustGetSuccessReturnsResponse(): void
    {
        // MockTransport returns 200 by default – should not throw.
        $response = $this->instance->mustGet('http://example.com/');

        self::assertTrue($response->isSuccess());
    }

    #[Test]
    public function mustGetThrowsOnErrorResponse(): void
    {
        $this->instance->on(
            AfterRequestEvent::class,
            function (AfterRequestEvent $event) {
                $event->response = $event->response->withStatus(404);
            },
        );

        $this->expectException(HttpRequestException::class);
        $this->expectExceptionCode(404);

        $this->instance->mustGet('http://example.com/not-found');
    }

    #[Test]
    public function mustRequestSuccessReturnsResponse(): void
    {
        $response = $this->instance->mustRequest('GET', 'http://example.com/');

        self::assertTrue($response->isSuccess());
        self::assertEquals('GET', $this->transport->request->getMethod());
    }

    #[Test]
    public function mustRequestThrowsOnErrorResponse(): void
    {
        $this->instance->on(
            AfterRequestEvent::class,
            function (AfterRequestEvent $event) {
                $event->response = $event->response->withStatus(503);
            },
        );

        $this->expectException(HttpRequestException::class);
        $this->expectExceptionCode(503);

        $this->instance->mustRequest(
            'POST',
            'http://example.com/api',
            ['data' => 1],
            new HttpClientOptions(),
        );
    }

    #[Test]
    public function mustPostViaCallThrowsOnErrorResponse(): void
    {
        $this->instance->on(
            AfterRequestEvent::class,
            function (AfterRequestEvent $event) {
                $event->response = $event->response->withStatus(422);
            },
        );

        $this->expectException(HttpRequestException::class);
        $this->expectExceptionCode(422);

        $this->instance->mustPost('http://example.com/api', ['flower' => 'sakura']);
    }

    // ------------------------------------------------------------------
    // Proxy-option integration tests (real CurlTransport + test server)
    // ------------------------------------------------------------------

    #[Test]
    public function timeoutOptionPassedToCurlTransport(): void
    {
        self::checkTestServerRunningOrSkip();

        $http = new HttpClient(
            [
                'base_uri' => Str::ensureEnd(WINDWALKER_TEST_HTTP_URL, '/'),
                'timeout' => 10,
            ],
        );

        $response = $http->get('json');

        self::assertTrue($response->isSuccess());
    }

    #[Test]
    public function userAgentOptionPassedToCurlTransport(): void
    {
        self::checkTestServerRunningOrSkip();

        $http = new HttpClient(
            [
                'base_uri' => Str::ensureRight(WINDWALKER_TEST_HTTP_URL, '/'),
                'user_agent' => 'WindwalkerTest/4.0',
            ],
        );

        $response = $http->get('server');
        $server = json_decode($response->getContent(), true);

        self::assertEquals('WindwalkerTest/4.0', $server['HTTP_USER_AGENT']);
    }

    #[Test]
    public function verifyPeerOptionPassedToCurlTransport(): void
    {
        self::checkTestServerRunningOrSkip();

        $http = new HttpClient(
            [
                'base_uri' => Str::ensureRight(WINDWALKER_TEST_HTTP_URL, '/'),
                'verify_peer' => false,
            ],
        );

        $response = $http->get('json');

        self::assertTrue($response->isSuccess());
    }

    #[Test]
    public function followLocationOptionPassedToCurlTransport(): void
    {
        self::checkTestServerRunningOrSkip();

        $http = new HttpClient(
            [
                'base_uri' => Str::ensureRight(WINDWALKER_TEST_HTTP_URL, '/'),
                'follow_location' => false,
            ],
        );

        $response = $http->get('json');

        self::assertTrue($response->isSuccess());
    }

    #[Test]
    public function connectSendsConnectMethod(): void
    {
        $this->instance->connect('http://example.com');

        self::assertSame('CONNECT', $this->transport->request->getMethod());
    }

    #[Test]
    public function createWrapsOptionsAndUsesGivenTransport(): void
    {
        $transport = new MockTransport();
        $client = HttpClient::create(['base_uri' => 'http://example.com/'], $transport);

        $client->get('foo');

        self::assertSame($transport, $client->getTransport());
        self::assertSame('http://example.com/foo', $transport->request->getRequestTarget());
    }

    #[Test]
    public function sendRequestThrowsWhenTransportIsNotSupported(): void
    {
        $transport = new class () extends MockTransport {
            public static function isSupported(): bool
            {
                return false;
            }
        };

        $this->expectException(RangeException::class);

        $this->createClient([], $transport)->get('http://example.com');
    }

    #[Test]
    public function downloadThrowsWhenTransportIsNotSupported(): void
    {
        $transport = new class () extends MockTransport {
            public static function isSupported(): bool
            {
                return false;
            }
        };

        $this->expectException(RangeException::class);

        $this->createClient([], $transport)->download('http://example.com', '/path/to/file');
    }

    #[Test]
    public function withBaseUriReturnsClonedClientWithoutChangingOriginal(): void
    {
        $new = $this->instance->withBaseUri('http://base.test/');

        self::assertNotSame($this->instance, $new);
        self::assertSame('http://base.test/', $new->getBaseUri());
        self::assertSame('', $this->instance->getBaseUri());
    }

    #[Test]
    public function withOptionsReplacesOrMergesOptionsOnClone(): void
    {
        $client = $this->createClient(['base_uri' => 'http://a.test/', 'timeout' => 5]);

        $replaced = $client->withOptions(['timeout' => 10]);
        $merged = $client->withOptions(['timeout' => 10], true);

        self::assertSame('', $replaced->getBaseUri());
        self::assertSame('http://a.test/', $merged->getBaseUri());
        self::assertSame(10, $merged->getOptions()->timeout);
        self::assertSame('http://a.test/', $client->getBaseUri());
    }

    #[Test]
    public function withOptionSetsSingleOptionOnCloneOnly(): void
    {
        $new = $this->instance->withOption('base_uri', 'http://opt.test/');

        self::assertSame('http://opt.test/', $new->getBaseUri());
        self::assertSame('', $this->instance->getBaseUri());
    }

    #[Test]
    public function withDefaultHeaderAddsHeaderToRequestsOfClone(): void
    {
        $new = $this->instance->withDefaultHeader('X-Token', 'abc');
        $new->get('http://example.com');

        self::assertSame('abc', $this->transportOf($new)->request->getHeaderLine('X-Token'));
    }

    #[Test]
    public function withDefaultHeadersReplacesOrMergesHeaders(): void
    {
        $client = $this->createClient(['headers' => ['X-A' => '1']]);

        $merged = $client->withDefaultHeaders(['X-B' => '2'], true);
        $merged->get('http://example.com');
        $mergedRequest = $this->transportOf($merged)->request;

        $replaced = $client->withDefaultHeaders(['X-B' => '2']);
        $replaced->get('http://example.com');
        $replacedRequest = $this->transportOf($replaced)->request;

        self::assertSame('1', $mergedRequest->getHeaderLine('X-A'));
        self::assertSame('2', $mergedRequest->getHeaderLine('X-B'));
        self::assertFalse($replacedRequest->hasHeader('X-A'));
        self::assertSame('2', $replacedRequest->getHeaderLine('X-B'));
    }

    #[Test]
    public function cloneDuplicatesTransportAndOptions(): void
    {
        $new = $this->instance->withBaseUri('http://x.test/');

        self::assertNotSame($this->instance->getTransport(), $new->getTransport());
    }

    #[Test]
    public function mustMethodsViaCallPassBodyAndOptions(): void
    {
        $this->instance->mustPost('http://example.com', 'data');

        self::assertSame('POST', $this->transport->request->getMethod());
        self::assertSame('data', (string) $this->transport->request->getBody());

        $this->instance->mustGet('http://example.com');

        self::assertSame('GET', $this->transport->request->getMethod());
    }

    #[Test]
    public function callThrowsBadMethodCallForUnknownMethod(): void
    {
        $this->expectException(BadMethodCallException::class);

        $this->instance->notExistsMethod();
    }

    #[Test]
    public function getAsyncTransportThrowsWhenTransportIsNotCurl(): void
    {
        $this->expectException(DomainException::class);

        $this->instance->getAsyncTransport();
    }

    #[Test]
    public function getAsyncTransportCreatesMultiCurlTransportOnceForCurl(): void
    {
        $client = new HttpClient([], new CurlTransport());

        $async = $client->getAsyncTransport();

        self::assertInstanceOf(MultiCurlTransport::class, $async);
        self::assertSame($async, $client->getAsyncTransport());
    }

    #[Test]
    public function requestAcceptsResourceAndStreamBodies(): void
    {
        $resource = fopen('php://memory', 'r+');
        fwrite($resource, 'from-resource');
        rewind($resource);

        $this->instance->post('http://example.com', $resource);

        self::assertSame('from-resource', (string) $this->transport->request->getBody());

        $this->instance->post('http://example.com', Stream::fromString('from-stream'));

        self::assertSame('from-stream', (string) $this->transport->request->getBody());
    }

    #[Test]
    public function requestWrapsFormDataInRequestBodyStream(): void
    {
        $this->instance->post('http://example.com', HttpClient::formData(['a' => 'b']));

        self::assertInstanceOf(RequestBodyStream::class, $this->transport->request->getBody());
        self::assertSame(['a' => 'b'], $this->transport->request->getBody()->getData());
    }

    #[Test]
    public function uploadFileHelpersCreateUploadObjects(): void
    {
        self::assertInstanceOf(HttpUploadFile::class, HttpClient::uploadFile(__FILE__, 'text/x-php', 'a.php'));
        self::assertInstanceOf(HttpUploadStringFile::class, HttpClient::uploadFileData('abc', 'text/plain', 'a.txt'));
        $upload = HttpClient::uploadFileStream(fopen('php://memory', 'r'));

        self::assertInstanceOf(HttpUploadStream::class, $upload);
        self::assertInstanceOf(StreamInterface::class, $upload->getStream());
    }

    #[Test]
    public function toCurlCmdOutputsJsonBodyWithoutSplitting(): void
    {
        $cmd = $this->instance->toCurlCmd('POST', 'http://example.com', ['a' => 'b&c']);

        self::assertStringContainsString("--request POST 'http://example.com'", $cmd);
        self::assertStringContainsString("-d '{\"a\":\"b&c\"}'", $cmd);
    }

    #[Test]
    public function toCurlCmdAttachesCommandToThrownRequestException(): void
    {
        $transport = new class () extends MockTransport {
            public function request(RequestInterface $request, array|TransportOptions $options = []): HttpClientResponse
            {
                throw new HttpRequestException('failed');
            }
        };
        $client = $this->createClient([], $transport);

        try {
            $client->get('http://example.com/fail');
            self::fail('Exception expected');
        } catch (HttpRequestException $e) {
            self::assertStringContainsString('http://example.com/fail', $e->toCurlCmd());
        }
    }

    protected function transportOf(HttpClient $client): MockTransport
    {
        return $client->getTransport();
    }
}
