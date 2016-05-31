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
     * @var array
     */
    protected $uidList = array();

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
        $baseMemory = memory_get_usage();
        $baseTime   = microtime();

        $menus = $this->getSitemapMenus();
        $count = 0;

        if (!empty($menus)) {
            foreach ($menus as $menu) {
                $items = $this->getMenuItems($menu);

                foreach ($items as $item) {
                    if ($this->itemIsBlackListed($item)) {
                        continue;
                    }

                    // Converts to an Item instance, setting internal attributes
                    $item = new Item($item, $this->sitemap);
                    // var_dump($item);

                    // Verify if the item's link was already listed
                    $this->checkDuplicatedUIDToIgnore($item);

                    // Call the plugins to prepare the item
                    $this->callPluginsPreparingThemItem($item);

                    // @todo: callplugins: getTree

                    // Check if the item was set to be ignored
                    if ((bool)$item->ignore) {
                        continue;
                    }

                    ++$count;

                    // Call the given callback function
                    $callback($item);

                    // Make sure the memory is cleaned
                    $item = null;
                }
            }
        }

        echo sprintf('<m>%s</m>', memory_get_usage() - $baseMemory);
        echo sprintf('<t>%s</t>', microtime() - $baseTime);

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
     * Get the menu items as a tree
     *
     * @param object $menu
     *
     * @return array
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
                    'm.type',
                    'm.home',
                    'm.params',
                    'm.parent_id',
                    'm.browserNav',
                    'm.link',
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

        $items = $db->setQuery($query)->loadAssocList();

        // Check for a database error
        if ($db->getErrorNum()) {
            throw new Exception($db->getErrorMsg(), 021);
        }

        return $items;
    }

    /**
     * Checks if the item's uid was already registered. If positive, set the
     * item to be ignored and return true. If negative, register the item and
     * return false.
     *
     * @param object $item
     *
     * @return bool
     */
    protected function checkDuplicatedUIDToIgnore($item)
    {
        // If is already set, interrupt the flux and ignore the item
        if (isset($this->uidList[$item->uid])) {
            $item->set('ignore', true);

            return true;
        }

        // Not set, so let's register
        $this->uidList[$item->uid] = 1;

        return false;
    }

    /**
     * Calls the respective OSMap and XMap plugin, according to the item's
     * component/option. If the plugin's method returns false, it will set
     * the item's ignore attribute to true.
     *
     * @param Item $item
     *
     * @return void
     */
    protected function callPluginsPreparingThemItem($item)
    {
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
                $item->set('ignore', true);
            }
        }
    }

    /**
     * Returns true if the link of the item is in the blacklist array.
     *
     * @param array $item
     *
     * @return bool
     */
    protected function itemIsBlackListed($item)
    {
        $blackList = array(
            'administrator' => 1
        );

        $link = $item['link'];

        return isset($blackList[$link]);
    }
}
