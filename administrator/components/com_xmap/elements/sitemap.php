<?php
/**
 * @version		$Id$
 * @copyright   Copyright (C) 2005 - 2009 Joomla! Vargas. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @author		Guillermo Vargas (guille@vargas.co.cr)
 */
 
// no direct access
defined('_JEXEC') or die;

class JElementSitemap extends JElement
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	var	$_name = 'Sitemap';

	function fetchElement($name, $value, &$node, $control_name)
	{
		global $mainframe;

		$db		=& JFactory::getDBO();
		$fieldName	= $control_name.'['.$name.']';
		
		$sql = "SELECT id, name from #__xmap_sitemap order by name";
		$db->setQuery($sql);
		$rows = $db->loadObjectList();

		$html = JHTML::_('select.genericlist',$rows,$fieldName,'','id','name',$value);

		return $html;
	}

}
