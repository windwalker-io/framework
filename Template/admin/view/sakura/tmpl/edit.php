<?php

$container = $this->getContainer();
$form = $this->data->form;
$item = $this->data->item;
$this->data->asset = $container->get('helper.asset');

$xul = new \Windwalker\Xul\XulEngine;

$xul->setPath(__DIR__);
echo $xul->render('edit', $this->data);

if (false):
?>

<form action="<?php echo JURI::getInstance(); ?>"  method="post" name="adminForm" id="adminForm"
	class="form-validate" enctype="multipart/form-data">
	<?php
	foreach ($form->getFieldsets() as $key => $fieldset)
		:
		?>
		<fieldset>
			<legend><?php echo !empty($fieldset->label) ? JText::_($fieldset->label) : JText::_('COM_FLOWER_EDIT_FIELDSET_' . $fieldset->name); ?></legend>

			<?php
			foreach ($form->getFieldset($fieldset->name) as $field)
			{
				echo $field->getControlGroup();
			}
			?>

		</fieldset>
	<?php
	endforeach;
	?>

	<!-- Hidden Inputs -->
	<div id="hidden-inputs">
		<input type="hidden" name="option" value="com_flower" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

<?php endif; ?>
