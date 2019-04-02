<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2019 LYRASOFT.
 * @license    LGPL-2.0-or-later
 */

include_once __DIR__ . '/../../../vendor/autoload.php';

use Windwalker\Cache\Cache;
use Windwalker\Cache\Storage\FileStorage;

$cache = new Cache(new FileStorage(__DIR__ . '/cache'));

$cache->set('flower', ['flower' => 'sakura']);

$data = $cache->get('flower');

print_r($data);
