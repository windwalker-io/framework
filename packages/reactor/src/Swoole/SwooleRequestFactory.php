<?php

declare(strict_types=1);

namespace Windwalker\Reactor\Swoole;

use Psr\Http\Message\ServerRequestInterface;
use Swoole\Http\Request as SwooleRequest;
use Swoole\WebSocket\Frame;
use Windwalker\Http\Factory\ServerRequestFactory;
use Windwalker\Http\Helper\MultipartParser;
use Windwalker\Http\HttpParameters;
use Windwalker\Http\Request\ServerRequest;
use Windwalker\Http\SafeJson;
use Windwalker\Reactor\WebSocket\WebSocketRequest;
use Windwalker\Stream\Stream;

use const Windwalker\Stream\READ_WRITE_FROM_BEGIN;

/**
 * The SwooleRequestFactory class.
 */
class SwooleRequestFactory
{
    public static function createPsrFromSwooleRequest(SwooleRequest $request, ?string $host): ServerRequestInterface
    {
        $server = HttpParameters::wrap((array) $request->server);
        $headers = HttpParameters::wrap((array) $request->header);

        $files = (array) $request->files;

        if (!$host) {
            $host = $server['remote_addr'];

            if ($server['port']) {
                $host .= ':' . $server['port'];
            }
        }

        $server['http_host'] = $host;

        $body = (string) $request->rawContent();

        $method = $server['REQUEST_METHOD'] ?? 'GET';

        $decodedBody = $_POST;
        $decodedFiles = $_FILES;
        $method = strtoupper($method);
        $type = (string) $headers['Content-Type'];

        if ($method === 'POST') {
            if (str_contains($type, 'application/json')) {
                $decodedBody = new SafeJson($body, true, 512, JSON_THROW_ON_ERROR);
            }
        } elseif (in_array($method, ['PUT', 'PATCH', 'DELETE', 'LINK', 'UNLINK'])) {
            if (str_contains($type, 'application/x-www-form-urlencoded')) {
                parse_str($body, $decodedBody);
            } elseif (str_contains($type, 'multipart/form-data')) {
                [$decodedBody, $decodedFiles] = array_values(MultipartParser::parseFormData($body));
            } elseif (str_contains($type, 'application/json')) {
                $decodedBody = new SafeJson($body, true, 512, JSON_THROW_ON_ERROR);
            }
        }

        $files = ServerRequestFactory::prepareFiles($files ?: $decodedFiles);

        $stream = new Stream('php://memory', READ_WRITE_FROM_BEGIN);
        $stream->write($body);
        $stream->rewind();

        return new ServerRequest(
            array_change_key_case($server->dump(), CASE_UPPER),
            $files,
            ServerRequestFactory::prepareUri($server, $headers),
            $method,
            $stream,
            $headers->dump(),
            $request->cookie ?: $_COOKIE,
            $request->get ?: $_GET,
            $request->post ?: $decodedBody,
            ServerRequestFactory::getProtocolVersion($server)
        );
    }

    public static function createPsrSwooleRequest(SwooleRequest $request, string $data = ''): WebSocketRequest
    {
        $frame = new Frame();
        $frame->fd = $request->fd;
        $frame->data = $data;

        $server = HttpParameters::wrap((array) $request->server);
        $headers = HttpParameters::wrap((array) $request->header);

        $host = $server['remote_addr'];

        if ($server['port']) {
            $host .= ':' . $server['port'];
        }

        $server['http_host'] = $host;

        $body = (string) $request->rawContent();

        $method = $server['REQUEST_METHOD'] ?? 'GET';

        $decodedBody = $request->post;
        $method = strtoupper($method);

        $stream = new Stream('php://memory', READ_WRITE_FROM_BEGIN);
        $stream->write($body);
        $stream->rewind();

        $instance = new WebSocketRequest(
            array_change_key_case($server->dump(), CASE_UPPER),
            [],
            ServerRequestFactory::prepareUri($server, $headers),
            $method,
            $stream,
            $headers->dump(),
            $request->cookie ?: $_COOKIE,
            $request->get ?: $_GET,
            $request->post ?: $decodedBody,
            ServerRequestFactory::getProtocolVersion($server)
        );

        return $instance->withFrame(new WebSocketFrameWrapper($frame));
    }

    public static function createFromSwooleFrame(Frame $frame): WebSocketRequest
    {
        return (new WebSocketRequest())->withFrame(new WebSocketFrameWrapper($frame));
    }
}
