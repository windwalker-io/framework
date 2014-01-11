<?php

$container = $this->getContainer();
$form = $data->form;
$item = $data->item;
$data->asset = $container->get('helper.asset');
$data->uri   = JURI::getInstance();

$tabs = array(
	'tab_basic',
	'tab_advanced',
	'tab_rules'
)
?>

<form action="<?php echo JURI::getInstance(); ?>"  method="post" name="adminForm" id="adminForm"
	class="form-validate" enctype="multipart/form-data">

	<?php echo JHtmlBootstrap::startTabSet('sakuraEditTab', array('active' => 'tab_basic')); ?>

		<?php
		foreach ($tabs as $tab)
		{
			echo $this->loadTemplate('tab', array('tab' => $tab));
		}
		?>

	<?php echo JHtmlBootstrap::endTabSet(); ?>

	<!-- Hidden Inputs -->
	<div id="hidden-inputs">
		<input type="hidden" name="option" value="com_flower" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

