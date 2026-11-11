<?php

declare(strict_types=1);

namespace Windwalker\Http\Exception;

/**
 * Thrown when a response body exceeds the configured `maxResponseSize` and the
 * transport aborted the download to avoid memory exhaustion.
 *
 * @since  4.1
 */
class HttpResponseSizeExceededException extends HttpRequestException {}
