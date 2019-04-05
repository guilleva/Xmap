<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016-2017 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Alledia\OSMap;
use Joomla\Registry\Registry;

defined('_JEXEC') or die();

class OSMapTableSitemap extends JTable
{
    /**
     * @var int Primary key
     */
    public $id = null;

    /**
     * @var string
     */
    public $name = null;

    /**
     * @var string
     */
    public $params = null;

    /**
     * @var string
     */
    public $created_on = null;

    /**
     * @var int
     */
    public $is_default = 0;

    /**
     * @var int
     */
    public $published = 1; //JPUBLISHED's value is 1

    /**
     * @var int
     */
    public $links_count = 0;

    /**
     * @var array
     */
    public $menus = array();

    /**
     * @var array
     */
    public $menus_priority = array();

    /**
     * @var array
     */
    public $menus_changefreq = array();

    /**
     * @var string
     */
    public $menus_ordering = '';

    public function __construct(&$db)
    {
        parent::__construct('#__osmap_sitemaps', 'id', $db);
    }

    public function bind($array, $ignore = '')
    {
        if (isset($array['params']) && is_array($array['params'])) {
            $registry = new Registry();
            $registry->loadArray($array['params']);
            $array['params'] = $registry->toString();
        }

        if (isset($array['metadata']) && is_array($array['metadata'])) {
            $registry = new Registry();
            $registry->loadArray($array['metadata']);
            $array['metadata'] = $registry->toString();
        }

        return parent::bind($array, $ignore);
    }

    public function check()
    {
        if (empty($this->name)) {
            $this->setError(JText::_('COM_OSMAP_MSG_SITEMAP_MUST_HAVE_NAME'));

            return false;
        }

        return true;
    }

    public function store($updateNulls = false)
    {
        $db   = OSMap\Factory::getDbo();
        $date = JFactory::getDate();

        if (!$this->id) {
            $this->created_on = $date->toSql();
        }

        // Make sure we have only one default sitemap
        if ((bool)$this->is_default) {
            // Set as not default any other sitemap
            $query = $db->getQuery(true)
                ->update('#__osmap_sitemaps')
                ->set('is_default = 0');

            $db->setQuery($query)->execute();

        } else {
            // Check if we have another default sitemap. If not, force this as default
            $query = $db->getQuery(true)
                ->select('COUNT(*)')
                ->from('#__osmap_sitemaps')
                ->where('is_default = 1')
                ->where('id <> ' . $db->quote($this->id));

            $count = (int)$db->setQuery($query)->loadResult();

            if ($count == 0) {
                $this->is_default = 1;

                OSMap\Factory::getApplication()->enqueueMessage(
                    JText::_('COM_OSMAP_MSG_SITEMAP_FORCED_AS_DEFAULT'),
                    'info'
                );
            }
        }

        // Get the menus
        $menus           = $this->menus;
        $menusPriority   = $this->menus_priority;
        $menusChangeFreq = $this->menus_changefreq;
        $menusOrdering   = explode(',', $this->menus_ordering);

        unset($this->menus, $this->menus_priority, $this->menus_changefreq, $this->menus_ordering);

        // Store the sitemap data
        $result = parent::store($updateNulls);

        if ($result) {
            // Remove the current menus
            $this->removeMenus();

            if (!empty($menus)) {
                $ordering = 0;

                // Store the menus for this sitemap
                foreach ($menus as $menuId) {
                    // Get the index of the selected menu in the ordering array
                    $index = array_search('menu_' . $menuId, $menusOrdering);

                    $query = $db->getQuery(true)
                        ->insert('#__osmap_sitemap_menus')
                        ->set(
                            array(
                                'sitemap_id = ' . $db->quote($this->id),
                                'menutype_id = ' . $db->quote($menuId),
                                'priority = ' . $db->quote($menusPriority[$index]),
                                'changefreq = ' . $db->quote($menusChangeFreq[$index]),
                                'ordering = ' . $ordering
                            )
                        );
                    $db->setQuery($query)->execute();

                    $ordering++;
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Remove all the menus for the given sitemap
     *
     * @param int $sitemapId
     *
     * @return void
     */
    public function removeMenus()
    {
        if (!empty($this->id)) {
            $db    = OSMap\Factory::getDbo();
            $query = $db->getQuery(true)
                ->delete('#__osmap_sitemap_menus')
                ->where('sitemap_id = ' . $db->quote($this->id));

            $db->setQuery($query)->execute();
        }
    }

    public function load($keys = null, $reset = true)
    {
        if (parent::load($keys, $reset)) {
            // Load the menus information
            $db       = OSMap\Factory::getDbo();
            $ordering = array();

            $query     = $db->getQuery(true)
                ->select('*')
                ->from('#__osmap_sitemap_menus')
                ->where('sitemap_id = ' . $db->quote($this->id))
                ->order('ordering');

            $menusRows = $db->setQuery($query)->loadObjectList();
            if ($menusRows) {
                foreach ($menusRows as $menu) {
                    $this->menus[]            = $menu->menutype_id;
                    $this->menus_priority[]   = $menu->priority;
                    $ordering[]               = 'menu_' . $menu->menutype_id;
                    $this->menus_changefreq[] = $menu->changefreq;
                }
            }

            $this->menus_ordering = join(',', $ordering);

            return true;
        }

        return false;
    }
}
