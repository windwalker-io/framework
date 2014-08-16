<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2008 - 2014 Asikart.com. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

include_once __DIR__ . '/init.php';

$dm = new \Windwalker\DataMapper\RelationDataMapper;

$dm->addTable('article', '#__content')
	->addTable('cat', '#__categories', 'article.catid = cat.id');

$result = $dm->find(null, 'article.title desc');

print_r($result);
