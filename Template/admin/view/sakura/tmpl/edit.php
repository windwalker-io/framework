<?php

$form = $this->data->form;
$item = $this->data->item;
?>

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


