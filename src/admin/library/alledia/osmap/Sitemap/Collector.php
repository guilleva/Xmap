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
use Alledia\Framework;

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
     * Callback used to trigger the desired action while fetching items.
     * This is only used in the legacy method printNode, which is called by
     * the osmap plugins to process the additional items.
     *
     * @var callable
     */
    protected $printNodeCallback;

    /**
     * The current view: xml or html. Kept for backward compatibility with
     * the legacy plugins. It is always HTML since the collector is generic now
     * and needs to have the information about the item's level even for the
     * XML view in the Pro version, to store that info in the cache.
     *
     * @var string
     */
    public $view = 'html';

    /**
     * Legacy property used by some plugins. True if we are collecting news.
     *
     * @var string
     *
     * @deprecated
     */
    public $isNews = false;

    /**
     * The items counter.
     *
     * @var int
     */
    protected $counter = 0;

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
        $this->counter = 0;

        if (!empty($menus)) {
            foreach ($menus as $menu) {
                $items = $this->getMenuItems($menu);

                foreach ($items as $item) {
                    if ($this->itemIsBlackListed($item)) {
                        continue;
                    }

                    // Submit the item and prepare it calling the plugins
                    $this->submitItem($item, $callback, true);

                    // Internal items can trigger plugins to grab more items
                    if ($item->isInternal) {
                        // Call the plugin to get additional items related to
                        $this->callPluginsGetItemTree($item, $callback);
                    }

                    // Make sure the memory is cleaned
                    $item = null;
                }
            }
        }

        echo sprintf('<m>%s</m>', memory_get_usage() - $baseMemory);
        echo sprintf('<t>%s</t>', microtime() - $baseTime);

        return $this->counter;
    }

    /**
     * Submit the item to the callback, checking duplicity and incrementing
     * the counter. It can receive an array or object and returns true or false
     * according to the result of the callback.
     *
     * @param mixed    $item
     * @param callable $callback
     * @param bool     $prepareItem
     *
     * @return bool
     */
    public function submitItem(&$item, $callback, $prepareItem = false)
    {
        $result = true;

        // Converts to an Item instance, setting internal attributes
        $item = new Item($item, $this->sitemap);

        // Verify if the item's link was already listed
        $this->checkDuplicatedUIDToIgnore($item);

        if ($prepareItem) {
            // Call the plugins to prepare the item
            $this->callPluginsPreparingTheItem($item);
        }

        // Check if the item was set to be ignored, if not, send to the callback
        if (!(bool)$item->ignore) {
            ++$this->counter;

            // Call the given callback function
            $result = (bool)$callback($item);
        }

        return $result;
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
                    // Say that the menu came from a menu
                    '1 AS ' . $db->quoteName('isMenuItem'),
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
        if ($app->isSite() && $app->getLanguageFilter()) {
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
    protected function callPluginsPreparingTheItem($item)
    {
        // Call the OSMap and XMap legacy plugins, if exists
        $plugins = OSMap\Helper::getPluginsForComponent($item->option);


        if (!empty($plugins)) {
            foreach ($plugins as $plugin) {
                $className = '\\' . $plugin->className;

                $result = true;

                if (method_exists($className, 'prepareMenuItem')) {
                    // If a legacy plugin doesn't specify this method as static, fix the plugin to avoid warnings
                    $result = $className::prepareMenuItem($item, $plugin->params);

                    // If a plugin doesn't return true we ignore the item and break
                    if ($result !== true) {
                        $item->set('ignore', true);

                        break;
                    }
                }
            }
        }
    }

    /**
     * Calls the respective OSMap and XMap plugin, according to the item's
     * component/option. Get additional items and send to the callback.
     *
     * @param Item     $item
     * @param Callable $callback
     *
     * @return void
     */
    protected function callPluginsGetItemTree($item, $callback)
    {
        // Register the current callback
        $this->printNodeCallback = $callback;

        // Call the OSMap and XMap legacy plugins, if exists
        $plugins = OSMap\Helper::getPluginsForComponent($item->option);

        if (!empty($plugins)) {
            foreach ($plugins as $plugin) {
                $className = '\\' . $plugin->className;

                if (method_exists($className, 'getTree')) {
                    $className::getTree($this, $item, $plugin->params);
                }
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

    /**
     * This method is used for backward compatibility. The plugins will call
     * it. In the legacy XMap its behavior depends on the sitemap view type,
     * only changing the level in the HTML view. Now, it always consider the
     * level of the item, even for XML view. That allows to store that info
     * in a cache for both view types. XML will just ignore that.
     *
     * @param int $step
     *
     * @return void
     */
    public function changeLevel($step)
    {
        if ($step) {

        }

        return true;
    }

    /**
     * Method called by legacy plugins, which will pass the new item to the
     * callback. Returns the result of the callback converted to boolean.
     *
     * @param object $node
     *
     * @return bool
     */
    public function printNode($node)
    {
        return $this->submitItem($node, $this->printNodeCallback);
    }
}
