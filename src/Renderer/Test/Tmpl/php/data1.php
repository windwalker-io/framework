<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU Lesser General Public License version 2.1 or later.
 */

use Windwalker\Data\Data;

/** @var Data $data */
?>

<div id="data" class="<?php echo $data->class; ?>">
	<?php echo $this->load('foo/data2'); ?>
</div>
