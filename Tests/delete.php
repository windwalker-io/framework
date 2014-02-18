<?php

include_once __DIR__ . '/init.php';

$dm = new \Windwalker\Data\Joomla\DataMapper('#__datamapper');

$result = $dm->delete([
	'id' => 5
]);

var_dump($result);
