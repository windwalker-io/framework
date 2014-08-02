<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

include_once __DIR__ . '/init.php';

$dm = new \Windwalker\DataMapper\DataMapper('#__flower_sakuras');

$result = $dm->findOne(null, 'title desc');

print_r($result);
