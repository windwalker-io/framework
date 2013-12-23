<?php

$data = $this->getData();

?>
<h1>Sakuras World!</h1>

<form action="">

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

</form>