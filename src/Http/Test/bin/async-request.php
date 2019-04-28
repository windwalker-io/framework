<?php declare(strict_types=1);
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

include_once __DIR__ . '/../../../../vendor/autoload.php';

$http = new \Windwalker\Http\AsyncHttpClient();

$http->get('http://google.com');
$http->get('http://facebook.com');

$http->resolve(
    function ($responses, $errors) {
        foreach ($responses as $response) {
            echo $response->getBody()->__toString();
        }
    }
);
