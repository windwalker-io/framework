<?php
/**
 * @package        Asikart.Module
 * @subpackage     {{extension.element.lower}}
 * @copyright      Copyright (C) 2014 SMS Taiwan, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;
?>
<div class="{{extension.name.lower}}-module-wrap<?php echo $classSfx; ?>">
	<div class="{{extension.name.lower}}-module-wrap-inner">

		<ul class="{{extension.name.lower}}-module-list nav nav-tabs nav-stacked">
			<?php foreach ($items as $item): ?>
				<li class="{{extension.name.lower}}-module-list-item">
					<?php echo JHtml::_('link', $item->link, Mod{{extension.name.cap}}Helper::escape("{$item->item_created} - {$item->item_title}")); ?>
				</li>
			<?php endforeach; ?>
		</ul>

	</div>
</div>