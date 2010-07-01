<?php

/**
 * @version       $Id$
 * @copyright     Copyright (C) 2005 - 2009 Joomla! Vargas. All rights reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @author	Guillermo Vargas (guille@vargas.co.cr)
 */
// No direct access
defined('_JEXEC') or die;

require_once(JPATH_SITE . DS . 'includes' . DS . 'application.php');
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

    public static function &getMenuItems($selections)
    {
        $db = JFactory::getDbo();
        $user = JFactory::getUser();
        $list = array();

        foreach ($selections as $menutype => $menuOptions) {
            // Initialize variables.
            // Get the menu items as a tree.
            $query = $db->getQuery(true);
            $query->select('n.id, n.title, n.alias, n.path, n.level, n.link, n.type, n.browserNav, n.params, n.home, n.parent_id');
            $query->from('#__menu AS n');
            $query->join('INNER', ' #__menu AS p ON p.lft = 0');
            $query->where('n.lft > p.lft');
            $query->where('n.lft < p.rgt');
            $query->order('n.lft');

            // Filter over the appropriate menu.
            $query->where('n.menutype = ' . $db->quote($menutype));

            // Filter over authorized access levels and publishing state.
            $query->where('n.published = 1');
            $query->where('n.access IN (' . implode(',', (array) $user->authorisedLevels()) . ')');

            // Get the list of menu items.
            $db->setQuery($query);
            $tmpList = $db->loadObjectList('id');
            $list[$menutype] = array();

            // Check for a database error.
            if ($db->getErrorNum()) {
                JError::raiseWarning(021, $db->getErrorMsg());
                return array();
            }

            // Set some values to make nested HTML rendering easier.
            foreach ($tmpList as $id => $item) {
                $item->items = array();

                $item->params = new JObject(json_decode($item->params));
                if (preg_match('#^/?index.php.*option=(com_[^&]+)#', $item->link, $matches)) {
                    $item->option = $matches[1];
                } else {
                    $item->option = null;
                }


                if ($item->type != 'separator') {
                    if ($item->home == 1) {
                        // Correct the URL for the home page.
                        $item->link = JURI::base();
                    }

                    $item->priority = $menuOptions['priority'];
                    $item->changefreq = $menuOptions['changefreq'];

                    XmapHelper::prepareMenuItem($item);
                }

                if ($item->parent_id > 1) {
                    $tmpList[$item->parent_id]->items[$item->id] = $item;
                    //unset($list[$menutype][$item->id]);
                } else {
                    $list[$menutype][$item->id] = $item;
                }
            }
        }
        return $list;
    }

    public static function &getExtensions()
    {
        static $list;

        jimport('joomla.html.parameter');

        if ($list != null) {
            return $list;
        }
        $db = JFactory::getDBO();

        $list = array();
        //ini_set('display_errors','Off');
        // Get the menu items as a tree.
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__extensions AS n');
        $query->where('n.type = \'xmap_ext\'');
        $query->where('n.enabled = 1');

        // Get the list of menu items.
        $db->setQuery($query);
        $extensions = $db->loadObjectList('element');

        foreach ($extensions as $element => $extension) {
            $element = preg_replace('/^xmap_/', '', $element);
            require_once(JPATH_COMPONENT_ADMINISTRATOR . DS . 'extensions' . DS . $extension->folder . DS . $element . '.php');
            $xmlPath = JPATH_COMPONENT_ADMINISTRATOR . DS . 'extensions' . DS . $extension->folder . DS . $element . '.xml';

            $params = new JParameter($extension->params, $xmlPath);
            $extension->params = $params->toArray();
            $list[$element] = $extension;
        }

        return $list;
    }

    /**
     * Call the function prepareMenuItem of the extension for the item (if any)
     *
     * @param	object		Menu item object
     *
     * @return	void
     */
    public static function prepareMenuItem($item)
    {
        $extensions = & XmapHelper::getExtensions();
        if (!empty($extensions[$item->option])) {
            $className = 'xmap_' . $item->option;
            $obj = new $className;
            if (method_exists($obj, 'prepareMenuItem')) {
                $obj->prepareMenuItem($item);
            }
        }
    }

}
