<?php
/**
 * @version       $Id$
 * @copyright     Copyright (C) 2005 - 2009 Joomla! Vargas. All rights reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @author	Guillermo Vargas (guille@vargas.co.cr)
 */

// No direct access
defined('_JEXEC') or die;

require_once(JPATH_SITE.DS.'includes'.DS.'application.php');
jimport('joomla.database.query');

/**
 * Xmap Component Sitemap Model
 *
 * @package		Xmap
 * @subpackage		com_xmap
 * @since 2.0
 */
class XmapHelper
{
	public function &getMenuItems($selections)
	{
		$db		= JFactory::getDbo();
		$user		= JFactory::getUser();
		$list		= array();

		foreach ( $selections as $menutype => $menuOptions) {
			// Initialize variables.
	
			// Get the menu items as a tree.
			$query = new JQuery;
			$query->select('n.id, n.title, n.alias, n.path, n.level, n.link, n.type, n.browserNav, n.params, n.home, n.parent_id');
			$query->from('#__menu AS n');
			$query->join('INNER', ' #__menu AS p ON p.lft = 0');
			$query->where('n.lft > p.lft');
			$query->where('n.lft < p.rgt');
			$query->order('n.lft');
	
			// Filter over the appropriate menu.
			$query->where('n.menutype = '.$db->quote($menutype));
	
			// Filter over authorized access levels and publishing state.
			$query->where('n.published = 1');
			$query->where('n.access IN ('.implode(',', (array) $user->authorisedLevels()).')');
	
			// Get the list of menu items.
			$db->setQuery($query);
			$list[$menutype] = $db->loadObjectList('id');

			// Check for a database error.
			if ($db->getErrorNum()) {
				JError::raiseWarning(021,$db->getErrorMsg());
				return array();
			}

			$router = JSite::getRouter();
			// Set some values to make nested HTML rendering easier.
			foreach ($list[$menutype] as $i => &$item)
			{
				$item->items = array();
	
				$item->params = new JObject(json_decode($item->params));

				switch ($item->type)
				{
					case 'separator':
						$item->browserNav=3;
						continue;
	
					case 'url':
						if ((strpos($item->link, 'index.php?') === 0) && (strpos($item->link, 'Itemid=') === false))
						{
							// If this is an internal Joomla link, ensure the Itemid is set.
							$item->link = $tmp->link.'&amp;Itemid='.$item->id;
						}
						break;
					case 'alias':
	
							// If this is an alias use the item id stored in the parameters to make the link.
							$item->link = 'index.php?Itemid='.$item->params->aliasoptions;
	
						break;
					default:
						if ($router->getMode() == JROUTER_MODE_SEF) {
							$item->link = 'index.php?Itemid='.$item->id;
						}
						else {
							$item->link .= '&Itemid='.$item->id;
						}
						break;
				}

	
				if ($item->home == 1)
				{
					// Correct the URL for the home page.
					$item->link = JURI::base();
				}

				$item->priority = $menuOptions->priority;
				$item->changefreq = $menuOptions->changefreq;

				XmapHelper::prepareMenuItem($item);

				if ($item->parent_id > 1) {
					$list[$menutype][$item->parent_id]->items[$item->id] = $item;
					unset($list[$menutype][$item->id]);
					
				} else {
				#	$list[$menutype][$item->id] =& $item;
				}
			}
		}
var_dump($list);exit;
		return $list;
	}


	function &getExtensions( ) {
		static $extensions;
		if ($extensions != null) {
			return $extensions;
		}
		$db = & JFactory::getDBO();

		$list = array();
		ini_set('display_errors','Off');

		// Get the menu items as a tree.
		$query = new JQuery;
		$query->select('*');
		$query->from('#__extensions AS n');
		$query->where('n.type = \'xmap_ext\'');
		$query->where('n.enabled = 1');

		// Get the list of menu items.
		$db->setQuery($query);
		$extensions = $db->loadObjectList('element');

		foreach ($extensions as $element => &$extension) {
			require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'extensions'.DS.$extension->folder.DS.$element.'.php');
			$xmlPath = JPATH_COMPONENT_ADMINISTRATOR.DS.'extensions'.DS.$extension->folder.DS.$element.'.xml';

			$params = new JParameter($extension->params,$xmlPath);
			$extension->params = $params->toArray();
		}

		return $extensions;
	}

	/**
	 * Call the function prepareMenuItem of the extension for the item (if any)
	 *
	 * @param	object		Menu item object
	 *
	 * @return	void
	 */
	public function prepareMenuItem(&$item)
	{
		$extensions =& XmapHelper::getExtensions();
		if ( preg_match('#^/?index.php.*option=(com_[^&]+)#',$item->link,$matches) ) {
			$option = $matches[1];
			if ( !empty($extensions[$option]) ) {
				$className = 'xmap_'.$option;
				$obj = new $className;
				if (method_exists($obj,'prepareMenuItem')) {
					$obj->prepareMenuItem($item);
				}
			}
		}
	}

}
