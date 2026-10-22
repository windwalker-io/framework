<?php

declare(strict_types=1);

namespace Windwalker\Http\Response;

use Psr\Http\Message\StreamInterface;
use Stringable;
use Windwalker\Stream\NullStream;
use Windwalker\Stream\Stream;

/**
 * The RedirectResponse class.
 *
 * @since  3.0
 */
class RedirectResponse extends Response
{
    /**
     * Constructor.
     *
     * @param  string|Stringable|null  $uri      The redirect uri.
     * @param  int                     $status   The status code.
     * @param  array                   $headers  The custom headers.
     */
    public function __construct(string|Stringable|null $uri = null, int $status = 303, array $headers = [])
    {
        if ($uri !== null) {
            $headers['location'] = [$this->sanitizeLocation((string) $uri)];
        }

        parent::__construct(new NullStream(), $status, $headers);
    }

    /**
     * Gets the body of the message.
     *
     * @return StreamInterface Returns the body as a stream.
     */
    public function getBody(): StreamInterface
    {
        if (headers_sent()) {
            $location = $this->getHeaderLine('location');
            $scheme = parse_url($location, PHP_URL_SCHEME);

            if ($scheme === false || ($scheme !== null && !in_array(strtolower($scheme), ['http', 'https'], true))) {
                return Stream::fromString('');
            }

            $url = json_encode(
                $location,
                JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
                | JSON_INVALID_UTF8_SUBSTITUTE | JSON_THROW_ON_ERROR
            );
            $html = "<script>document.location.href=$url;</script>\n";

            return Stream::fromString($html);
        }

        return $this->stream;
    }

    public function getLocation(): string
    {
        return $this->getHeaderLine('location');
    }

    private function sanitizeLocation(string $uri): string
    {
        return preg_replace('/[\r\n]+/', '', $uri) ?? $uri;
    }
}
