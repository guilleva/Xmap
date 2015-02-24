<?php

/**
 * @version       $Id$
 * @copyright     Copyright (C) 2005 - 2009 Joomla! Vargas. All rights reserved.
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 * @author        Guillermo Vargas (guille@vargas.co.cr)
 */
// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.database.query');

/**
 * Xmap Component Sitemap Model
 *
 * @package        Xmap
 * @subpackage     com_xmap
 * @since          2.0
 */
class XmapHelper
{

    public static function &getMenuItems($selections)
    {
        $db = JFactory::getDbo();
        $app = JFactory::getApplication();
        $user = JFactory::getUser();
        $list = array();

        foreach ($selections as $menutype => $menuOptions) {
            // Initialize variables.
            // Get the menu items as a tree.
            $query = $db->getQuery(true);
            $query->select(
                    'n.id, n.title, n.alias, n.path, n.level, n.link, '
                  . 'n.type, n.params, n.home, n.parent_id'
                  . ',n.'.$db->quoteName('browserNav')
                  );
            $query->from('#__menu AS n');
            $query->join('INNER', ' #__menu AS p ON p.lft = 0');
            $query->where('n.lft > p.lft');
            $query->where('n.lft < p.rgt');
            $query->order('n.lft');

            // Filter over the appropriate menu.
            $query->where('n.menutype = ' . $db->quote($menutype));

            // Filter over authorized access levels and publishing state.
            $query->where('n.published = 1');
            $query->where('n.access IN (' . implode(',', (array) $user->getAuthorisedViewLevels()) . ')');

            // Filter by language
            if ($app->getLanguageFilter()) {
                $query->where('n.language in ('.$db->quote(JFactory::getLanguage()->getTag()).','.$db->quote('*').')');
            }

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

                $params = new JRegistry($item->params);
                $item->uid = 'itemid'.$item->id;

                if (preg_match('#^/?index.php.*option=(com_[^&]+)#', $item->link, $matches)) {
                    $item->option = $matches[1];
                    $componentParams = clone(JComponentHelper::getParams($item->option));
                    $componentParams->merge($params);
                    //$params->merge($componentParams);
                    $params = $componentParams;
                } else {
                    $item->option = null;
                }

                $item->params = $params;

                if ($item->type != 'separator') {

                    $item->priority = $menuOptions['priority'];
                    $item->changefreq = $menuOptions['changefreq'];

                    XmapHelper::prepareMenuItem($item);
                } else {
                    $item->priority = null;
                    $item->changefreq = null;
                }

                if ($item->parent_id > 1) {
                    $tmpList[$item->parent_id]->items[$item->id] = $item;
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
        // Get the menu items as a tree.
        $query = $db->getQuery(true);
        $query->select('*');
        $query->from('#__extensions AS n');
        $query->where('n.folder = \'xmap\'');
        $query->where('n.enabled = 1');

        // Get the list of menu items.
        $db->setQuery($query);
        $extensions = $db->loadObjectList('element');

        foreach ($extensions as $element => $extension) {
            if (file_exists(JPATH_PLUGINS . '/' . $extension->folder . '/' . $element. '/'. $element . '.php')) {
                require_once(JPATH_PLUGINS . '/' . $extension->folder . '/' . $element. '/'. $element . '.php');
                $params = new JRegistry($extension->params);
                $extension->params = $params->toArray();
                $list[$element] = $extension;
            }
        }

        return $list;
    }

    /**
     * Call the function prepareMenuItem of the extension for the item (if any)
     *
     * @param    object        Menu item object
     *
     * @return    void
     */
    public static function prepareMenuItem($item)
    {
        $extensions = XmapHelper::getExtensions();
        if (!empty($extensions[$item->option])) {
            $className = 'xmap_' . $item->option;
            $obj = new $className;
            if (method_exists($obj, 'prepareMenuItem')) {
                $obj->prepareMenuItem($item,$extensions[$item->option]->params);
            }
        }
    }


    static function getImages($text,$max)
    {
        if (!isset($urlBase)) {
            $urlBase = JURI::base();
            $urlBaseLen = strlen($urlBase);
        }

        $images = null;
        $matches = $matches1 = $matches2 = array();
        // Look <img> tags
        preg_match_all('/<img[^>]*?(?:(?:[^>]*src="(?P<src>[^"]+)")|(?:[^>]*alt="(?P<alt>[^"]+)")|(?:[^>]*title="(?P<title>[^"]+)"))+[^>]*>/i', $text, $matches1, PREG_SET_ORDER);
        // Loog for <a> tags with href to images
        preg_match_all('/<a[^>]*?(?:(?:[^>]*href="(?P<src>[^"]+\.(gif|png|jpg|jpeg))")|(?:[^>]*alt="(?P<alt>[^"]+)")|(?:[^>]*title="(?P<title>[^"]+)"))+[^>]*>/i', $text, $matches2, PREG_SET_ORDER);
        $matches = array_merge($matches1,$matches2);
        if (count($matches)) {
            $images = array();

            $count = count($matches);
            $j = 0;
            for ($i = 0; $i < $count && $j < $max; $i++) {
                if (trim($matches[$i]['src']) && (substr($matches[$i]['src'], 0, 1) == '/' || !preg_match('/^https?:\/\//i', $matches[$i]['src']) || substr($matches[$i]['src'], 0, $urlBaseLen) == $urlBase)) {
                    $src = $matches[$i]['src'];
                    if (substr($src, 0, 1) == '/') {
                        $src = substr($src, 1);
                    }
                    if (!preg_match('/^https?:\//i', $src)) {
                        $src = $urlBase . $src;
                    }
                    $image = new stdClass;
                    $image->src = $src;
                    $image->title = (isset($matches[$i]['title']) ? $matches[$i]['title'] : @$matches[$i]['alt']);
                    $images[] = $image;
                    $j++;
                }
            }
        }
        return $images;
    }

    static function getPagebreaks($text,$baseLink)
    {
        $matches = $subnodes = array();
        if (preg_match_all(
                '/<hr\s*[^>]*?(?:(?:\s*alt="(?P<alt>[^"]+)")|(?:\s*title="(?P<title>[^"]+)"))+[^>]*>/i',
                $text, $matches, PREG_SET_ORDER)
        ) {
            $i = 2;
            foreach ($matches as $match) {
                if (strpos($match[0], 'class="system-pagebreak"') !== FALSE) {
                    $link = $baseLink . '&limitstart=' . ($i - 1);

                    if (@$match['alt']) {
                        $title = stripslashes($match['alt']);
                    } elseif (@$match['title']) {
                        $title = stripslashes($match['title']);
                    } else {
                        $title = JText::sprintf('Page #', $i);
                    }
                    $subnode = new stdclass();
                    $subnode->name = $title;
                    $subnode->expandible = false;
                    $subnode->link = $link;
                    $subnodes[] = $subnode;
                    $i++;
                }
            }

        }
        return $subnodes;
    }
}
