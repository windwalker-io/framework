<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2014 {ORGANIZATION}. All rights reserved.
 * @license    GNU General Public License version 2 or later;
 */

?>

<?php $this->extend('foo/extend2'); ?>

<?php $this->block('sakura'); ?>
	<?php echo $this->parent(); ?>
	<span>Sed tempor urna quis varius luctus.</span>
<?php $this->endblock(); ?>
