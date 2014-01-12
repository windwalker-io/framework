<?php
/**
 * Part of Windwalker project. 
 *
 * @copyright  Copyright (C) 2011 - 2014 SMS Taiwan, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Windwalker\Elfinder\View;

use JRegistry;
use JURI;
use Windwalker\View\Html\AbstractHtmlView;

/**
 * Class ElfinderView
 *
 * @since 1.0
 */
class DisplayView extends AbstractHtmlView
{
	/**
	 * @var  array  Property defaultToolbar.
	 */
	protected $defaultToolbar = array(
		array('back', 'forward'),
		array('reload'),
		//array('home', 'up'),
		array('mkdir', 'mkfile', 'upload'),
		//array('open', 'download', 'getfile'),
		array('info'),
		array('quicklook'),
		array('copy', 'cut', 'paste'),
		array('rm'),
		array('duplicate', 'rename', 'edit', 'resize'),
		//array('extract', 'archive'),
		array('search'),
		array('view'),
		array('help')
	);

	/**
	 * render
	 *
	 * @return string
	 */
	public function render()
	{
		// Init some API objects
		// ================================================================================
		$container  = $this->getContainer();
		$input      = $container->get('input');
		$asset      = $container->get('helper.asset');
		$lang       = $container->get('language');
		$lang_code  = $lang->getTag();
		$lang_code  = str_replace('-', '_', $lang_code);

		$com_option = $this->option ? : $input->get('option');
		$config     = new JRegistry($this->data->config);
		
		// Script
		$this->displayScript($com_option, $config);

		// Get Request
		$finder_id  = $input->get('finder_id');
		$modal      = ($input->get('tmpl') == 'component') ? : false;
		$root       = $config->get('root', $input->get('root', '/'));
		$start_path = $config->get('start_path', $input->get('start_path', '/'));
		$site_root  = JURI::root(true) . '/';

		$toolbar = $config->get('toolbar', $this->defaultToolbar);
		$toolbar = json_encode($toolbar);

		$onlymimes = $config->get('onlymimes', $input->get('onlymimes', null));

		if ($onlymimes)
		{
			$onlymimes = is_array($onlymimes) ? $onlymimes : explode(',', $onlymimes);
			$onlymimes = array_map('trim', $onlymimes);
			$onlymimes = array_map(array('\\Windwalker\\Helper\\StringHelper', 'quote'), $onlymimes);
			$onlymimes = implode(',', $onlymimes);
		}

		// Get INI setting
		$upload_max = ini_get('upload_max_filesize');
		$upload_num = ini_get('max_file_uploads');

		$upload_limit = 'Max upload size: ' . $upload_max;
		$upload_limit .= ' | Max upload files: ' . $upload_num;

		// Set Script
		$getFileCallback = !$modal ? '' : "
            ,
            getFileCallback : function(file){
                if (window.parent) window.parent.AKFinderSelect( '{$finder_id}',AKFinderSelected, window.elFinder, '{$site_root}');
            }";

		$script = <<<SCRIPT
		var AKFinderSelected ;
        var elFinder ;

		// Init elFinder
        jQuery(document).ready(function($) {
            elFinder = $('#elfinder').elfinder({
                url         : 'index.php?option={$com_option}&task=finder.elfinder.connect&root={$root}&start_path={$start_path}' ,
                width       : '100%' ,
                height      : 445 ,
                onlyMimes   : [$onlymimes],
                lang        : '{$lang_code}',
                uiOptions   : {
                    toolbar : {$toolbar}
                },
                handlers    : {
                    select : function(event, elfinderInstance) {
                        var selected = event.data.selected;

                        if (selected.length) {
                            AKFinderSelected = [];
                            jQuery.each(selected, function(i, e){
                                    AKFinderSelected[i] = elfinderInstance.file(e);
                            });
                        }

                    }
                }

                {$getFileCallback}

            }).elfinder('instance');

            elFinder.ui.statusbar.append( '<div class="akfinder-upload-limit">{$upload_limit}</div>' );
        });
SCRIPT;

		$asset->internalJS($script);

		return '<div class="row-fluid">
                <div id="elfinder" class="span12 windwalker-finder"></div>
            </div>';
	}

	/**
	 * displayScript
	 *
	 * @param $com_option
	 * @param $config
	 *
	 * @return void
	 */
	private function displayScript($com_option, $config)
	{
		$lang      = $this->container->get('language');
		$lang_code = $lang->getTag();
		$lang_code = str_replace('-', '_', $lang_code);

		// Include elFinder and JS
		// ================================================================================

		$asset = $this->container->get('helper.asset');

		// JQuery
		$asset->jquery();
		$asset->bootstrap();

		// ElFinder includes
		$asset->addCss('js/jquery-ui/css/smoothness/jquery-ui-1.8.24.custom.css');
		$asset->addCss('js/elfinder/css/elfinder.min.css');
		$asset->addCss('js/elfinder/css/theme.css');

		$asset->addJs('js/jquery-ui/js/jquery-ui.min.js');
		$asset->addJs('js/elfinder/js/elfinder.min.js');
		$asset->addJs('js/elfinder/js/i18n/elfinder.' . $lang_code . '.js');
	}
}
