<?php

declare(strict_types=1);

/**
 * Overrides headers_sent() inside the Windwalker\Http\Response namespace so tests can force
 * RedirectResponse::getBody() down the "headers already sent" branch. It delegates to the native
 * function unless a test sets the flag. Remove once RedirectResponse no longer calls headers_sent().
 */

namespace Windwalker\Http\Response {
    function headers_sent(): bool
    {
        return \Windwalker\Http\Test\Stub\HeadersSentSwitch::$forced ?? \headers_sent();
    }
}

namespace Windwalker\Http\Test\Stub {
    class HeadersSentSwitch
    {
        public static ?bool $forced = null;
    }
}
