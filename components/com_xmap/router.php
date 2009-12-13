<?php
/**
 * @version		$Id$
 * @copyright   Copyright (C) 2005 - 2009 Joomla! Vargas. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @author		Guillermo Vargas (guille@vargas.co.cr)
 */

/**
 * Content Component Route Helper
 *
 * @package		Xmap
 * @subpackage	com_xmap
 * @since 2.0
 */
class XmapRoute
{

	/**
	 * @param	int $id			The id of the article.
	 * @param	int	$categoryId	An optional category id.
	 *
	 * @return	string	The routed link.
	 */
	public static function sitemap($id, $view = 'html')
	{
		$needles = array(
			'html'	=> (int) $id
		);

		//Create the link
		$link = 'index.php?option=com_xmap&view='.$view.'&id='. $id;

		if ($itemId = self::_findItemId($needles)) {
			$link .= '&Itemid='.$itemId;
		};

		return $link;
	}


	protected static function _findItemId($needles)
	{
		// Prepare the reverse lookup array.
		if (self::$lookup === null)
		{
			self::$lookup = array();

			$component	= &JComponentHelper::getComponent('com_xmap');
			$menus		= &JApplication::getMenu('site', array());
			$items		= $menus->getItems('component_id', $component->id);

			foreach ($items as &$item)
			{
				if (isset($item->query) && isset($item->query['view']))
				{
					$view = $item->query['view'];
					if (!isset(self::$lookup[$view])) {
						self::$lookup[$view] = array();
					}
					if (isset($item->query['id'])) {
						self::$lookup[$view][$item->query['id']] = $item->id;
					}
				}
			}
		}

		$match = null;

		foreach ($needles as $view => $id)
		{
			if (isset(self::$lookup[$view]))
			{
				if (isset(self::$lookup[$view][$id])) {
					return self::$lookup[$view][$id];
				}
			}
		}

		return null;
	}
}

/**
 * Build the route for the com_content component
 *
 * @param	array	An array of URL arguments
 *
 * @return	array	The URL arguments to use to assemble the subsequent URL.
 */
function XmapBuildRoute(&$query)
{
	$segments = array();

	// get a menu item based on Itemid or currently active
	$menu = &JSite::getMenu();

	if (empty($query['Itemid'])) {
		$menuItem = &$menu->getActive();
	}
	else {
		$menuItem = &$menu->getItem($query['Itemid']);
	}
	$mView	= (empty($menuItem->query['view'])) ? null : $menuItem->query['view'];
	$mId	= (empty($menuItem->query['id'])) ? null : $menuItem->query['id'];

	if ( !empty($query['Itemid']) ) {
		unset($query['view']);
		unset($query['id']);
	} else {
		if ( !empty($query['view']) ) {
			 $segments[] = $query['view'];
		}
	}


	if (isset($query['id']))
	{
		if (empty($query['Itemid'])) {
			$segments[] = $query['id'];
		}
		else
		{
			if (isset($menuItem->query['id']))
			{
				if ($query['id'] != $mId) {
					$segments[] = $query['id'];
				}
			}
			else {
				$segments[] = $query['id'];
			}
		}
		unset($query['id']);
	};

	if (isset($query['layout']))
	{
		if (!empty($query['Itemid']) && isset($menuItem->query['layout']))
		{
			if ($query['layout'] == $menuItem->query['layout']) {

				unset($query['layout']);
			}
		}
		else
		{
			if ($query['layout'] == 'default') {
				unset($query['layout']);
			}
		}
	};

	return $segments;
}

/**
 * Parse the segments of a URL.
 *
 * @param	array	The segments of the URL to parse.
 *
 * @return	array	The URL attributes to be used by the application.
 */
function XmapParseRoute($segments)
{
	$vars = array();

	//G et the active menu item.
	$menu = &JSite::getMenu();
	$item = &$menu->getActive();

	// Count route segments
	$count = count($segments);

	// Standard routing for articles.
	if (!isset($item))
	{
		$vars['view']	= $segments[0];
		$vars['id']		= $segments[$count - 1];
		return $vars;
	}

	$vars['view'] = $item->query['view'];
	$vars['id'] = $item->query['id'];

	return $vars;
}
