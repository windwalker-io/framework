<?php

include_once __DIR__ . '/init.php';

$dm = new \Windwalker\DataMapper\DataMapper('#__datamapper');

$dataset = new \Windwalker\Data\DataSet;


$dataset[] = new \Windwalker\Data\Data(
	[
		'id' => 1,
		'title' => uniqid(),
		'year' => rand(20, 100),
		'text' => str_repeat(md5(uniqid()), rand(1, 20))
	]
);

$dataset[] = new \Windwalker\Data\Data(
	[
		'id' => 2,
		'title' => uniqid(),
		'year' => rand(20, 100),
		'text' => str_repeat(md5(uniqid()), rand(1, 20))
	]
);

$dataset[] = new \Windwalker\Data\Data(
	[
		'id' => 3,
		'title' => uniqid(),
		'year' => rand(20, 100),
		'text' => str_repeat(md5(uniqid()), rand(1, 20))
	]
);

$result = $dm->update($dataset);

print_r($result);
