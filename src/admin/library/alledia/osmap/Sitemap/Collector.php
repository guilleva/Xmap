<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.alledia.com, support@alledia.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap\Sitemap;

use Alledia\OSMap;

defined('_JEXEC') or die();

/**
 * Sitemap items collector
 */
class Collector
{
    /**
     * @var SitemapInterface
     */
    protected $sitemap;

    /**
     * The constructor
     */
    public function __construct($sitemap)
    {
        $this->sitemap = $sitemap;
    }

    /**
     * Collects sitemap items based on the selected menus. This is the main
     * method of this class. For each found item, it will call the given
     * callback, so it can manipulate the data in many ways. It returns the
     * total of found items.
     *
     * @param callable $callback
     *
     * @return int
     */
    public function fetch($callback)
    {
        $menus = $this->getSitemapMenus();
        $count = 0;

        if (!empty($menus)) {
            foreach ($menus as $menu) {
                $items = $this->getMenuItems($menu);

                foreach ($items as $item) {
                    // Set additional attributes to the item
                    $this->prepareItemAttributes($item);

                    // Check if the item was set to be ignored
                    if ((bool)$item->ignore) {
                        continue;
                    }

                    ++$count;

                    // Call the given callback function
                    $callback($item);
                }
            }
        }

        return $count;
    }

    /**
     * Gets the list of selected menus for the sitemap.
     * It returns a list of objects with the attributes:
     *  - title
     *  - menutype
     *  - priority
     *  - changefrq
     *  - ordering
     *
     * @return array;
     */
    protected function getSitemapMenus()
    {
        $db = OSMap\Factory::getContainer()->db;

        $query = $db->getQuery(true)
            ->select(
                array(
                    'mt.title',
                    'mt.menutype',
                    'osm.priority',
                    'osm.changefreq',
                    'osm.ordering'
                )
            )
            ->from('#__osmap_sitemap_menus AS osm')
            ->join('LEFT', '#__menu_types AS mt ON (osm.menutype_id = mt.id)')
            ->where('osm.sitemap_id = ' . $db->quote($this->sitemap->id))
            ->order('osm.ordering');

        $list = $db->setQuery($query)->loadObjectList('menutype');

        // Check for a database error
        if ($db->getErrorNum()) {
            throw new Exception($db->getErrorMsg(), 021);
        }

        return $list;
    }

    /**
     * Get the menu items as a tree. Each menu item has the following
     * attributes:
     */
    protected function getMenuItems($menu)
    {
        $container = OSMap\Factory::getContainer();
        $db        = $container->db;
        $user      = $container->user;
        $app       = $container->app;
        $lang      = $container->language;

        $query = $db->getQuery(true)
            ->select(
                array(
                    'm.id',
                    'm.title',
                    'm.alias',
                    'm.path',
                    'm.level',
                    'm.link',
                    'm.type',
                    'm.params',
                    'm.home',
                    'm.parent_id',
                    'm.browserNav',
                    // Flag that allows to children classes choose to ignore items
                    '0 AS ' . $db->quoteName('ignore')
                )
            )
            ->from('#__menu AS m')
            ->join('INNER', '#__menu AS p ON (p.lft = 0)')
            ->where('m.menutype = ' . $db->quote($menu->menutype))
            ->where('m.published = 1')
            ->where('m.access IN (' . implode(',', (array)$user->getAuthorisedViewLevels()) . ')')
            ->where('m.lft > p.lft')
            ->where('m.lft < p.rgt')
            ->order('m.lft');

        // Filter by language
        if ($app->getLanguageFilter()) {
            $query->where('m.language IN (' . $db->quote($lang->getTag()) . ',' . $db->quote('*') . ')');
        }

        $items = $db->setQuery($query)->loadObjectList();

        // Check for a database error
        if ($db->getErrorNum()) {
            throw new Exception($db->getErrorMsg(), 021);
        }

        return $items;
    }

    /**
     * Gets the UID for the given menu item.
     *
     * @param object
     *
     * @return string
     */
    protected function getUIDForItem($item)
    {
        return $this->sitemap->id . '#itemid' . $item->id;
    }

    /**
     * Sets additional attributes to the item
     *
     * @param object
     */
    protected function prepareItemAttributes(&$item)
    {
        $db = OSMap\Factory::getContainer()->db;

        // Set the UID for the item, to avoid duplication
        $item->uid        = $this->getUIDForItem($item);
        $item->params     = new \JRegistry($item->params);
        $item->priority   = null;
        $item->changefreq = null;
        $item->modifiedOn = null;

        // Check if its link has an option/component
        $item->option = null;
        if (preg_match('#^/?index.php.*option=(com_[^&]+)#', $item->link, $matches)) {
            $item->option = $matches[1];

            // Merge the component options
            $componentParams = clone(\JComponentHelper::getParams($item->option));
            $componentParams->merge($item->params);
            $item->params =& $componentParams;

            // Call the OSMap and XMap legacy plugins, if exists
            $plugin = OSMap\Helper::getPluginForComponent($item->option);
            if (!empty($plugin)) {
                $result = Framework\Helper::callMethod(
                    $plugin->className,
                    'prepareMenuItem',
                    array(
                        &$item,
                        &$plugin->params
                    )
                );

                if ($result === false) {
                    $item->ignore = true;
                }
            }
        }

        // Set the last modification date
        $item->modifiedOn = null;

        if (isset($item->modified) && $item->modified != false && $item->modified != $db->getNullDate() && $item->modified != -1) {
            $item->modifiedOn = $item->modified;
            unset($item->modified);
        }

        // @todo: not for news?
        if (empty($item->modifiedOn)) {
            $item->modifiedOn = time();
        }

        if (!empty($item->modifiedOn) && !is_numeric($item->modifiedOn)) {
            $date =  new \JDate($item->modifiedOn);
            $item->modifiedOn = $date->toUnix();
        }

        if ($item->modifiedOn) {
            $item->modifiedOn = gmdate('Y-m-d\TH:i:s\Z', $item->modifiedOn);
        }

        // @todo: Set the priority and changefreq values
    }

    /**
     * Converts a menu item record in a sitemap node instance.
     *
     * @param object
     *
     * @return void
     */
    protected function convertMenuItemToNode($menu)
    {
        $node = new Node;
    }
}
