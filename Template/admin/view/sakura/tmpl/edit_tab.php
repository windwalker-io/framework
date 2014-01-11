<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

$tab = $data->tab;
?>

<?php echo JHtmlBootstrap::addTab('sakuraEditTab', $tab, \JText::_($data->view->option . '_EDIT_' . strtoupper($tab))) ?>

<?php echo $this->loadTemplate($tab); ?>

<?php echo JHtmlBootstrap::endTab(); ?>
