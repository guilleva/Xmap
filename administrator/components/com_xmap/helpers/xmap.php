<?php
/**
 * @version		$Id$
 * @copyright   Copyright (C) 2007 - 2009 Joomla! Vargas. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @author		Guillermo Vargas (guille@vargas.co.cr)
 */


// No direct access
defined('_JEXEC') or die;

/**
 * Xmap component helper.
 *
 * @package	     Xmap
 * @subpackage  com_xmap
 * @since	       2.0
 */
class XmapHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param       string  The name of the active view.
	 */
	public static function addSubmenu($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('Xmap_Submenu_Sitemaps'),
			'index.php?option=com_xmap',
			$vName == 'sitemaps'
		);
		JSubMenuHelper::addEntry(
			JText::_('Xmap_Submenu_Extensions'),
			'index.php?option=com_plugins&view=plugins&filter_folder=xmap',
			$vName == 'extensions');
	}
}
