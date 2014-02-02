<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

$tab       = $data->tab;
$fieldsets = $data->form->getFieldsets();
?>

<?php echo JHtmlBootstrap::addTab('sakuraEditTab', $tab, \JText::_($data->view->option . '_EDIT_' . strtoupper($tab))) ?>

<div class="row-fluid">
	<div class="span8">
		<?php echo $this->loadTemplate('fieldset', array('fieldset' => $fieldsets['information'], 'class' => 'form-horizontal')); ?>

		<?php echo $this->loadTemplate('fieldset', array('fieldset' => $fieldsets['text'], 'class' => 'form-horizontal')); ?>
	</div>

	<div class="span4">
		<?php echo $this->loadTemplate('fieldset', array('fieldset' => $fieldsets['publish'], 'class' => 'form-horizontal')); ?>
	</div>
</div>

<?php echo JHtmlBootstrap::endTab(); ?>
