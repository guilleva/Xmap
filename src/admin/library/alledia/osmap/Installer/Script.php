<?php
/**
 * @package   OSMap
 * @copyright 2007-2014 XMap - Joomla! Vargas - Guillermo Vargas. All rights reserved.
 * @copyright 2016 Open Source Training, LLC. All rights reserved.
 * @contact   www.joomlashack.com, help@joomlashack.com
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSMap\Installer;

use Alledia\Installer\AbstractScript;
use Alledia\OSMap;

defined('_JEXEC') or die();

if (file_exists(__DIR__ . '/../../../Installer/include.php')) {
    $basePath = __DIR__ . '/../../..';
} else {
    $basePath = __DIR__ . '/../..';
}

require_once $basePath . '/Installer/include.php';
require_once __DIR__ . '/XmapConverter.php';

require_once JPATH_ADMINISTRATOR . '/modules/mod_menu/helper.php';


/**
 * OSMap Installer Script
 */
class Script extends AbstractScript
{
    /**
     * @var bool
     */
    protected $isXmapDataFound = false;

    /**
     * Post installation actions
     *
     * @return bool
     */
    public function postFlight($type, $parent)
    {
        // Check if XMap is installed, to start a migration
        $xmapConverter = new XmapConverter;

        // This attribute will be used by the cusotm template to display the option to migrate legacy sitemaps
        $this->isXmapDataFound = $this->tableExists('#__xmap_sitemap') ?
            $xmapConverter->checkXmapDataExists() : false;

        // If Xmap plugins are still available and we don't have the OSMap plugins yet,
        // save Xmap plugins params to re-apply after install OSMap plugins
        $xmapConverter->saveXmapPluginParamsIfExists();

        // Runs the post install/update method
        if (!parent::postFlight($type, $parent)) {
            return false;
        }

        // Load Alledia Framework
        require_once JPATH_ADMINISTRATOR . '/components/com_osmap/include.php';

        if ($type === 'update') {
            $this->migrateLegacySitemaps();
        }

        if ($type === 'install') {
            // Check if we have any sitemap. If not, create a default one
            $this->createDefaultSitemap();
        }

        // Check if we have params from Xmap plugins to apply to OSMap plugins
        $xmapConverter->moveXmapPluginsParamsToOSMapPlugins();

        return true;
    }

    /**
     * Creates a default sitemap if no one is found.
     *
     @return void
     */
    protected function createDefaultSitemap()
    {
        $db = OSMap\Factory::getDbo();

        // Check if we have any sitemap, otherwise lets create a default one
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from('#__osmap_sitemaps');
        $noSitemaps = ((int) $db->setQuery($query)->loadResult()) === 0;

        if ($noSitemaps) {
            // Get all menus
            $menus = \ModMenuHelper::getMenus();
            if (!empty($menus)) {
                $data = array(
                    'name'       => 'Default Sitemap',
                    'is_default' => 1,
                    'published'  => 1
                );

                // Create the sitemap
                \JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_osmap/tables');
                $row = \JTable::getInstance('Sitemap', 'OSMapTable');
                $row->save($data);

                $i = 0;
                foreach ($menus as $menu) {
                    $menuTypeId = $this->getMenuTypeId($menu->menutype);

                    $query = $db->getQuery(true)
                        ->set('sitemap_id = ' . $db->quote($row->id))
                        ->set('menutype_id = ' . $db->quote($menuTypeId))
                        ->set('priority = ' . $db->quote('0.5'))
                        ->set('changefreq = ' . $db->quote('weekly'))
                        ->set('ordering = ' . $db->quote($i++))
                        ->insert('#__osmap_sitemap_menus');
                    $db->setQuery($query)->execute();
                }
            }
        }
    }

    /**
     * In case we are updating from a legacy version, make sure to cleanup
     * the new tables to get a clean start for the data migration
     *
     * @return void
     */
    protected function cleanupDatabase()
    {
        $db = OSMap\Factory::getDbo();

        $db->setQuery('DELETE FROM ' . $db->quoteName('#__osmap_items_settings'))->execute();
        $db->setQuery('DELETE FROM ' . $db->quoteName('#__osmap_sitemap_menus'))->execute();
        $db->setQuery('DELETE FROM ' . $db->quoteName('#__osmap_sitemaps'))->execute();
    }

    /**
     * Check if there are sitemaps in the old table. After migrate, remove
     * the table.
     *
     * @return void
     */
    protected function migrateLegacySitemaps()
    {
        $db = OSMap\Factory::getDbo();

        if ($this->tableExists('#__osmap_sitemap')) {
            try {
                $db->transactionStart();

                // For the migration, as we only have new tables, make sure to have a clean start
                $this->cleanupDatabase();

                // Get legacy sitemaps
                $query = $db->getQuery(true)
                    ->select(
                        array(
                            'id',
                            'title',
                             'is_default',
                             'state',
                             'created',
                             'selections',
                             'excluded_items'
                        )
                    )
                    ->from('#__osmap_sitemap');
                $sitemaps = $db->setQuery($query)->loadObjectList();

                // Move the legacy sitemaps to the new table
                if (!empty($sitemaps)) {
                    foreach ($sitemaps as $sitemap) {
                        // Make sure we have a creation date
                        if ($sitemap->created === $db->getNullDate()) {
                            $sitemap->created = OSMap\Factory::getDate()->toSql();
                        }

                        $query = $db->getQuery(true)
                            ->insert('#__osmap_sitemaps')
                            ->set(
                                array(
                                    'id = ' . $db->quote($sitemap->id),
                                    'name = ' . $db->quote($sitemap->title),
                                    'is_default = ' . $db->quote($sitemap->is_default),
                                    'published = ' . $db->quote($sitemap->state),
                                    'created_on = ' . $db->quote($sitemap->created)
                                )
                            );
                        $db->setQuery($query)->execute();

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
                        if ($this->tableExists('#__osmap_items')) {
                            $query = $db->getQuery(true)
                                ->select(
                                    array(
                                        'uid',
                                        'properties'
                                    )
                                )
                                ->from('#__osmap_items')
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
                        }
                    }
                }

                // Remove the old table
                $query = 'DROP TABLE IF EXISTS ' . $db->quoteName('#__osmap_items');
                $db->setQuery($query)->execute();

                // Remove the old table
                $query = 'DROP TABLE IF EXISTS ' . $db->quoteName('#__osmap_sitemap');
                $db->setQuery($query)->execute();

                $db->transactionCommit();
            } catch (\Exception $e) {
                \JFactory::getApplication()->enqueueMessage(
                    \JText::sprintf('COM_OSMAP_INSTALLER_ERROR_MIGRATING_DATA', $e->getMessage()),
                    'error'
                );
                $db->transactionRollback();
            }
        }
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
