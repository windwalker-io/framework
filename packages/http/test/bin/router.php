<?php

declare(strict_types=1);

/**
 * Simple router for PHP built-in server used in HTTP transport integration tests.
 *
 * Routes:
 *   GET  /__health        – health check (returns 200 "ok")
 *   *    /json            – returns query-string params as JSON
 *   *    /auth            – returns HTTP Basic credentials as "user:pass"
 *   *    /server          – returns $_SERVER as JSON
 *   *    /                – echoes the raw request body (or POST fields)
 *   *    (anything else)  – 404 Not Found
 */

// Normalise multiple leading slashes produced by URI base + path concatenation.
// e.g. REQUEST_URI "//json?foo=bar" (from CurlTransport's URI building) should be treated as "/json".
$requestPath = '/' . ltrim(
    explode('?', $_SERVER['REQUEST_URI'] ?? '/')[0],
    '/'
);

// Health check – used by PhpBuiltinServer to detect readiness
if ($requestPath === '/__health') {
    http_response_code(200);
    header('Content-Type: text/plain');
    echo 'ok';

    return true;
}

// JSON endpoint – return query params as JSON
if ($requestPath === '/json') {
    header('Content-Type: application/json');

    parse_str($_SERVER['QUERY_STRING'] ?? '', $query);

    echo json_encode($query);

    return true;
}

// Auth endpoint – return "user:pass" from Basic auth / URL credentials
if ($requestPath === '/auth') {
    $user = $_SERVER['PHP_AUTH_USER'] ?? '';
    $pass = $_SERVER['PHP_AUTH_PW'] ?? '';

    // Fallback: the test may embed credentials in the URL, which the built-in
    // server exposes via HTTP_AUTHORIZATION or not at all.
    if ($user === '' && isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $encoded = substr($_SERVER['HTTP_AUTHORIZATION'], 6);
        [$user, $pass] = explode(':', base64_decode($encoded), 2) + ['', ''];
    }

    header('Content-Type: text/plain');
    echo $user . ($pass !== '' ? ':' . $pass : '');

    return true;
}

// Server params endpoint
if ($requestPath === '/server') {
    header('Content-Type: application/json');
    echo json_encode($_SERVER);

    return true;
}

// Root / index endpoint – echo raw POST body
if ($requestPath === '/' || $requestPath === '') {
    header('Content-Type: text/plain');

    $body = file_get_contents('php://input');

    if ($body === '' || $body === false) {
        $body = http_build_query($_POST);
    }

    echo $body;

    return true;
}

// Fallback – 404
http_response_code(404);
header('Content-Type: text/plain');
echo 'Not Found';

return true;

