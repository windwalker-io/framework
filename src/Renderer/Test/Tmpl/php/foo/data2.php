<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 - 2015 LYRASOFT. All rights reserved.
 * @license    GNU Lesser General Public License version 3 or later.
 */

use Windwalker\Data\Data;

/** @var Data $data */
?>
<h1><?php echo $data->title; ?></h1>
<?php echo $this->load('foo/data3', array('content' => 'Morbi suscipit ante massa')); ?>
