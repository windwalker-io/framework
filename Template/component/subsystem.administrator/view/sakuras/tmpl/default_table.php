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
			<th>Title</th>
			<th>Category</th>
			<th>State</th>
			<th>Order</th>
			<th>ID</th>
		</tr>
		</thead>
		<tbody>
		<?php
			foreach ($data->items as $item)
				:
				$item = new JData($item);
				?>
				<tr>
					<td><?php echo $item->title; ?></td>
					<td><?php echo $item->catidd; ?></td>
					<td><?php echo $item->published; ?></td>
					<td><?php echo $item->ordering; ?></td>
					<td><?php echo $item->id; ?></td>
				</tr>
			<?php
			endforeach;
		?>
</tbody>
</table>