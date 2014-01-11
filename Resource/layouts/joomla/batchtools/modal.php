<?php

defined('JPATH_BASE') or die;

$data = $displayData['view'];

$textPrefix = $data->view->option ? : 'LIB_WINDWALKER';

$task = JArrayHelper::getValue($displayData, 'task_prefix', '');
?>

<div class="modal hide fade" id="batchModal">
	<div class="modal-header">
		<button type="button" role="presentation" class="close" data-dismiss="modal">x</button>
		<h3><?php echo JText::_($textPrefix . '_BATCH_OPTIONS'); ?></h3>
	</div>

	<div class="modal-body form-horizontal">
		<p>
			<?php echo JText::_($textPrefix . '_BATCH_TIP'); ?>
		</p>

		<?php
		foreach ($data->batchForm->getGroup('batch') as $field)
		{
			echo $field->getControlGroup();
		}
		?>
	</div>

	<div class="modal-footer">
		<button class="btn" type="button" onclick="var inputs = jQuery('#batchModal input, #batchModal select, #batchModal textarea');inputs.attr('value', '');inputs.trigger('liszt:updated');" data-dismiss="modal">
			<?php echo JText::_('JCANCEL'); ?>
		</button>
		<button class="btn btn-primary" type="submit" onclick="Joomla.submitbutton(jQuery('input:radio:checked[name=\'batch[task]\']').attr('value'));">
			<?php echo JText::_('JGLOBAL_BATCH_PROCESS'); ?>
		</button>
	</div>
</div>
