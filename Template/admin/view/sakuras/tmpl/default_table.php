<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_flower
 * @copyright   Copyright (C) 2012 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Windwalker\Data\Data;

/**
 * Prepare data for this template.
 *
 * @var $container Windwalker\DI\Container
 * @var $data      Windwalker\Data\Data
 * @var $grid      Windwalker\View\Helper\GridHelper
 */
$container = $this->getContainer();
$data      = $this->getData();
$grid      = $data->grid;

// Prepare some API objects
$app  = $container->get('app');
$date = $container->get('date');
$doc  = $container->get('document');
$user = $container->get('user');

// Set order script.
$grid->registerTableSort();

echo $this->loadTemplate('test');
?>

<!-- LIST TABLE -->
<table id="sakuraList" class="table table-striped adminlist">

<!-- TABLE HEADER -->
<thead>
<tr>
	<!--SORT-->
	<th width="1%" class="nowrap center hidden-phone">
		<?php echo $grid->orderTitle(); ?>
	</th>

	<!--CHECKBOX-->
	<th width="1%" class="center">
		<?php echo JHtml::_('grid.checkAll'); ?>
	</th>

	<!--STATUS-->
	<th width="5%" class="nowrap center">
		<?php echo $grid->sortTitle('JSTATUS', 'sakura.published'); ?>
	</th>

	<!--TITLE-->
	<th class="center">
		<?php echo $grid->sortTitle('JGLOBAL_TITLE', 'sakura.title'); ?>
	</th>

	<!--CATEGORY-->
	<th width="10%" class="center">
		<?php echo $grid->sortTitle('JCATEGORY', 'category.title'); ?>
	</th>

	<!--ACCESS VIEW LEVEL-->
	<th width="5%" class="center">
		<?php echo $grid->sortTitle('JGRID_HEADING_ACCESS', 'viewlevel.title'); ?>
	</th>

	<!--CREATED-->
	<th width="10%" class="center">
		<?php echo $grid->sortTitle('JDATE', 'sakura.created'); ?>
	</th>

	<!--USER-->
	<th width="10%" class="center">
		<?php echo $grid->sortTitle('JAUTHOR', 'user.name'); ?>
	</th>

	<!--LANGUAGE-->
	<th width="5%" class="center">
		<?php echo $grid->sortTitle('JGRID_HEADING_LANGUAGE', 'lang.title'); ?>
	</th>

	<!--ID-->
	<th width="1%" class="nowrap center">
		<?php echo $grid->sortTitle('JGRID_HEADING_ID', 'sakura.id'); ?>
	</th>
</tr>
</thead>

<!--PAGINATION-->
<tfoot>
<tr>
	<td colspan="15">
		<div class="pull-left">
			<?php echo $data->pagination->getListFooter(); ?>
		</div>
	</td>
</tr>
</tfoot>

<!-- TABLE BODY -->
<tbody>
<?php foreach ($data->items as $i => $item)
	:
	// Prepare data
	$item = new Data($item);

	// Prepare item for GridHelper
	$grid->setItem($item, $i);
	?>
	<tr class="sakura-row" sortable-group-id="<?php echo $item->catid; ?>">
		<!-- DRAG SORT -->
		<td class="order nowrap center hidden-phone">
			<?php echo $grid->dragSort(); ?>
		</td>

		<!--CHECKBOX-->
		<td class="center">
			<?php echo JHtml::_('grid.id', $i, $item->sakura_id); ?>
		</td>

		<!--PUBLISHED-->
		<td class="center">
			<div class="btn-group">
				<?php echo $grid->published() ?>
			</div>
		</td>

		<!--TITLE-->
		<td class="n/owrap has-context quick-edit-wrap">

			<div class="pull-left fltlft">

				<div class="item-title">
					<!-- Checkout -->
					<?php echo $grid->checkoutButton(); ?>

					<!-- Title -->
					<?php echo $grid->editTitle(); ?>
				</div>

				<!-- Sub Title -->
				<div class="small">
					<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?>
				</div>
			</div>
		</td>

		<!--CATEGORY-->
		<td class="center">
			<?php echo $this->escape($item->category_title); ?>
		</td>

		<!--ACCESS VIEW LEVEL-->
		<td class="center">
			<?php echo $this->escape($item->viewlevel_title); ?>
		</td>

		<!--CREATED-->
		<td class="center">
			<?php echo JHtml::_('date', $item->created, JText::_('DATE_FORMAT_LC4')); ?>
		</td>

		<!--USER-->
		<td class="center">
			<?php echo $this->escape($item->user_name); ?>
		</td>

		<!--LANGUAGE-->
		<td class="center">
			<?php
			if ($item->language == '*')
			{
				echo JText::alt('JALL', 'language');
			}
			else
			{
				echo $item->lang_title ? $this->escape($item->lang_title) : JText::_('JUNDEFINED');
			}
			?>
		</td>

		<!--ID-->
		<td class="center">
			<?php echo (int) $item->id; ?>
		</td>

	</tr>
<?php endforeach; ?>
</tbody>
</table>
