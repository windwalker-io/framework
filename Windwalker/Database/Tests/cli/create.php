<?php

include_once __DIR__ . '/init.php';

$dm = new \Windwalker\DataMapper\DataMapper('#__datamapper');

$dataset = new \Windwalker\Data\DataSet;

foreach (range(1, 5) as $row)
	$dataset[] = new \Windwalker\Data\Data(
		[
			'title' => uniqid(),
			'year' => rand(20, 100),
			'text' => str_repeat(md5(uniqid()), rand(1, 20)),
			'foo'  => 123123
		]
	);

$result = $dm->create($dataset);

print_r($result);
