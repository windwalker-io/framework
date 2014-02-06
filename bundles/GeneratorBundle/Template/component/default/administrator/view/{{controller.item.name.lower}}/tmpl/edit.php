<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  {{extension.element.lower}}
 * @copyright   Copyright (C) 2012 Asikart. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

JHtmlBootstrap::tooltip();
JHtmlFormbehavior::chosen('select');

/**
 * Prepare data for this template.
 *
 * @var $container Windwalker\DI\Container
 * @var $data      Windwalker\Data\Data
 * @var $item      \stdClass
 */
$container = $this->getContainer();
$form      = $data->form;
$item      = $data->item;

// Setting tabset
$tabs = array(
	'tab_basic',
	'tab_advanced',
	'tab_rules'
)
?>
<div id="{{extension.name.lower}}" class="windwalker {{controller.item.name.lower}} edit-form row-fluid">
	<form action="<?php echo JURI::getInstance(); ?>"  method="post" name="adminForm" id="adminForm"
		class="form-validate" enctype="multipart/form-data">

		<?php echo JHtmlBootstrap::startTabSet('{{controller.item.name.lower}}EditTab', array('active' => 'tab_basic')); ?>

			<?php
			foreach ($tabs as $tab)
			{
				echo $this->loadTemplate($tab, array('tab' => $tab));
			}
			?>

		<?php echo JHtmlBootstrap::endTabSet(); ?>

		<!-- Hidden Inputs -->
		<div id="hidden-inputs">
			<input type="hidden" name="option" value="{{extension.element.lower}}" />
			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>

