<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_flower
 * @copyright   Copyright (C) 2012 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

$data = $this->getData();
?>
<table class="table table-striped">
	<thead>
	<tr>
		<th><?php echo JHtml::_('grid.checkAll'); ?></th>
		<th>Title</th>
		<th>Category</th>
		<th>State</th>
		<th>Order</th>
		<th>ID</th>
	</tr>
	</thead>
	<tbody>
	<?php
		foreach ($data->items as $i => $item)
			:
			$item = new JData($item);
			?>
			<tr>
				<td>
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>

				<!--TITLE-->
				<td>
					<div class="item-title">
						<a href="<?php echo JRoute::_('index.php?option=com_flower&task=sakura.edit&id=' . $item->id); ?>">
							<?php echo $this->escape($item->title); ?>
						</a>
					</div>

					<div class="small">
						<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
					</div>
				</td>

				<td><?php echo $item->catid; ?></td>

				<td><?php echo $item->published; ?></td>

				<td><?php echo $item->ordering; ?></td>

				<td><?php echo $item->id; ?></td>
			</tr>
		<?php
		endforeach;
	?>
</tbody>
</table>