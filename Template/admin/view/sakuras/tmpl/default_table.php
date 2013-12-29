<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_flower
 * @copyright   Copyright (C) 2012 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Windwalker\Data\Data;

$data = $this->getData();

// Init some API objects
// ================================================================================
$app    = JFactory::getApplication();
$date   = JFactory::getDate('now', JFactory::getConfig()->get('offset'));
$doc    = JFactory::getDocument();
$uri    = JFactory::getURI();
$user   = JFactory::getUser();
$userId = $user->get('id');

$listOrder = $data->state->get('list.ordering');
$listDirn  = $data->state->get('list.direction');
$orderCol  = $data->state->get('list.orderCol', 'sakura.ordering');
$saveOrder = $listOrder == $orderCol;
$trashed   = $data->state->get('filter.published') == -2 ? true : false;
?>

<!-- List Table -->
<table class="table table-striped adminlist" id="itemList">
<thead>
<tr>
	<!--SORT-->
	<th width="1%" class="nowrap center hidden-phone">
		<?php echo JHtml::_('searchtools.sort', '', $orderCol, $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
	</th>

	<!--CHECKBOX-->
	<th width="1%" class="center">
		<?php echo JHtml::_('grid.checkAll'); ?>
	</th>

	<!--TITLE-->
	<th class="center">
		<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'sakura.title', $listDirn, $listOrder); ?>
	</th>

	<!--PUBLISHED-->
	<th width="5%" class="nowrap center">
		<?php echo JHtml::_('searchtools.sort', 'JPUBLISHED', 'sakura.published', $listDirn, $listOrder); ?>
	</th>

	<!--CATEGORY-->
	<th width="10%" class="center">
		<?php echo JHtml::_('searchtools.sort', 'JCATEGORY', 'category.title', $listDirn, $listOrder); ?>
	</th>

	<!--ACCESS VIEW LEVEL-->
	<th width="5%" class="center">
		<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ACCESS', 'viewlevel.title', $listDirn, $listOrder); ?>
	</th>

	<!--CREATED-->
	<th width="10%" class="center">
		<?php echo JHtml::_('searchtools.sort', 'JDATE', 'sakura.created', $listDirn, $listOrder); ?>
	</th>

	<!--USER-->
	<th width="10%" class="center">
		<?php echo JHtml::_('searchtools.sort', 'JAUTHOR', 'user.name', $listDirn, $listOrder); ?>
	</th>

	<!--LANGUAGE-->
	<th width="5%" class="center">
		<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_LANGUAGE', 'lang.title', $listDirn, $listOrder); ?>
	</th>

	<!--ID-->
	<th width="1%" class="nowrap center">
		<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'sakura.id', $listDirn, $listOrder); ?>
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

		<!-- Limit Box -->
		<div class="btn-group pull-right hidden-phone">
			<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
			<?php echo $data->pagination->getLimitBox(); ?>
		</div>
	</td>
</tr>
</tfoot>

<tbody>
<?php foreach ($data->items as $i => $item)
	:
	$item = new Data($item);

	$ordering   = ($listOrder == $orderCol);
	$canEdit    = $user->authorise('core.edit', 'com_flower.sakura.' . $item->sakura_id);
	$canCheckin = $user->authorise('core.edit.state', 'com_flower.sakura.' . $item->sakura_id) || $item->sakura_checked_out == $userId || $item->sakura_checked_out == 0;
	$canChange  = $user->authorise('core.edit.state', 'com_flower.sakura.' . $item->sakura_id) && $canCheckin;
	$canEditOwn = $user->authorise('core.edit.own', 'com_flower.sakura.' . $item->sakura_id) && $item->sakura_created_by == $userId;
	?>
	<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->sakura_catid; ?>">
		<!-- Drag sort for -->
		<td class="order nowrap center hidden-phone">
			<?php
			if ($canChange)
			{
				$disableClassName = '';
				$disabledLabel    = '';
			}

			if (!$saveOrder || !$canOrder)
				:
				$disabledLabel    = JText::_('JORDERINGDISABLED');
				$disableClassName = 'inactive tip-top';
			?>
			<span class="sortable-handler hasTooltip <?php echo $disableClassName ?>" title="<?php echo $disabledLabel ?>">
				<i class="icon-menu"></i>
			</span>
				<input type="hidden" style="display:none" name="order[]" size="5" value="<?php echo $item->sakura_ordering; ?>" class="text-area-order " />
			<?php else: ?>
			<span class="sortable-handler inactive">
				<i class="icon-menu"></i>
			</span>
			<?php endif; ?>
		</td>

		<!--CHECKBOX-->
		<td class="center">
			<?php echo JHtml::_('grid.id', $i, $item->a_id); ?>
		</td>

		<!--TITLE-->
		<td class="n/owrap has-context quick-edit-wrap">

			<div class="pull-left fltlft">

				<div class="item-title">
					<!-- Checkout -->
					<?php if ($item->sakura_checked_out) : ?>
						<?php echo JHtml::_('jgrid.checkedout', $i, $item->user_name, $item->sakura_checked_out_time, 'sakuras.', $canCheckin); ?>
					<?php endif; ?>

					<!-- Title -->
					<?php if ($canEdit || $canEditOwn) : ?>
						<a href="<?php echo JRoute::_('index.php?option=com_flower&task=sakura.edit&id=' . $item->sakura_id); ?>">
							<?php echo $this->escape($item->sakura_title); ?>
						</a>
					<?php else: ?>
						<?php echo $this->escape($item->sakura_title); ?>
					<?php endif; ?>
				</div>

				<!-- Sub Title -->
				<div class="small">
					<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->sakura_alias)); ?>
				</div>
			</div>

			<!-- Title Edit Button -->
			<div class="pull-left">
				<?php
				// Create dropdown items
				if ($canEdit || $canEditOwn)
				{
					JHtml::_('dropdown.edit', $item->a_id, 'sakura.');
					JHtml::_('dropdown.divider');
				}


				if ($canChange || $canEditOwn)
				{
					if ($item->sakura_published)
					{
						JHtml::_('dropdown.unpublish', 'cb' . $i, 'sakuras.');
					}
					else
					{
						JHtml::_('dropdown.publish', 'cb' . $i, 'sakuras.');
					}

					JHtml::_('dropdown.divider');
				}


				if ($item->sakura_checked_out && $canCheckin)
				{
					JHtml::_('dropdown.checkin', 'cb' . $i, 'sakuras.');
				}

				if ($canChange || $canEditOwn)
				{
					if ($trashed)
					{
						JHtml::_('dropdown.untrash', 'cb' . $i, 'sakuras.');
					}
					else
					{
						JHtml::_('dropdown.trash', 'cb' . $i, 'sakuras.');
					}
				}

				// Render dropdown list
				echo JHtml::_('dropdown.render');
				?>
			</div>
		</td>

		<!--PUBLISHED-->
		<td class="center">
			<?php echo JHtml::_('jgrid.published', $item->sakura_published, $i, 'sakuras.state.', $canChange, 'cb', $item->sakura_publish_up, $item->sakura_publish_down); ?>
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
			<?php echo JHtml::_('date', $item->sakura_created, JText::_('DATE_FORMAT_LC4')); ?>
		</td>

		<!--USER-->
		<td class="center">
			<?php echo $this->escape($item->user_name); ?>
		</td>

		<!--LANGUAGE-->
		<td class="center">
			<?php
			if ($item->sakura_language == '*')
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
			<span><?php echo (int) $item->sakura_id; ?></span>
		</td>

	</tr>
<?php endforeach; ?>
</tbody>
</table>
