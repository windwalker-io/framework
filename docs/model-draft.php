<?php
/**
 * Part of Windwalker project.
 *
 * @copyright  Copyright (C) 2016 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later.
 */
$model = UserModel::create();

// All
$items = $model->getItems();

// Where Conditions
$model->setScope(
    'activated', function ($query, $model) {
        $query->where('activated >= 1');
    }
);

$items = $model->reset()
    ->filter('registered', new DateTime('now'))
    ->filter('group.id', 2)
    ->scope('activated')
    ->where('login_count', '>=', 15)
    ->order('id DESC')
    ->start(5)
    ->limit(20)
    ->getItems([$dump = true]);

// Run as iterator
foreach ($model as $user) {
    echo $user->name;
}

// Get Pagination
$pagination = $model->pagination();
$simplePagination = $model->simplePagination();
