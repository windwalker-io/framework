<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 ${ORGANIZATION}.
 * @license    __LICENSE__
 */

use Windwalker\Database\DatabaseFactory;

require getcwd() . '/vendor/autoload.php';

$db = DatabaseFactory::getDbo(
    'sqlsrv',
    [
        'host' => 'localhost',
        'user' => 'sa',
        'password' => '',
        'port' => 1433,
        'prefix' => 'ww_',
    ]
);

$database = $db->getDatabase('hello');
$database->drop(true);

$database = $database->create(true);

$db->disconnect();

unset($db);

DatabaseFactory::reset();

$db = DatabaseFactory::getDbo(
    'sqlsrv',
    [
        'host' => 'localhost',
        'user' => 'sa',
        'password' => '',
        'port' => 1433,
        'prefix' => 'ww_',
    ]
);

$database = $db->getDatabase('hello');
$database->drop(true);

$database->create(true);
