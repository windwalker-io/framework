<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$data = $displayData;

// Load the form filters
$filters = $data['view']->filterForm->getGroup('filter');
$searches = $data['view']->filterForm->getFieldset('multisearch');
?>
<?php if ($searches) : ?>
	<?php foreach ($searches as $fieldName => $field) : ?>
		<div class="js-stools-field-filter">
			<?php echo $field->label; ?>
			<?php echo $field->input; ?>
		</div>
	<?php endforeach; ?>
<?php endif; ?>

<?php if ($filters) : ?>
	<?php foreach ($filters as $fieldName => $field) : ?>
		<div class="js-stools-field-filter">
			<?php echo $field->label; ?>
			<?php echo $field->input; ?>
		</div>
	<?php endforeach; ?>
<?php endif; ?>
