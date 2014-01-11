<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_flower
 * @copyright   Copyright (C) 2012 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Windwalker\View\Layout\FileLayout;

/**
 * Prepare data for this template.
 *
 * @var Windwalker\DI\Container       $container
 * @var Windwalker\Helper\AssetHelper $asset
 */
$container = $this->getContainer();
$data->asset = $container->get('helper.asset');

$listOrder = 'id';
$listDirn = 'asc';
$originalOrders = [];
?>

<div id="flower" class="windwalker sakuras tablelist row-fluid">
	<form action="<?php echo JURI::getInstance(); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">

		<?php if (!empty($this->data->sidebar)): ?>
		<div id="j-sidebar-container" class="span2">
			<h4 class="page-header"><?php echo JText::_('JOPTION_MENUS'); ?></h4>
			<?php echo $this->data->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
		<?php else: ?>
		<div id="j-main-container">
		<?php endif;?>

			<?php echo (new FileLayout('joomla.searchtools.default'))->render(array('view' => $this->data)); ?>

			<?php echo $this->loadTemplate('table'); ?>

			<?php echo (new FileLayout('joomla.batchtools.modal'))->render(array('view' => $this->data, 'task_prefix' => 'sakuras.')); ?>

			<!-- Hidden Inputs -->
			<div id="hidden-inputs">
				<input type="hidden" name="task" value="" />
				<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
				<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
				<input type="hidden" name="original_order_values" value="<?php echo implode($originalOrders, ','); ?>" />
				<?php echo JHtml::_('form.token'); ?>
			</div>

		</div>
	</form>
</div>
