<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

include __DIR__ . '/../vendor/autoload.php';

$data = new \Windwalker\Data\Data(array('aaa' => 'RRR', 2, 3));

print_r($data);

foreach ($data as $val)
{
	echo $val;
}

var_dump($data->aaa);
var_dump($data->bbb);
var_dump($data->ccc);
