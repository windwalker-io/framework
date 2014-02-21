<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

include_once __DIR__ . '/init.php';

$dm = new \Windwalker\DataMapper\RelationDataMapper;

$dm->addTable('article', '#__content')
	->addTable('cat', '#__categories', 'article.catid = cat.id');

$result = $dm->find(null, 'article.title desc');

print_r($result);
