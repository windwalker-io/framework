<?php

use Windwalker\DataMapper\DataMapper;

include_once __DIR__ . '/init.php';

$dm = new DataMapper('#__datamapper');

$data = new \Windwalker\Data\Data(
	[
		'year' => 20,
		'foo' => 2345
	]
);

$result = $dm->updateAll($data, [
	'id' => new \Windwalker\Compare\GteCompare('id', 5)
]);

print_r($result);
