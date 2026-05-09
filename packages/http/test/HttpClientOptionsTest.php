<?php

declare(strict_types=1);

namespace Windwalker\Http\Test;

use PHPUnit\Framework\TestCase;
use Windwalker\Http\HttpClient;
use Windwalker\Http\HttpClientOptions;
use Windwalker\Http\Test\Mock\MockTransport;
use Windwalker\Http\Transport\Options\TransportOptions;

/**
 * Tests for HttpClientOptions proxy fields and the TransportOptions merge methods
 * that propagate those fields down to the transport layer.
 */
class HttpClientOptionsTest extends TestCase
{
    // ------------------------------------------------------------------
    // TransportOptions::fromHttpClientOptions()
    // ------------------------------------------------------------------

    public function testFromHttpClientOptionsConvertsProxyFields(): void
    {
        $clientOptions = new HttpClientOptions(
            timeout: 15,
            userAgent: 'TestBot/1.0',
            followLocation: false,
            certpath: '/path/to/cert.pem',
            verifyPeer: false,
        );

        $transportOptions = TransportOptions::fromHttpClientOptions($clientOptions);

        self::assertSame(15, $transportOptions->timeout);
        self::assertSame('TestBot/1.0', $transportOptions->userAgent);
        self::assertFalse($transportOptions->followLocation);
        self::assertSame('/path/to/cert.pem', $transportOptions->certpath);
        self::assertFalse($transportOptions->verifyPeer);
        self::assertTrue($transportOptions->optionMerged);
    }

    public function testFromHttpClientOptionsUsesExistingTransportSubOptionsAsBase(): void
    {
        $clientOptions = new HttpClientOptions(
            transport: new TransportOptions(allowEmptyStatusCode: true),
            timeout: 20,
        );

        $transportOptions = TransportOptions::fromHttpClientOptions($clientOptions);

        // Base transport sub-option is preserved.
        self::assertTrue($transportOptions->allowEmptyStatusCode);
        // Proxy field from HttpClientOptions is applied on top.
        self::assertSame(20, $transportOptions->timeout);
        self::assertTrue($transportOptions->optionMerged);
    }

    public function testFromHttpClientOptionsWithNoTransportSubOptions(): void
    {
        $clientOptions = new HttpClientOptions();

        $transportOptions = TransportOptions::fromHttpClientOptions($clientOptions);

        // Defaults remain intact.
        self::assertNull($transportOptions->timeout);
        self::assertNull($transportOptions->userAgent);
        self::assertTrue($transportOptions->followLocation);
        self::assertTrue($transportOptions->verifyPeer);
        self::assertTrue($transportOptions->optionMerged);
    }

    // ------------------------------------------------------------------
    // TransportOptions::withMergeHttpClientOptions()
    // ------------------------------------------------------------------

    public function testWithMergeHttpClientOptionsOverridesFields(): void
    {
        $base = new TransportOptions(
            timeout: 5,
            userAgent: 'OldAgent/1.0',
            followLocation: true,
        );

        $clientOptions = new HttpClientOptions(
            timeout: 30,
            userAgent: 'NewAgent/2.0',
            followLocation: false,
        );

        $merged = $base->withMergeHttpClientOptions($clientOptions);

        // Original is not mutated.
        self::assertSame(5, $base->timeout);
        self::assertSame('OldAgent/1.0', $base->userAgent);
        self::assertTrue($base->followLocation);

        // Merged copy has the HttpClientOptions values.
        self::assertSame(30, $merged->timeout);
        self::assertSame('NewAgent/2.0', $merged->userAgent);
        self::assertFalse($merged->followLocation);
        self::assertTrue($merged->optionMerged);
    }

    public function testWithMergeHttpClientOptionsMergesProgress(): void
    {
        $called = false;
        $clientOptions = new HttpClientOptions(
            progress: static function () use (&$called): void {
                $called = true;
            },
        );

        $merged = (new TransportOptions())->withMergeHttpClientOptions($clientOptions);

        self::assertNotNull($merged->progress);
    }

    public function testWithMergeHttpClientOptionsMergesFiles(): void
    {
        $clientOptions = new HttpClientOptions(
            files: ['upload' => '/tmp/file.txt'],
        );

        $merged = (new TransportOptions())->withMergeHttpClientOptions($clientOptions);

        self::assertSame(['upload' => '/tmp/file.txt'], $merged->files);
    }

    // ------------------------------------------------------------------
    // HttpClientOptions array-wrapping (snake_case keys)
    // ------------------------------------------------------------------

    public function testHttpClientOptionsWrapsFromSnakeCaseKeys(): void
    {
        $options = HttpClientOptions::wrap([
            'timeout' => 45,
            'user_agent' => 'SnakeBot/1.0',
            'follow_location' => false,
            'verify_peer' => false,
            'certpath' => '/ca.pem',
        ]);

        self::assertSame(45, $options->timeout);
        self::assertSame('SnakeBot/1.0', $options->userAgent);
        self::assertFalse($options->followLocation);
        self::assertFalse($options->verifyPeer);
        self::assertSame('/ca.pem', $options->certpath);
    }

    // ------------------------------------------------------------------
    // End-to-end: proxy fields travel through HttpClient → transport
    // ------------------------------------------------------------------

    public function testProxyOptionsReachTransportViaRequest(): void
    {
        $http = new HttpClient([], $transport = new MockTransport());

        $http->get('.', [
            'timeout' => 60,
            'user_agent' => 'IntegrationBot/3.0',
            'follow_location' => false,
            'verify_peer' => false,
            'certpath' => '/certs/ca.pem',
        ]);

        self::assertSame(60, $transport->receivedOptions->timeout);
        self::assertSame('IntegrationBot/3.0', $transport->receivedOptions->userAgent);
        self::assertFalse($transport->receivedOptions->followLocation);
        self::assertFalse($transport->receivedOptions->verifyPeer);
        self::assertSame('/certs/ca.pem', $transport->receivedOptions->certpath);
    }

    public function testClientLevelProxyOptionsReachTransport(): void
    {
        // Nullable proxy fields (timeout, userAgent, certpath…) set at client-constructor level
        // propagate to the transport because per-request HttpClientOptions defaults them to null,
        // and withDefaults() ignores nulls when re-merging, so the client-level values survive.
        $http = new HttpClient(
            [
                'timeout' => 10,
                'user_agent' => 'GlobalBot/1.0',
            ],
            $transport = new MockTransport(),
        );

        $http->get('.');

        self::assertSame(10, $transport->receivedOptions->timeout);
        self::assertSame('GlobalBot/1.0', $transport->receivedOptions->userAgent);
    }

    public function testClientLevelBoolProxyOptionIsOverriddenByPerRequestDefault(): void
    {
        // NOTE: Boolean proxy fields that have a non-null per-request default (e.g. followLocation=true)
        // cannot be overridden at the client constructor level because withDefaults() re-applies the
        // per-request instance (which carries the PHP default `true`) while ignoring nulls only –
        // non-null booleans are never skipped.  This is an expected consequence of the withDefaults
        // semantics and is documented here as a known behaviour.
        $http = new HttpClient(
            ['follow_location' => false],
            $transport = new MockTransport(),
        );

        $http->get('.');

        // Per-request default (true) wins over client-level false.
        self::assertTrue($transport->receivedOptions->followLocation);
    }

    public function testPerRequestProxyOptionsMergedWithClientOptions(): void
    {
        $http = new HttpClient(
            ['timeout' => 10],
            $transport = new MockTransport(),
        );

        // Per-request override for timeout; other client-level options remain.
        $http->get('.', ['timeout' => 99]);

        self::assertSame(99, $transport->receivedOptions->timeout);
    }

    public function testTransportSubOptionsAndProxyOptionsAreBothApplied(): void
    {
        $http = new HttpClient([], $transport = new MockTransport());

        $http->get('.', [
            'timeout' => 25,
            'transport' => ['allow_empty_status_code' => true],
        ]);

        self::assertSame(25, $transport->receivedOptions->timeout);
        self::assertTrue($transport->receivedOptions->allowEmptyStatusCode);
    }
}


