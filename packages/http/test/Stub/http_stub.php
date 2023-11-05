<?php

declare(strict_types=1);

// Travis-CI use Ubuntu 12.04 so the default php version is 5.3, we can not use short array syntax
// Use this line to generate empty array.
$globals = (array) null;
$_SERVER['HTTP_HOST'];

foreach ($GLOBALS as $key => $value) {
    if ($key === 'GLOBALS' || $key === 'globals' || $key === 'value') {
        continue;
    }

    $globals[$key] = $value;
}

parse_str(file_get_contents('php://input'), $globals['data']);

header('Content-Type: application/json');
echo json_encode($globals);
