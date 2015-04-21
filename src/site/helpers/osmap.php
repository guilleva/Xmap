<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas. All rights reserved.
 * @copyright 2015 Open Source Training, LLC. All rights reserved..
 * @author    Guillermo Vargas <guille@vargas.co.cr>
 * @author    Alledia <support@alledia.com>
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 *
 * This file is part of OSMap.
 *
 * OSMap is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * OSMap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with OSMap. If not, see <http://www.gnu.org/licenses/>.
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.database.query');

/**
 * OSMap Component Sitemap Model
 *
 * @package        OSMap
 * @subpackage     com_osmap
 * @since          2.0
 */
class OSMapHelper
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

                if (OSMAP_LICENSE === 'pro') {
                    $menuItem = new Alledia\OSMap\Pro\Joomla\Item($item);

                    if (!$menuItem->isVisibleForRobots()) {
                        continue;
                    }
                }

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

                    if (false === OSMapHelper::prepareMenuItem($item)) {
                        continue;
                    }
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
        $query->from('#__extensions');
        $query->where("folder IN (" . $db->quote('osmap') . ',' . $db->quote('xmap') . ")");
        $query->where("type = " . $db->quote('plugin'));
        $query->where('enabled = 1');

        // Get the list of menu items.
        $db->setQuery($query);
        $extensions = $db->loadObjectList();

        foreach ($extensions as $extension) {
            $path = JPATH_PLUGINS . '/' . $extension->folder . '/' . $extension->element. '/'. $extension->element . '.php';

            if (file_exists($path)) {
                $extension->className = $extension->folder . '_' . $extension->element;

                $params = new JRegistry($extension->params);
                $extension->params = $params->toArray();

                if (!class_exists($extension->className)) {
                    require $path;

                    $className = $extension->className;

                    if (method_exists($className, 'getInstance')) {
                        $instance = $className::getInstance();
                    }

                }

                $list[$extension->element] = $extension;
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
        $extensions = OSMapHelper::getExtensions();
        $result     = true;

        if (!empty($extensions[$item->option])) {
            $plugin = $extensions[$item->option];

            // Check if the method is static or not
            $result = Alledia\Framework\Helper::callMethod($plugin->className, 'prepareMenuItem', array($item, &$plugin->params));
        }

        if ($result === null) {
            $result = true;
        }

        return $result;
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
