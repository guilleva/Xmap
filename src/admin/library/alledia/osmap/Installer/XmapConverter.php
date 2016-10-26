<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap\Installer;

use Alledia\Framework;
use Alledia\OSMap;

defined('_JEXEC') or die();


/**
 * Class with methods to migrate a Xmap installation to OSMap
 */
class XmapConverter
{
    /**
     * @var array
     */
    protected $xmapPluginsParams = array();

    /**
     * List of refactored Xmap plugins to migrate the settings
     *
     * @var array
     */
    protected $refactoredXmapPlugins = array('com_content' => 'joomla');

    /**
     * Look for the Xmap data to suggest a data migration
     *
     * @return bool True if Xmap data was found
     */
    public function checkXmapDataExists()
    {
        $db = \JFactory::getDbo();

        // Do we have any Xmap sitemap?
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from('#__xmap_sitemap');

        $total = (int) $db->setQuery($query)->loadResult();

        return $total > 0;
    }

    /**
     * Save the Xmap plugins params into the new plugins. Receives a list of
     * plugin names to look for params.
     *
     * @return void
     */
    public function saveXmapPluginParamsIfExists()
    {
        $db = \JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select(
                array(
                    'element',
                    'params'
                )
            )
            ->from('#__extensions')
            ->where(
                array(
                    'type = "plugin"',
                    'folder = "xmap"',
                    'element IN ("' . implode('","', array_keys($this->refactoredXmapPlugins)) . '")'
                )
            );
        $legacyPlugins = $db->setQuery($query)->loadObjectList();

        // Check if the respective OSMap plugin is already installed. If so, do not save its params to not override.
        if (!empty($legacyPlugins)) {
            foreach ($legacyPlugins as $plugin) {
                $query = $db->getQuery(true)
                    ->select('extension_id')
                    ->from('#__extensions')
                    ->where(
                        array(
                            'type = "plugin"',
                            'folder = "osmap"',
                            'element = "' . $plugin->element . '"'
                        )
                    );
                $osmapPluginID = $db->setQuery($query)->loadResult();

                if (empty($osmapPluginID)) {
                    $this->xmapPluginsParams[] = $plugin;
                }
            }
        }
    }

    /**
     * This method move the Xmap plugins' params to the OSMap plugins.
     *
     * @return void
     */
    public function moveXmapPluginsParamsToOSMapPlugins()
    {
        $db = \JFactory::getDbo();

        if (!empty($this->xmapPluginsParams)) {
            foreach ($this->xmapPluginsParams as $plugin) {
                // Look for the OSMap plugin
                $query = $db->getQuery(true)
                    ->select('extension_id')
                    ->from('#__extensions')
                    ->where(
                        array(
                            'type = "plugin"',
                            'folder = "osmap"',
                            'element = "' . @$this->refactoredXmapPlugins[$plugin->element] . '"'
                        )
                    );
                $osmapPluginID = $db->setQuery($query)->loadResult();

                if (!empty($osmapPluginID)) {
                    $query = $db->getQuery(true)
                        ->update('#__extensions')
                        ->set('params = "' . addslashes($plugin->params) . '"')
                        ->where('extension_id = ' . $osmapPluginID);
                    $db->setQuery($query)->execute();
                }
            }
        }
    }

    /**
     * Migrates data from Xmap to OSMap.
     *
     * @return void
     */
    public function migrateData()
    {
        $result = new \stdClass;
        $result->success = false;

        $db = \JFactory::getDbo();
        $db->startTransaction();

        try {
            // Do we have any Xmap sitemap?
            $sitemapIds       = array();
            $itemIds          = array();
            $sitemapFailedIds = array();
            $itemFailedIds = array();
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__xmap_sitemap');
            $db->setQuery($query);
            $sitemaps = $db->loadObjectList();

            if (!empty($sitemaps)) {
                // Cleanup the db tables
                $db->setQuery('DELETE FROM `#__osmap_items_settings`')->execute();
                $db->setQuery('DELETE FROM `#__osmap_sitemap_menus`')->execute();
                $db->setQuery('DELETE FROM `#__osmap_sitemaps`')->execute();

                // Import the sitemaps
                foreach ($sitemaps as $sitemap) {
                    $query = $db->getQuery(true)
                        ->set(
                            array(
                                $db->quoteName('id') . '=' . $db->quote($sitemap->id),
                                $db->quoteName('name') . '=' . $db->quote($sitemap->title),
                                $db->quoteName('is_default') . '=' . $db->quote($sitemap->is_default),
                                $db->quoteName('published') . '=' . $db->quote($sitemap->state),
                                $db->quoteName('created_on') . '=' . $db->quote($sitemap->created)
                            )
                        )
                        ->insert('#__osmap_sitemaps');
                    $db->setQuery($query);

                    if ($db->execute()) {
                        $sitemapIds[$sitemap->id] = $db->insertId();

                        // Add the selected menus to the correct table
                        $menus = json_decode($sitemap->selections, true);

                        if (!empty($menus)) {
                            foreach ($menus as $menuType => $menu) {
                                $menuTypeId = $this->getMenuTypeId($menuType);

                                // Check if the menutype still exists
                                if (!empty($menuTypeId)) {
                                    // Convert the selection of menus into a row
                                    $query = $db->getQuery(true)
                                        ->insert('#__osmap_sitemap_menus')
                                        ->columns(
                                            array(
                                                'sitemap_id',
                                                'menutype_id',
                                                'priority',
                                                'changefreq',
                                                'ordering'
                                            )
                                        )
                                        ->values(
                                            implode(
                                                ',',
                                                array(
                                                    $db->quote($sitemap->id),
                                                    $db->quote($menuTypeId),
                                                    $db->quote($menu['priority']),
                                                    $db->quote($menu['changefreq']),
                                                    $db->quote($menu['ordering'])
                                                )
                                            )
                                        );
                                    $db->setQuery($query)->execute();
                                }
                            }
                        }

                        // Convert settings about excluded items
                        $excludedItems = array();
                        if (!empty($sitemap->excluded_items)) {
                            $excludedItems = json_decode($sitemap->excluded_items, true);

                            if (!empty($excludedItems)) {
                                foreach ($excludedItems as $item) {
                                    $uid = $this->convertItemUID($item[0]);

                                    // Check if the item was already registered
                                    $query = $db->getQuery(true)
                                        ->select('COUNT(*)')
                                        ->from('#__osmap_items_settings')
                                        ->where(
                                            array(
                                                'sitemap_id = ' . $db->quote($sitemap->id),
                                                'uid = ' . $db->quote($uid)
                                            )
                                        );
                                    $count = $db->setQuery($query)->loadResult();

                                    if ($count == 0) {
                                        // Insert the settings
                                        $query = $db->getQuery(true)
                                            ->insert('#__osmap_items_settings')
                                            ->columns(
                                                array(
                                                    'sitemap_id',
                                                    'uid',
                                                    'published',
                                                    'changefreq',
                                                    'priority'
                                                )
                                            )
                                            ->values(
                                                implode(
                                                    ',',
                                                    array(
                                                        $sitemap->id,
                                                        $db->quote($uid),
                                                        0,
                                                        $db->quote('weekly'),
                                                        $db->quote('0.5')
                                                    )
                                                )
                                            );
                                        $db->setQuery($query)->execute();
                                    } else {
                                        // Update the setting
                                        $query = $db->getQuery(true)
                                            ->update('#__osmap_items_settings')
                                            ->set('published = 0')
                                            ->where(
                                                array(
                                                    'sitemap_id = ' . $db->quote($sitemap->id),
                                                    'uid = ' . $db->quote($uid)
                                                )
                                            );
                                        $db->setQuery($query)->execute();
                                    }
                                }
                            }
                        }

                        // Convert custom settings for items
                        $query = $db->getQuery(true)
                            ->select(
                                array(
                                    'uid',
                                    'properties'
                                )
                            )
                            ->from('#__xmap_items')
                            ->where('sitemap_id = ' . $db->quote($sitemap->id))
                            ->where('view = ' . $db->quote('xml'));
                        $modifiedItems = $db->setQuery($query)->loadObjectList();

                        if (!empty($modifiedItems)) {
                            foreach ($modifiedItems as $item) {
                                $item->properties = str_replace(';', '&', $item->properties);
                                parse_str($item->properties, $properties);

                                $item->uid = $this->convertItemUID($item->uid);

                                // Check if the item already exists to update, or insert
                                $query = $db->getQuery(true)
                                    ->select('COUNT(*)')
                                    ->from('#__osmap_items_settings')
                                    ->where(
                                        array(
                                            'sitemap_id = ' . $db->quote($sitemap->id),
                                            'uid = ' . $db->quote($item->uid)
                                        )
                                    );
                                $exists = (bool)$db->setQuery($query)->loadResult();


                                if ($exists) {
                                    $fields = array();

                                    // Check if the changefreq is set and set to update
                                    if (isset($properties['changefreq'])) {
                                        $fields = 'changefreq = ' . $db->quote($properties['changefreq']);
                                    }

                                    // Check if the priority is set and set to update
                                    if (isset($properties['priority'])) {
                                        $fields = 'priority = ' . $db->quote($properties['priority']);
                                    }

                                    // Update the item
                                    $query = $db->getQuery(true)
                                        ->update('#__osmap_items_settings')
                                        ->set($fields)
                                        ->where(
                                            array(
                                                'sitemap_id = ' . $db->quote($sitemap->id),
                                                'uid = ' . $db->quote($item->uid)
                                            )
                                        );
                                    $db->setQuery($query)->execute();
                                }

                                if (!$exists) {
                                    $columns = array(
                                        'sitemap_id',
                                        'uid',
                                        'published'
                                    );

                                    $values = array(
                                        $db->quote($sitemap->id),
                                        $db->quote($item->uid),
                                        1
                                    );

                                    // Check if the changefreq is set and set to update
                                    if (isset($properties['changefreq'])) {
                                        $columns[] = 'changefreq';
                                        $values[] = 'changefreq = ' . $db->quote($properties['changefreq']);
                                    }

                                    // Check if the priority is set and set to update
                                    if (isset($properties['priority'])) {
                                        $columns[] = 'priority';
                                        $values[] = 'priority = ' . $db->quote($properties['priority']);
                                    }

                                    // Insert a new item
                                    $query = $db->getQuery(true)
                                        ->insert('#__osmap_items_settings')
                                        ->columns($columns)
                                        ->values(implode(',', $values));
                                    $db->setQuery($query)->execute();
                                }
                            }
                        }
                    } else {
                        $sitemapFailedIds = $sitemap->id;
                    }
                }
            }

            if (!empty($sitemapFailedIds) || !empty($itemFailedIds)) {
                throw new \Exception("Failed the sitemap or item migration");
            }

            /*
             * Menu Migration
             */
            $xmap  = new Framework\Joomla\Extension\Generic('Xmap', 'component');
            $osmap = new Framework\Joomla\Extension\Generic('OSMap', 'component');

            // Remove OSMap menus
            $query = $db->getQuery(true)
                ->delete('#__menu')
                ->where('type = ' . $db->quote('component'))
                ->where('component_id = ' . $db->quote($osmap->getId()));
            $db->setQuery($query);
            $db->execute();

            // Get the Xmap menus
            $query = $db->getQuery(true)
                ->select('*')
                ->from('#__menu')
                ->where('type = ' . $db->quote('component'))
                ->where('component_id = ' . $db->quote($xmap->getId()));
            $db->setQuery($query);
            $xmapMenus = $db->loadObjectList();

            if (!empty($xmapMenus)) {
                // Convert each menu to OSMap
                foreach ($xmapMenus as $menu) {
                    $query = $db->getQuery(true)
                        ->set('title = ' . $db->quote($this->replaceXmapByOSMap($menu->title)))
                        ->set('alias = ' . $db->quote($this->replaceXmapByOSMap($menu->alias)))
                        ->set('path = ' . $db->quote($this->replaceXmapByOSMap($menu->path)))
                        ->set('link = ' . $db->quote($this->replaceXmapByOSMap($menu->link)))
                        ->set('img = ' . $db->quote($this->replaceXmapByOSMap($menu->img)))
                        ->set('component_id = ' . $db->quote($osmap->getId()))
                        ->update('#__menu')
                        ->where('id = ' . $db->quote($menu->id));
                    $db->setQuery($query);
                    $db->execute();
                }
            }

            // Disable Xmap
            $query = $db->getQuery(true)
                ->set('enabled = 0')
                ->update('#__extensions')
                ->where('extension_id = ' . $db->quote($xmap->getId()));
            $db->setQuery($query);
            $db->execute();

            // Clean up Xmap db tables
            $db->setQuery('DELETE FROM ' . $db->quoteName('#__xmap_sitemap'));
            $db->execute();

            $db->setQuery('DELETE FROM ' . $db->quoteName('#__xmap_items'));
            $db->execute();

            $db->commitTransaction();

            $result->success = true;
        } catch (\Exception $e) {
            $db->rollbackTransaction();
            var_dump($e);
        }

        echo json_encode($result);
    }

    /**
     * Replaces the Xmap strings in multiple formats, changing to OSMap.
     *
     * @param string $str
     *
     * @return string
     */
    protected function replaceXmapByOSMap($str)
    {
        $str = str_replace('XMAP', 'OSMAP', $str);
        $str = str_replace('XMap', 'OSMap', $str);
        $str = str_replace('xMap', 'OSMap', $str);
        $str = str_replace('xmap', 'osmap', $str);

        return $str;
    }

    /**
     * Returns the id of the menutype.
     *
     * @param string $menuType
     *
     * @return int
     */
    protected function getMenuTypeId($menuType)
    {
        $db = OSMap\Factory::getDbo();

        $query = $db->getQuery(true)
            ->select('id')
            ->from('#__menu_types')
            ->where('menutype = ' . $db->quote($menuType));
        return (int)$db->setQuery($query)->loadResult();
    }

    /**
     * Converts a legacy UID to the new pattern. Instead of "com_contenta25",
     * "joomla.article.25". Returns the new UID
     *
     * @param string $uid
     *
     * @return string
     */
    protected function convertItemUID($uid)
    {
        // Joomla articles in categories
        if (preg_match('#com_contentc[0-9]+a([0-9]+)#', $uid, $matches)) {
            return 'joomla.article.' . $matches[1];
        }

        // Joomla categories
        if (preg_match('#com_contentc([0-9]+)#', $uid, $matches)) {
            return 'joomla.category.' . $matches[1];
        }

        // Joomla articles
        if (preg_match('#com_contenta([0-9]+)#', $uid, $matches)) {
            return 'joomla.article.' . $matches[1];
        }

        // Joomla featured
        if (preg_match('#com_contentfeatureda([0-9]+)#', $uid, $matches)) {
            return 'joomla.featured.' . $matches[1];
        }

        // Menu items
        if (preg_match('#itemid([0-9]*)#', $uid, $matches)) {
            return 'menuitem.' . $matches[1];
        }

        return $uid;
    }
}
