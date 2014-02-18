<?php

include_once __DIR__ . '/init.php';

$dm = new \Windwalker\Data\DataMapper\DataMapper('#__datamapper');

$data = new \Windwalker\Data\Data(
	[
		'year' => 20
	]
);

$result = $dm->updateAll($data, [
	'id' => new \Windwalker\Data\Compare\GteCompare('id', 5)
]);

print_r($result);
